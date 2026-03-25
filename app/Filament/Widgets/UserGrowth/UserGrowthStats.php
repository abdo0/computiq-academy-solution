<?php

namespace App\Filament\Widgets\UserGrowth;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UserGrowthStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $cacheKey = 'user_growth_stats_edu';

        $data = Cache::remember($cacheKey, 300, function () {
            // Admin Users
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $newUsers30d = User::where('created_at', '>=', now()->subDays(30))->count();
            $newUsers7d = User::where('created_at', '>=', now()->subDays(7))->count();

            // Calculate growth rate (30 days vs previous 30 days)
            $prev30dStart = now()->subDays(60);
            $prev30dEnd = now()->subDays(30);
            $prevNewUsers = User::whereBetween('created_at', [$prev30dStart, $prev30dEnd])->count();
            $growthRate = $prevNewUsers > 0 ? (($newUsers30d - $prevNewUsers) / $prevNewUsers) * 100 : ($newUsers30d > 0 ? 100 : 0);

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'new_users_30d' => $newUsers30d,
                'new_users_7d' => $newUsers7d,
                'growth_rate' => $growthRate,
            ];
        });

        return [
            Stat::make(__('Total Users'), number_format($data['total_users']))
                ->description(__('All admin users'))
                ->descriptionIcon(Heroicon::UserGroup)
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make(__('Active Users'), number_format($data['active_users']))
                ->description(__('Currently active'))
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success')
                ->chart([5, 4, 3, 7, 5, 4, 6]),

            Stat::make(__('New Users (30d)'), number_format($data['new_users_30d']))
                ->description(__('Registered in last 30 days'))
                ->descriptionIcon(Heroicon::UserPlus)
                ->color('info')
                ->chart([3, 5, 4, 6, 5, 7, 4]),

            Stat::make(__('Growth Rate'), number_format($data['growth_rate'], 1).'%')
                ->description(__('30-day user growth'))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color($data['growth_rate'] >= 0 ? 'success' : 'danger')
                ->chart([3, 4, 5, 4, 6, 5, 4]),
        ];
    }
}
