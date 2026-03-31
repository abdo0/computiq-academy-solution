<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ActivityLogWidget;
use App\Filament\Widgets\CustomAccountWidget;
use App\Http\Middleware\Panels\AdminPanelAuthenticate;
use App\Http\Middleware\TrackUserActivity;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Sky,
            ])
            ->breadcrumbs(false)
            ->brandName(fn () => settings('app_name'))
            ->brandLogo(fn () => view('filament.components.custom-brand'))
            ->favicon(fn () => settings('company_favicon') ?: settings('favicon'))
            ->brandLogoHeight('40px')
            ->homeUrl('/')
            ->font('Noto Sans Arabic')
            ->profile(isSimple: false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('300px')
            ->sidebarCollapsibleOnDesktop()
            ->spa(hasPrefetching: true)
            ->databaseNotifications()
            ->databaseTransactions()
            ->databaseNotificationsPolling('30s')
            ->unsavedChangesAlerts()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                'panels::head.end',
                fn (): string => '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />'
                    .'<link rel="apple-touch-icon" sizes="180x180" href="'.(settings('logo') ?: settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="icon" type="image/png" sizes="32x32" href="'.(settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="icon" type="image/png" sizes="16x16" href="'.(settings('favicon') ?: asset('favicon.ico')).'" />'
                    .'<link rel="manifest" href="'.route('manifest').'" />'
                    .'<meta name="theme-color" content="#ffffff" />'
                    .'<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'
                    .'<style>
                        .fi-wi-chart-container,
                        .fi-wi-chart-container > div,
                        [data-widget-chart-container],
                        .fi-wi-chart-container > canvas,
                        .fi-section-content > div:has(canvas),
                        .fi-wi-chart-container canvas {
                            max-height: 300px !important;
                        }
                        .fi-wi-chart-container {
                            overflow: hidden !important;
                        }
                    </style>'
            )
            ->renderHook(
                'panels::footer',
                fn (): string => view('filament.components.copyright-footer')->render()
            )
            ->renderHook(
                'panels::scripts.before',
                fn (): string => view('filament.components.vite-assets')->render()
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                CustomAccountWidget::class,
                // ActivityLogWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Courses & Learning')),
                NavigationGroup::make()->label(fn () => __('Content Management')),
                NavigationGroup::make()->label(fn () => __('Communication')),
                NavigationGroup::make()->label(fn () => __('User Management')),
                NavigationGroup::make()->label(fn () => __('Settings')),
                NavigationGroup::make()->label(fn () => __('System')),
            ])
            ->plugins([

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                TrackUserActivity::class, // Track user activity for online status
            ])
            ->authMiddleware([
                AdminPanelAuthenticate::class,
            ]);
    }
}
