<?php

namespace App\Filament\Widgets\FinancialReports;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinancialStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $data = Cache::remember('financial_report_stats', 300, function (): array {
            $completedRevenue = (float) Transaction::query()
                ->where('status', TransactionStatus::COMPLETED)
                ->sum(DB::raw('COALESCE(total_amount, amount, 0)'));

            $completedCount = Transaction::query()
                ->where('status', TransactionStatus::COMPLETED)
                ->count();

            $failedCount = Transaction::query()
                ->where('status', TransactionStatus::FAILED)
                ->count();

            $processingCount = Transaction::query()
                ->whereIn('status', [
                    TransactionStatus::PENDING->value,
                    TransactionStatus::PROCESSING->value,
                ])
                ->count();

            $averageTransactionValue = $completedCount > 0
                ? $completedRevenue / $completedCount
                : 0;

            return [
                'completed_revenue' => $completedRevenue,
                'completed_count' => $completedCount,
                'failed_count' => $failedCount,
                'processing_count' => $processingCount,
                'average_transaction_value' => $averageTransactionValue,
            ];
        });

        return [
            Stat::make(__('Completed Revenue'), money($data['completed_revenue']))
                ->description(__('Total revenue from completed transactions'))
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success')
                ->chart([3, 5, 4, 6, 8, 7, 9]),

            Stat::make(__('Completed Transactions'), number_format($data['completed_count']))
                ->description(__('Successfully completed payments'))
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('primary')
                ->chart([2, 3, 5, 4, 6, 5, 7]),

            Stat::make(__('Average Transaction Value'), money($data['average_transaction_value']))
                ->description(__('Average completed payment amount'))
                ->descriptionIcon(Heroicon::ChartBar)
                ->color('info')
                ->chart([1, 2, 2, 3, 4, 3, 5]),

            Stat::make(__('Pending or Processing'), number_format($data['processing_count']))
                ->description(__('Transactions still awaiting completion'))
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning')
                ->chart([4, 4, 3, 3, 2, 2, 1]),

            Stat::make(__('Failed Transactions'), number_format($data['failed_count']))
                ->description(__('Transactions that did not complete'))
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger')
                ->chart([1, 1, 2, 1, 2, 2, 3]),
        ];
    }
}
