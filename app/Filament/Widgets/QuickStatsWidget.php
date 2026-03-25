<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [

            Stat::make(__('Total Users'), User::where('is_active', true)->count())
                ->description(__('Active users in the system'))
                ->descriptionIcon(Heroicon::UserGroup)
                ->color('warning'),

        ];
    }
}
