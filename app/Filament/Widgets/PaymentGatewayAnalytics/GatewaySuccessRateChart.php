<?php

namespace App\Filament\Widgets\PaymentGatewayAnalytics;

use App\Enums\TransactionStatus;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class GatewaySuccessRateChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        return __('Gateway Success Rate (Last 30 Days)');
    }

    protected function getData(): array
    {
        $data = Cache::remember('gateway_success_rate_chart', 300, function (): array {
            $gateways = PaymentGateway::query()
                ->whereHas('transactions', fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
                ->with(['transactions' => fn ($query) => $query
                    ->where('created_at', '>=', now()->subDays(30))
                    ->select('id', 'payment_gateway_id', 'status'),
                ])
                ->get();

            if ($gateways->isEmpty()) {
                return [
                    'labels' => [__('No Data')],
                    'rates' => [0],
                ];
            }

            return [
                'labels' => $gateways->map(fn (PaymentGateway $gateway) => $gateway->getTranslation('name', app()->getLocale())
                    ?: $gateway->getTranslation('name', 'en')
                    ?: __('Unknown Gateway'))
                    ->all(),
                'rates' => $gateways->map(function (PaymentGateway $gateway) {
                    $total = $gateway->transactions->count();
                    $completed = $gateway->transactions
                        ->where('status', TransactionStatus::COMPLETED)
                        ->count();

                    return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                })->all(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('Success Rate %'),
                    'data' => $data['rates'],
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }
}
