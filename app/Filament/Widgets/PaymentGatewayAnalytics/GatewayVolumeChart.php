<?php

namespace App\Filament\Widgets\PaymentGatewayAnalytics;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GatewayVolumeChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '320px';

    public function getHeading(): ?string
    {
        return __('Gateway Volume (Last 30 Days)');
    }

    protected function getData(): array
    {
        $data = Cache::remember('gateway_volume_chart', 300, function (): array {
            $rows = Transaction::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('payment_gateway_id')
                ->select('payment_gateway_id')
                ->selectRaw('SUM(COALESCE(total_amount, amount, 0)) as volume_total')
                ->groupBy('payment_gateway_id')
                ->orderByDesc('volume_total')
                ->get();

            $gateways = PaymentGateway::query()
                ->whereIn('id', $rows->pluck('payment_gateway_id'))
                ->get()
                ->keyBy('id');

            if ($rows->isEmpty()) {
                return [
                    'labels' => [__('No Data')],
                    'values' => [0],
                ];
            }

            return [
                'labels' => $rows->map(function ($row) use ($gateways) {
                    $gateway = $gateways->get($row->payment_gateway_id);

                    return $gateway?->getTranslation('name', app()->getLocale())
                        ?: $gateway?->getTranslation('name', 'en')
                        ?: __('Unknown Gateway');
                })->all(),
                'values' => $rows->map(fn ($row) => (float) $row->volume_total)->all(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('Processed Volume'),
                    'data' => $data['values'],
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6',
                        '#ef4444',
                        '#06b6d4',
                    ],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
