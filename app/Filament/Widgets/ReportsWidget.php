<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ReportsWidget extends Widget
{
    protected string $view = 'filament.widgets.reports-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getReports(): array
    {
        return [
            [
                'title' => __('User Reports'),
                'description' => __('Analyze user data and activity'),
                'icon' => 'heroicon-o-user-group',
                'color' => 'info',
                'url' => route('filament.admin.resources.users.index'),
                'stats' => [
                    __('Total Users') => \App\Models\User::count(),
                    __('Active Users') => \App\Models\User::where('is_active', true)->count(),
                    __('New This Month') => \App\Models\User::where('created_at', '>=', now()->startOfMonth())->count(),
                    __('Recent Activity') => \App\Models\User::where('updated_at', '>=', now()->subDays(7))->count(),
                ],
            ],
        ];
    }

    public function getChartData(): array
    {
        return [
            'users' => [
                'labels' => ['Active Users', 'Inactive Users', 'New This Month', 'Recent Activity'],
                'data' => [
                    \App\Models\User::where('is_active', true)->count(),
                    \App\Models\User::where('is_active', false)->count(),
                    \App\Models\User::where('created_at', '>=', now()->startOfMonth())->count(),
                    \App\Models\User::where('updated_at', '>=', now()->subDays(7))->count(),
                ],
                'colors' => ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
            ],
        ];
    }
}
