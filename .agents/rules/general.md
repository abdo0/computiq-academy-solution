# Computiq Academy — Project Rules

## Overview
Computiq Academy is an **online learning platform** (courses, instructors, enrollments). The backend admin panel is built with **Filament 4**, and the public-facing frontend is a **React 19 SPA** served through a single Blade entry point.

---

## Architecture

### Backend
| Layer | Location |
|---|---|
| Filament Resources | `app/Filament/Resources/` |
| Actions (business logic) | `app/Actions/` |
| Services | `app/Services/` |
| Observers | `app/Observers/` |
| Traits | `app/Traits/` |
| Enums | `app/Enums/` |
| Models | `app/Models/` |
| Helpers (auto-loaded) | `app/Helpers/` |
| Livewire components | `app/Livewire/` |
| API controllers | `app/Http/Controllers/` |

### Frontend (React SPA)
| Layer | Location |
|---|---|
| Entry point | `resources/js/react/index.tsx` |
| Pages & components | `resources/js/react/components/` |
| Auth components | `resources/js/react/components/auth/` |
| Shared/common | `resources/js/react/components/common/` |
| Home page sections | `resources/js/react/components/home/` |
| Organization pages | `resources/js/react/components/org/` |
| Contexts (state) | `resources/js/react/contexts/` |
| Hooks | `resources/js/react/hooks/` |
| Services (API) | `resources/js/react/services/` |
| Utilities | `resources/js/react/utils/` |
| Vite alias | `@` → `resources/js/react/` |

---

## Multi-Language Support (Critical)

The app supports **Arabic (ar)**, **Kurdish (ku)**, and **English (en)**.

### Frontend i18n
- Use `useLanguage()` for current language and `useTranslation()` for the `__()` and `t()` helpers.
- RTL detection: `const isRTL = language === 'ar' || language === 'ku';`
- **Never compare translated strings for state/logic** — use index-based or key-based comparison to avoid locale mismatch bugs.
- Wrap all user-facing text with `t()` (translatable content from API) or `__()` (static UI strings).
- **Translation Keys Naming Convention**: ALWAYS use natural English sentences/phrases for the `__()` function values (e.g., `__('Explore learning paths')`). **NEVER** use programmatic `snake_case` keys (e.g., `__('explore_paths_description')`). The English text itself acts as the default JSON key across the project.
- **CRITICAL**: After creating any new files or adding new translation labels (e.g., `__('New key')`), you **MUST** run `php artisan sync:translations` to automatically extract and inject the keys into `lang/en.json`, `lang/ar.json`, and `lang/ku.json`.

### SPA Language Switching Flow
When switching languages programmatically (e.g., in `Header.tsx`), **never use full page reloads** (`window.location.href`) or update state synchronously before fetching translations to avoid UI "tearing" (like `dir` flipping before text loads). Follow the strict "True SPA" atomic flow:
1. **Show Progress & Guard**: Call `NProgress.start()` and set `sessionStorage.setItem('language_switch_in_progress', '1')` to block `LocaleSync` interference.
2. **Prefetch Silently**: Call `prefetchLocale(lang)` from `useTranslation()` and `preloadPage(path)` concurrently to fetch translations and route data WITHOUT changing React state.
3. **Apply Atomically & Navigate**: In exactly this order:
   - `applyFetchedLocale(prefetched)` (updates state + globals instantly)
   - `setLanguage(lang)` (flips `dir` and HTML `lang`)
   - `rawNavigate(newPath)` (React Router SPA push)
   - `NProgress.done()`
4. **Cleanup**: Clear the `sessionStorage` guard after ~500ms.

### Backend i18n
- Translatable model fields use `solution-forest/filament-translate-field` in Filament forms.
- Custom translation helpers are in `app/Helpers/TranslationHelper.php` and `app/Helpers/TranslationGlobalHelpers.php`.

---

## Frontend Patterns

- **Navigation**: Always use the `AppLink` component (`components/common/AppLink`) — never raw `<a>` or React Router `<Link>`.
- **Icons**: Use `lucide-react`.
- **API calls**: Use `dataService` from `services/dataService`.
- **Notifications**: Use `react-toastify`.
- **Phone input**: Use `react-phone-input-2`.
- **SEO**: Use `react-helmet-async`.
- **Realtime**: `pusher-js` + `laravel-echo`.
- **AI features**: `@google/genai`.

---

## Navigation & Page Loading (Inertia.js-style)

The frontend uses a **custom Inertia.js-like navigation system** — pages are NOT loaded by default browser navigation. Instead, `AppLink` + `useAppNavigate` preload the JS chunk AND API data before navigating, giving a smooth SPA feel with NProgress bar.

### How It Works (Flow)
1. User clicks an `AppLink` → `useAppNavigate()` is called
2. `NProgress.start()` — progress bar begins
3. `preloadPage(path)` runs concurrently:
   - Loads the **JS chunk** (via `pageImportMap` in `App.tsx`)
   - Loads the **API data** (via `dataService`)
4. `NProgress.done()` — progress bar completes
5. React Router `navigate()` triggers the URL change + render (page is already cached)

### Key Files
| File | Purpose |
|---|---|
| `components/common/AppLink.tsx` | Drop-in link component — **always use this for links** |
| `hooks/useAppNavigate.ts` | Navigation hook with preloading + NProgress + locale prefix |
| `App.tsx` → `pageImportMap` | Maps paths → lazy `import()` for JS chunk preloading |
| `App.tsx` → `preloadPage()` | Preloads both JS chunk + API data before navigation |
| `components/common/ProgressBar.tsx` | NProgress bar configuration |

### Rules
- **Always use `AppLink`** for internal links — never `<a>`, `<Link>`, or `useNavigate()` directly
- **Always use `useAppNavigate()`** for programmatic navigation (e.g. after form submit)
- Paths are **locale-aware**: `useAppNavigate` auto-prefixes the path (`/about` → `/en/about`) for non-Arabic languages. Arabic (`ar`) is the default with no prefix.

### Adding a New Page
When creating a new page component, you must update **3 things** in `App.tsx`:

1. **Lazy import** at the top:
   ```tsx
   const NewPage = React.lazy(() => import('./components/NewPage'));
   ```

2. **`pageImportMap`** — add entry so `AppLink` can preload the chunk:
   ```tsx
   '/new-page': () => import('./components/NewPage'),
   ```

3. **Route definition** inside `commonRoutes`:
   ```tsx
   <Route path="new-page" element={<NewPage />} />
   ```

4. *(Optional)* If the page fetches API data, add a preload entry in `preloadPage()`:
   ```tsx
   else if (normalized === '/new-page') {
       dataPromises.push(dataService.getNewPageData().catch(() => null));
   }
   ```

### Page Component Pattern
All pages are **lazy-loaded** via `React.lazy()` and wrapped in `<Suspense>` with `GlobalPageLoader` as fallback. Pages should:
- Fetch their own data via `dataService` in a `useEffect`
- Show a loading spinner while data loads
- Use `useParams()` for slug-based routes (e.g. `/courses/:slug`)

---

## Key Integrations

| Feature | Package |
|---|---|
| Media uploads | `spatie/laravel-medialibrary` |
| Roles & permissions | `spatie/laravel-permission` |
| PDF generation | `barryvdh/laravel-dompdf` |
| Excel import/export | `maatwebsite/excel`, `pxlrbt/filament-excel` |
| Backups | `spatie/laravel-backup` |
| Payment | `waad/zaincash` |
| Phone input (admin) | `ysfkaya/filament-phone-input` |
| Language switch | `bezhansalleh/filament-language-switch` |

---

## Development Commands
- `composer dev` — Start Laravel server + queue + Vite concurrently
- `npm run dev` — Vite dev server only
- `npm run build` — Production build
- `npm run deploy` — Build + git commit + push
