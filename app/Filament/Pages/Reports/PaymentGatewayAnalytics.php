<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\PaymentGatewayAnalytics\GatewayPerformanceStats;
use App\Filament\Widgets\PaymentGatewayAnalytics\GatewaySuccessRateChart;
use App\Filament\Widgets\PaymentGatewayAnalytics\GatewayVolumeChart;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

class PaymentGatewayAnalytics extends Dashboard
{
    protected static string $routePath = 'reports/payment-gateway-analytics';

    protected static ?string $navigationLabel = 'Payment Gateway Analytics';

    public static function getNavigationLabel(): string
    {
        return __('Payment Gateway Analytics');
    }

    public function getTitle(): string
    {
        return __('Payment Gateway Analytics');
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CreditCard;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Analytics');
    }

    public function getWidgets(): array
    {
        return [
            GatewayPerformanceStats::class,
            GatewayVolumeChart::class,
            GatewaySuccessRateChart::class,
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
