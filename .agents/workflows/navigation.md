---
description: How to create a new page, add navigation links, and wire up route bootstrap data in this React SPA
---

# Navigation System — Computiq Academy

This project uses a custom **Inertia.js-style** SPA navigation system built on top of React Router v6.
It pre-fetches both the **JS chunk** and **API data** for a page _before_ transitioning the URL, so the user sees a
progress bar while loading and the page appears instantly with data — no spinners, no empty flashes.

> **CRITICAL**: Do NOT use `<Link>` from `react-router-dom` or raw `<a>` tags for internal navigation.
> Always use `<AppLink>` or the `useAppNavigate()` hook.

---

## Architecture Overview

```
User clicks link
       │
       ▼
  AppLink / useAppNavigate()
       │
       ├─ NProgress.start()
       │
       ├─ prepareRoute(path)
       │    ├─ loadRouteModule(path)   ← downloads the JS chunk via eagerRoute closure
       │    └─ resolveRouteBootstrapData(path)  ← fetches API data (SEO + page data)
       │
       ├─ beginRouteTransition(path)
       ├─ navigate(path)   ← React Router pushes URL
       ├─ waitForRenderedRoute(path)
       ├─ waitForNextPaint()   ← double-rAF ensures browser has painted
       │
       └─ NProgress.done()
```

### Key Files

| File | Purpose |
|------|---------|
| `routing/routeRegistry.ts` | `eagerRoute()`, `loadRouteModule()`, `normalizeRouteTarget()`, `localizeAppPath()` |
| `hooks/useAppNavigate.ts` | Custom navigation hook (progress bar + prefetch) |
| `components/common/AppLink.tsx` | Drop-in `<a>` replacement that uses `useAppNavigate()` |
| `contexts/RouteBootstrapContext.tsx` | State machine for route data (preparing → transitioning → committed) |
| `services/routeBootstrap.ts` | `resolveRouteBootstrapData()` — maps routes to their API calls |
| `App.tsx` | Route definitions, `LayoutWrapper`, `displayLocation` logic |

---

## How To: Add a New Page (Full Checklist)

Follow these **5 steps** in order. Missing any step will break navigation.

### Step 1: Create the Page Component

Create a new file in `resources/js/react/components/`.

```tsx
// resources/js/react/components/MyNewPage.tsx
import React, { useState, useEffect } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';
import Seo from './Seo';
import AppLink from './common/AppLink';

const MyNewPage: React.FC = () => {
    const { dir } = useLanguage();
    const { __ } = useTranslation();
    
    // 1. Consume pre-loaded bootstrap data (loaded during progress bar)
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const initialItems = initialBootstrap?.items || [];
    
    // 2. Initialize state from bootstrap (instant render, no loading spinner)
    const [items, setItems] = useState<any[]>(() => initialItems);
    const [isLoading, setIsLoading] = useState(() => initialItems.length === 0);
    
    // 3. Fallback fetch — only runs if bootstrap didn't provide data
    //    (e.g. direct URL visit before bootstrap resolves, or browser back/forward)
    useEffect(() => {
        if (initialItems.length > 0) {
            setItems(initialItems);
            setIsLoading(false);
            return;
        }
        const fetchData = async () => {
            try {
                const data = await dataService.getMyItems();
                setItems(data || []);
            } catch (error) {
                console.error('Failed to fetch items', error);
            } finally {
                setIsLoading(false);
            }
        };
        fetchData();
    }, [initialItems.length]);

    return (
        <div className="min-h-screen py-10 pb-24">
            <Seo title={__('My Page Title')} description={__('My page description')} />
            {/* Page content using `items` */}
        </div>
    );
};

export default MyNewPage;
```

> **IMPORTANT**: Always use `useCurrentRouteBootstrap()` to get pre-loaded data.
> Initialize `useState` from bootstrap data using a **function initializer** `() => initialItems`.
> This prevents empty-state flashes.

### Step 2: Register the Route Component in `App.tsx`

Add an `eagerRoute()` declaration at the top of `App.tsx` (around lines 12-36):

```tsx
// In App.tsx — top-level component declarations
const MyNewPage = eagerRoute('/my-page', () => import('./components/MyNewPage'));
```

For **dynamic routes** (with URL parameters), use a pattern key:

```tsx
const MyItemDetailPage = eagerRoute('/items/:slug', () => import('./components/MyItemDetailPage'));
```

Then add the `<Route>` element inside the `commonRoutes` fragment (around line 319):

```tsx
<Route path="my-page" element={<MyNewPage />} />
```

For dynamic routes:

```tsx
<Route path="items/:slug" element={<MyItemDetailPage />} />
```

> **IMPORTANT**: The `moduleKey` in `eagerRoute()` must match what `loadRouteModule()` resolves.
> For static routes (e.g. `/paths`), the key IS the pathname.
> For dynamic routes (e.g. `/paths/frontend`), you must use the pattern key `/paths/:slug`.

### Step 3: Add Bootstrap Data Resolution

Open `services/routeBootstrap.ts` and add a new `if` block inside `resolveRouteBootstrapData()`:

```ts
// In resolveRouteBootstrapData()
if (pathname === '/my-page') {
    const [seo, items] = await Promise.all([
        seoPromise,
        dataService.getMyItems().catch(() => null),
    ]);
    return { path: fullPath, seo, items };
}
```

For dynamic routes:

```ts
if (pathname.startsWith('/items/')) {
    const itemSlug = pathname.replace('/items/', '');
    const [seo, item] = await Promise.all([
        seoPromise,
        dataService.getItemBySlug(itemSlug).catch(() => null),
    ]);
    return { path: fullPath, seo, item };
}
```

> **CRITICAL**: Always include `seoPromise` in the `Promise.all()`.
> Always use `.catch(() => null)` on data fetches — failed API calls must not block navigation.
> Always return `{ path: fullPath, seo, ...yourData }`.

### Step 4: Add Dynamic Route Mapping to `loadRouteModule()` (dynamic routes only)

If your route has URL parameters (`:slug`, `:id`, etc.), add a mapping in `routing/routeRegistry.ts`
inside `loadRouteModule()`:

```ts
// In loadRouteModule() — the "if (!routeEntries.has(moduleKey))" block
if (pathname.startsWith('/items/')) moduleKey = '/items/:slug';
```

This maps the actual URL `/items/my-item` to the pattern key `/items/:slug` that
was registered by `eagerRoute()`.

> **NOTE**: Static routes (like `/paths`, `/blog`, `/faq`) do NOT need this step.
> The pathname already matches the moduleKey exactly.

### Step 5: Add the API Service Method (if needed)

Add the data-fetching method to `services/dataService.ts`:

```ts
getMyItems: async (): Promise<any> => {
    try {
        const response = await api.get('/my-items');
        return response.data.data;
    } catch (error) {
        console.error('API fetch failed for my items.', error);
        return null;
    }
},
```

---

## How To: Create Navigation Links

### Use `<AppLink>` (preferred)

```tsx
import AppLink from './common/AppLink';

// Use locale-independent paths (no /en/ or /ar/ prefix)
<AppLink to="/paths" className="...">مسارات التعلم</AppLink>
<AppLink to="/courses/react-basics">React Basics</AppLink>
<AppLink to={`/blog/${post.slug}`}>Read More</AppLink>
```

### Use `useAppNavigate()` hook (for programmatic navigation)

```tsx
import { useAppNavigate } from '../hooks/useAppNavigate';

const MyComponent = () => {
    const navigate = useAppNavigate();
    
    const handleClick = () => {
        navigate('/courses');           // SPA navigation with progress bar
        navigate(-1);                   // Go back (browser history)
        navigate('/login', { replace: true }); // Replace current history entry
    };
};
```

### ❌ NEVER DO THIS

```tsx
// WRONG — bypasses the custom navigation system:
import { Link } from 'react-router-dom';
<Link to="/paths">...</Link>           // ❌ No progress bar, no data prefetch

<a href="/paths">...</a>               // ❌ Full page reload

const navigate = useNavigate();         // ❌ Raw React Router navigate
navigate('/paths');
```

---

## How To: Consume Bootstrap Data in a Page Component

The bootstrap payload is available via `useCurrentRouteBootstrap()`:

```tsx
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const MyPage = () => {
    const bootstrap = useCurrentRouteBootstrap<any>();
    
    // The shape matches what you returned in resolveRouteBootstrapData()
    // e.g. { path, seo, items } → bootstrap?.items
    const items = bootstrap?.items || [];
};
```

The `seo` field is automatically consumed by the `<SeoWrapper>` component in the layout.
You do **not** need to manually apply SEO data. Just use the `<Seo>` component for
page-specific overrides (title, description).

---

## Localization

- Paths are **locale-independent** in code: always use `/paths`, not `/en/paths` or `/ar/paths`.
- The `localizeAppPath()` function and `<LocaleSync>` component handle prefixing automatically.
- Arabic (`ar`) is the default locale and has NO prefix. Other locales get a prefix: `/en/paths`, `/ku/paths`.
- `normalizeRouteTarget()` strips locale prefixes when matching routes internally.

---

## Quick Reference: Existing Route Keys

| Module Key | Component File | Has Bootstrap Data |
|------------|---------------|-------------------|
| `/` | `Home.tsx` | ✅ homeData |
| `/about` | `AboutPage.tsx` | ✅ pageInfo |
| `/courses` | `CoursesPage.tsx` | ✅ courses, categories |
| `/courses/:slug` | `CourseDetailsPage.tsx` | ✅ course |
| `/paths` | `PathsPage.tsx` | ✅ paths |
| `/paths/:slug` | `PathDetailPage.tsx` | ✅ pathData, allPaths |
| `/blog` | `BlogPage.tsx` | ✅ posts, pageInfo |
| `/blog/:slug` | `BlogPostDetail.tsx` | ✅ post, recentPosts |
| `/faq` | `FaqPage.tsx` | ✅ faqs, pageInfo |
| `/instructors/:slug` | `InstructorProfilePage.tsx` | ✅ instructor |
| `/contact` | `ContactPage.tsx` | ✅ pageInfo |
| `/how-it-works` | `HowItWorksPage.tsx` | ✅ pageInfo |
| `/guide` | `GuidePage.tsx` | ✅ page |
| `/success-stories` | `SuccessStoriesPage.tsx` | ✅ page |
| `/page/:slug` | `CmsPage.tsx` | ✅ page |
| `/search` | `SearchPage.tsx` | ✅ query, results |
| `/cart` | `CartPage.tsx` | ✅ cart |
| `/checkout` | `CheckoutPage.tsx` | ✅ checkout |
| `/dashboard` | `DashboardPage.tsx` | ✅ dashboardStats, cart |
| `/login` | `LoginPage.tsx` | SEO only |
| `/signup` | `SignupPage.tsx` | SEO only |
| `/forgot-password` | `ForgotPasswordPage.tsx` | SEO only |
| `/reset-password` | `ResetPasswordPage.tsx` | SEO only |

---

## Common Mistakes & Debugging

### Page shows empty then content appears after a delay
- You're using `React.lazy()` instead of `eagerRoute()`.
- Or you forgot to add the route to `resolveRouteBootstrapData()`.

### Progress bar doesn't appear on navigation
- You're using `<Link>` or `useNavigate()` from react-router-dom instead of `<AppLink>` / `useAppNavigate()`.

### Bootstrap data is `null` even though the API works
- Check that `resolveRouteBootstrapData()` has a matching `if` block for your route pathname.
- Make sure the returned object keys match what you're reading in the component.

### Page doesn't load at all (blank screen)
- Check that `eagerRoute()` moduleKey matches `loadRouteModule()` mapping for dynamic routes.
- Check the browser console for chunk loading errors.
