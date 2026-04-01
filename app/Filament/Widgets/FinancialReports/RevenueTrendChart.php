<?php

namespace App\Filament\Widgets\FinancialReports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        return __('Revenue Trend (Last 30 Days)');
    }

    protected function getData(): array
    {
        $data = Cache::remember('financial_revenue_trend_chart', 300, function (): array {
            $rows = Transaction::query()
                ->where('status', TransactionStatus::COMPLETED)
                ->where('created_at', '>=', now()->subDays(29)->startOfDay())
                ->selectRaw('DATE(created_at) as report_date')
                ->selectRaw('SUM(COALESCE(total_amount, amount, 0)) as revenue_total')
                ->groupBy('report_date')
                ->orderBy('report_date')
                ->get()
                ->keyBy('report_date');

            $labels = [];
            $values = [];

            foreach (range(29, 0) as $daysAgo) {
                $date = now()->subDays($daysAgo);
                $key = $date->toDateString();
                $labels[] = $date->format('M j');
                $values[] = (float) ($rows[$key]->revenue_total ?? 0);
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('Revenue'),
                    'data' => $data['values'],
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
