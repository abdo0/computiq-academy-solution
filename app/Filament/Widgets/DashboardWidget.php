<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class DashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    /**
     * Get alerts for urgent items requiring attention
     */
    public function getAlerts(): array
    {
        return Cache::remember('dashboard.alerts', 60, function () {
            $unreadMessages = ContactMessage::where('is_read', false)->count();

            return [
                [
                    'type' => 'info',
                    'title' => __('Unread Contact Messages'),
                    'count' => $unreadMessages,
                    'icon' => 'heroicon-o-envelope',
                    'color' => 'info',
                    'url' => '#',
                    'priority' => 2,
                ],
            ];
        });
    }

    /**
     * Get quick stats for key metrics
     */
    public function getQuickStats(): array
    {
        return Cache::remember('dashboard.quick_stats', 120, function () {
            $totalCourses = Course::where('is_active', true)->count();
            $totalCategories = CourseCategory::where('is_active', true)->count();
            $totalUsers = User::where('is_active', true)->count();

            return [
                [
                    'title' => __('Active Courses'),
                    'value' => number_format($totalCourses),
                    'description' => __('Published courses'),
                    'icon' => Heroicon::AcademicCap,
                    'color' => 'info',
                    'url' => '#',
                ],
                [
                    'title' => __('Course Categories'),
                    'value' => number_format($totalCategories),
                    'description' => __('Active categories'),
                    'icon' => Heroicon::RectangleStack,
                    'color' => 'primary',
                    'url' => '#',
                ],
                [
                    'title' => __('Total Users'),
                    'value' => number_format($totalUsers),
                    'description' => __('Active users in the system'),
                    'icon' => Heroicon::Users,
                    'color' => 'warning',
                    'url' => '#',
                ],
            ];
        });
    }

    /**
     * Get reports for different sections
     */
    public function getReports(): array
    {
        return Cache::remember('dashboard.reports', 300, function () {
            return [
                [
                    'title' => __('Course Overview'),
                    'description' => __('Analyze course data and student engagement'),
                    'icon' => 'heroicon-o-academic-cap',
                    'color' => 'info',
                    'url' => '#',
                    'stats' => [
                        __('Total Courses') => Course::count(),
                        __('Active') => Course::where('is_active', true)->count(),
                        __('Live Courses') => Course::where('is_live', true)->count(),
                        __('Best Sellers') => Course::where('is_best_seller', true)->count(),
                    ],
                ],
            ];
        });
    }

    /**
     * Get chart data for reports
     */
    public function getChartData(): array
    {
        return Cache::remember('dashboard.chart_data', 300, function () {
            return [
                'courses' => [
                    'labels' => [__('Active'), __('Inactive'), __('Live'), __('Best Seller')],
                    'data' => [
                        Course::where('is_active', true)->count(),
                        Course::where('is_active', false)->count(),
                        Course::where('is_live', true)->count(),
                        Course::where('is_best_seller', true)->count(),
                    ],
                    'colors' => ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
                ],
            ];
        });
    }
}
