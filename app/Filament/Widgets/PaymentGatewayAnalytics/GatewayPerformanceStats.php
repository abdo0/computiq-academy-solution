<?php

namespace App\Filament\Widgets\PaymentGatewayAnalytics;

use App\Enums\TransactionStatus;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GatewayPerformanceStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $data = Cache::remember('payment_gateway_performance_stats', 300, function (): array {
            $volume30d = (float) Transaction::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->sum(DB::raw('COALESCE(total_amount, amount, 0)'));

            $successCount30d = Transaction::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->where('status', TransactionStatus::COMPLETED)
                ->count();

            $totalCount30d = Transaction::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            $successRate = $totalCount30d > 0
                ? ($successCount30d / $totalCount30d) * 100
                : 0;

            $topGatewayId = Transaction::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('payment_gateway_id')
                ->select('payment_gateway_id')
                ->groupBy('payment_gateway_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('payment_gateway_id');

            $topGateway = $topGatewayId ? PaymentGateway::find($topGatewayId) : null;

            return [
                'active_gateways' => PaymentGateway::query()->where('is_active', true)->count(),
                'volume_30d' => $volume30d,
                'success_count_30d' => $successCount30d,
                'success_rate' => $successRate,
                'top_gateway' => $topGateway?->getTranslation('name', app()->getLocale())
                    ?: $topGateway?->getTranslation('name', 'en')
                    ?: __('No Data'),
            ];
        });

        return [
            Stat::make(__('Active Gateways'), number_format($data['active_gateways']))
                ->description(__('Payment gateways currently enabled'))
                ->descriptionIcon(Heroicon::CreditCard)
                ->color('primary')
                ->chart([2, 2, 3, 3, 4, 4, 4]),

            Stat::make(__('Processed Volume (30d)'), money($data['volume_30d']))
                ->description(__('Total processed through all gateways'))
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success')
                ->chart([3, 4, 5, 6, 5, 7, 8]),

            Stat::make(__('Successful Payments (30d)'), number_format($data['success_count_30d']))
                ->description(__('Completed transactions in the last 30 days'))
                ->descriptionIcon(Heroicon::CheckBadge)
                ->color('info')
                ->chart([1, 2, 3, 4, 4, 5, 6]),

            Stat::make(__('Success Rate (30d)'), number_format($data['success_rate'], 1).'%')
                ->description(__('Across all payment gateways'))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color($data['success_rate'] >= 80 ? 'success' : 'warning')
                ->chart([60, 68, 72, 75, 78, 82, 85]),

            Stat::make(__('Top Gateway'), $data['top_gateway'])
                ->description(__('Most-used gateway in the last 30 days'))
                ->descriptionIcon(Heroicon::Trophy)
                ->color('gray'),
        ];
    }
}
