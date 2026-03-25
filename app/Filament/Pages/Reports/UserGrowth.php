<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\UserGrowth\UserActivityChart;
use App\Filament\Widgets\UserGrowth\UserGrowthStats;
use App\Filament\Widgets\UserGrowth\UserRegistrationChart;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

class UserGrowth extends Dashboard
{
    protected static string $routePath = 'reports/user-growth';

    protected static ?string $navigationLabel = 'User Growth';

    public static function getNavigationLabel(): string
    {
        return __('User Growth');
    }

    public function getTitle(): string
    {
        return __('User Growth');
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Analytics');
    }

    public function getWidgets(): array
    {
        return [
            UserGrowthStats::class,
            UserRegistrationChart::class,
            UserActivityChart::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }
}
