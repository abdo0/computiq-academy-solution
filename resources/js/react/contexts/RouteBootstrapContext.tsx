import React, { createContext, ReactNode, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { loadRouteModule, normalizeRouteTarget } from '../routing/routeRegistry';
import { resolveRouteBootstrapData } from '../services/routeBootstrap';

type AsyncStatus = 'idle' | 'loading' | 'ready' | 'error';
type NavigationStatus = 'idle' | 'preparing' | 'transitioning' | 'committed' | 'error';

interface RouteBootstrapState {
    renderedPath: string | null;
    renderedPayload: any | null;
    pendingTargetPath: string | null;
    pendingPreparedPath: string | null;
    pendingPayload: any | null;
    navigationStatus: NavigationStatus;
    moduleStatus: AsyncStatus;
    dataStatus: AsyncStatus;
    error: unknown;
}

interface RouteBootstrapContextType {
    state: RouteBootstrapState;
    prepareRoute: (path: string) => Promise<any>;
    beginRouteTransition: (path: string) => void;
    commitRenderedRoute: (path: string) => void;
    waitForRenderedRoute: (path: string) => Promise<void>;
    isNavigationPending: () => boolean;
    getPreparedPayloadForPath: (path: string) => any | null;
    getRenderedPayloadForPath: (path: string) => any | null;
}

const initialState: RouteBootstrapState = {
    renderedPath: null,
    renderedPayload: null,
    pendingTargetPath: null,
    pendingPreparedPath: null,
    pendingPayload: null,
    navigationStatus: 'idle',
    moduleStatus: 'idle',
    dataStatus: 'idle',
    error: null,
};

const RouteBootstrapContext = createContext<RouteBootstrapContextType | undefined>(undefined);

export const RouteBootstrapProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [state, setState] = useState<RouteBootstrapState>(initialState);
    const stateRef = useRef<RouteBootstrapState>(initialState);
    const inFlightRef = useRef<{ path: string; promise: Promise<any> } | null>(null);
    const renderWaitersRef = useRef<Map<string, Set<() => void>>>(new Map());

    const updateState = useCallback((updater: (current: RouteBootstrapState) => RouteBootstrapState) => {
        setState((current) => {
            const nextState = updater(current);
            stateRef.current = nextState;
            return nextState;
        });
    }, []);

    useEffect(() => {
        stateRef.current = state;
    }, [state]);

    const resolveRenderedWaiters = useCallback((path: string) => {
        const waiters = renderWaitersRef.current.get(path);

        if (!waiters) {
            return;
        }

        waiters.forEach((resolve) => resolve());
        renderWaitersRef.current.delete(path);
    }, []);

    const prepareRoute = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.renderedPath === normalized && stateRef.current.renderedPayload !== null) {
            return Promise.resolve(stateRef.current.renderedPayload);
        }

        if (stateRef.current.pendingPreparedPath === normalized && stateRef.current.pendingPayload !== null) {
            return Promise.resolve(stateRef.current.pendingPayload);
        }

        if (inFlightRef.current?.path === normalized) {
            return inFlightRef.current.promise;
        }

        updateState((current) => ({
            ...current,
            pendingTargetPath: normalized,
            pendingPreparedPath: current.pendingPreparedPath === normalized ? current.pendingPreparedPath : null,
            pendingPayload: current.pendingPreparedPath === normalized ? current.pendingPayload : null,
            navigationStatus: current.navigationStatus === 'transitioning' && current.pendingTargetPath === normalized
                ? 'transitioning'
                : 'preparing',
            moduleStatus: 'loading',
            dataStatus: 'loading',
            error: null,
        }));

        const modulePromise = loadRouteModule(normalized).then(() => {
            updateState((current) => current.pendingTargetPath === normalized ? {
                ...current,
                moduleStatus: 'ready',
            } : current);
        }).catch((error) => {
            updateState((current) => current.pendingTargetPath === normalized ? {
                ...current,
                navigationStatus: 'error',
                moduleStatus: 'error',
                error,
            } : current);
            throw error;
        });

        const dataPromise = resolveRouteBootstrapData(normalized).then((payload) => {
            updateState((current) => current.pendingTargetPath === normalized ? {
                ...current,
                pendingPreparedPath: normalized,
                pendingPayload: payload,
                dataStatus: 'ready',
            } : current);
            return payload;
        }).catch((error) => {
            updateState((current) => current.pendingTargetPath === normalized ? {
                ...current,
                navigationStatus: 'error',
                dataStatus: 'error',
                error,
            } : current);
            throw error;
        });

        const promise = Promise.all([modulePromise, dataPromise]).then(([, payload]) => {
            updateState((current) => current.pendingTargetPath === normalized ? {
                ...current,
                pendingPreparedPath: normalized,
                pendingPayload: payload,
                navigationStatus: current.navigationStatus === 'transitioning' ? 'transitioning' : 'preparing',
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
    }, [updateState]);

    const beginRouteTransition = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        updateState((current) => ({
            ...current,
            pendingTargetPath: normalized,
            navigationStatus: 'transitioning',
            error: null,
        }));
    }, [updateState]);

    const commitRenderedRoute = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        updateState((current) => {
            const nextPayload =
                current.pendingPreparedPath === normalized
                    ? current.pendingPayload
                    : current.renderedPath === normalized
                        ? current.renderedPayload
                        : null;

            return {
                renderedPath: normalized,
                renderedPayload: nextPayload,
                pendingTargetPath: null,
                pendingPreparedPath: null,
                pendingPayload: null,
                navigationStatus: 'committed',
                moduleStatus: nextPayload !== null ? 'ready' : current.moduleStatus,
                dataStatus: nextPayload !== null ? 'ready' : current.dataStatus,
                error: null,
            };
        });

        resolveRenderedWaiters(normalized);
    }, [resolveRenderedWaiters, updateState]);

    const waitForRenderedRoute = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.renderedPath === normalized) {
            return Promise.resolve();
        }

        return new Promise<void>((resolve) => {
            const currentWaiters = renderWaitersRef.current.get(normalized) ?? new Set<() => void>();
            const resolver = () => {
                currentWaiters.delete(resolver);
                resolve();

                if (currentWaiters.size === 0) {
                    renderWaitersRef.current.delete(normalized);
                }
            };

            currentWaiters.add(resolver);
            renderWaitersRef.current.set(normalized, currentWaiters);
        });
    }, []);

    const isNavigationPending = useCallback(() => {
        return stateRef.current.navigationStatus === 'preparing'
            || stateRef.current.navigationStatus === 'transitioning';
    }, []);

    const getPreparedPayloadForPath = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.pendingPreparedPath === normalized) {
            return stateRef.current.pendingPayload;
        }

        if (stateRef.current.renderedPath === normalized) {
            return stateRef.current.renderedPayload;
        }

        return null;
    }, []);

    const getRenderedPayloadForPath = useCallback((path: string) => {
        const normalized = normalizeRouteTarget(path).fullPath;

        if (stateRef.current.renderedPath === normalized) {
            return stateRef.current.renderedPayload;
        }

        return null;
    }, []);

    return (
        <RouteBootstrapContext.Provider
            value={{
                state,
                prepareRoute,
                beginRouteTransition,
                commitRenderedRoute,
                waitForRenderedRoute,
                isNavigationPending,
                getPreparedPayloadForPath,
                getRenderedPayloadForPath,
            }}
        >
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
    const context = useContext(RouteBootstrapContext);

    return (context?.state.renderedPayload as T | null) ?? null;
};
