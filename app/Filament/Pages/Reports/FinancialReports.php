<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\FinancialReports\FinancialStatsWidget;
use App\Filament\Widgets\FinancialReports\RevenueTrendChart;
use App\Filament\Widgets\FinancialReports\TransactionStatusChart;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

class FinancialReports extends Dashboard
{
    protected static string $routePath = 'reports/financial';

    public static function getNavigationLabel(): string
    {
        return __('Financial Reports');
    }

    public function getTitle(): string
    {
        return __('Financial Reports');
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Analytics');
    }

    public function getWidgets(): array
    {
        return [
            FinancialStatsWidget::class,
            RevenueTrendChart::class,
            TransactionStatusChart::class,
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
