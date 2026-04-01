<?php

namespace App\Filament\Widgets\FinancialReports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class TransactionStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return __('Transaction Status Distribution');
    }

    protected function getData(): array
    {
        $data = Cache::remember('financial_transaction_status_chart', 300, function (): array {
            $statuses = collect(TransactionStatus::cases())->map(function (TransactionStatus $status) {
                return [
                    'label' => $status->getLabel(),
                    'count' => Transaction::query()->where('status', $status)->count(),
                ];
            })->filter(fn (array $item) => $item['count'] > 0)->values();

            if ($statuses->isEmpty()) {
                return [
                    'labels' => [__('No Transactions')],
                    'data' => [1],
                    'colors' => ['#94a3b8'],
                ];
            }

            return [
                'labels' => $statuses->pluck('label')->all(),
                'data' => $statuses->pluck('count')->all(),
                'colors' => [
                    '#f59e0b',
                    '#3b82f6',
                    '#10b981',
                    '#ef4444',
                    '#6b7280',
                    '#8b5cf6',
                    '#06b6d4',
                ],
            ];
        });

        return [
            'datasets' => [
                [
                    'data' => $data['data'],
                    'backgroundColor' => array_slice($data['colors'], 0, count($data['data'])),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
