import React, { createContext, ReactNode, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { loadRouteModule, normalizeRouteTarget } from '../routing/routeRegistry';
import { resolveRouteBootstrapData } from '../services/routeBootstrap';

type AsyncStatus = 'idle' | 'loading' | 'ready' | 'error';

interface RouteBootstrapState {
    path: string | null;
    payload: any | null;
    status: AsyncStatus;
    moduleStatus: AsyncStatus;
    dataStatus: AsyncStatus;
    error: unknown;
}

interface RouteBootstrapContextType {
    state: RouteBootstrapState;
    prepareRoute: (path: string) => Promise<any>;
    getPayloadForPath: (path: string) => any | null;
}

const initialState: RouteBootstrapState = {
    path: null,
    payload: null,
    status: 'idle',
    moduleStatus: 'idle',
    dataStatus: 'idle',
    error: null,
};

const RouteBootstrapContext = createContext<RouteBootstrapContextType | undefined>(undefined);

export const RouteBootstrapProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [state, setState] = useState<RouteBootstrapState>(initialState);
    const stateRef = useRef<RouteBootstrapState>(initialState);
    const inFlightRef = useRef<{ path: string; promise: Promise<any> } | null>(null);

    useEffect(() => {
        stateRef.current = state;
    }, [state]);

    const prepareRoute = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.status === 'ready' && stateRef.current.path === normalized) {
            return Promise.resolve(stateRef.current.payload);
        }

        if (inFlightRef.current?.path === normalized) {
            return inFlightRef.current.promise;
        }

        setState({
            path: normalized,
            payload: null,
            status: 'loading',
            moduleStatus: 'loading',
            dataStatus: 'loading',
            error: null,
        });

        const modulePromise = loadRouteModule(normalized).then(() => {
            setState((current) => current.path === normalized ? {
                ...current,
                moduleStatus: 'ready',
            } : current);
        }).catch((error) => {
            setState((current) => current.path === normalized ? {
                ...current,
                status: 'error',
                moduleStatus: 'error',
                error,
            } : current);
            throw error;
        });

        const dataPromise = resolveRouteBootstrapData(normalized).then((payload) => {
            setState((current) => current.path === normalized ? {
                ...current,
                payload,
                dataStatus: 'ready',
            } : current);
            return payload;
        }).catch((error) => {
            setState((current) => current.path === normalized ? {
                ...current,
                status: 'error',
                dataStatus: 'error',
                error,
            } : current);
            throw error;
        });

        const promise = Promise.all([modulePromise, dataPromise]).then(([, payload]) => {
            setState((current) => current.path === normalized ? {
                ...current,
                payload,
                status: 'ready',
                moduleStatus: 'ready',
                dataStatus: 'ready',
                error: null,
            } : current);

            return payload;
        }).finally(() => {
            if (inFlightRef.current?.path === normalized) {
                inFlightRef.current = null;
            }
        });

        inFlightRef.current = { path: normalized, promise };

        return promise;
    }, []);

    const getPayloadForPath = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.status === 'ready' && stateRef.current.path === normalized) {
            return stateRef.current.payload;
        }

        return null;
    }, []);

    return (
        <RouteBootstrapContext.Provider value={{ state, prepareRoute, getPayloadForPath }}>
            {children}
        </RouteBootstrapContext.Provider>
    );
};

export const useRouteBootstrap = () => {
    const context = useContext(RouteBootstrapContext);

    if (!context) {
        throw new Error('useRouteBootstrap must be used within a RouteBootstrapProvider');
    }

    return context;
};

export const useCurrentRouteBootstrap = <T = any>() => {
    const location = useLocation();
    const { getPayloadForPath } = useRouteBootstrap();

    return getPayloadForPath(`${location.pathname}${location.search}`) as T | null;
};
