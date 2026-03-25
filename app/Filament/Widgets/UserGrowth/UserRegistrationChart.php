<?php

namespace App\Filament\Widgets\UserGrowth;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserRegistrationChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return __('User Registration Trend');
    }

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $cacheKey = 'user_registration_chart_edu';

        $data = Cache::remember($cacheKey, 300, function () {
            // Admin Users
            $users = User::where('created_at', '>=', now()->subDays(30))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $allDates = $users->pluck('date')->unique()->sort()->values();

            $userCounts = [];
            foreach ($allDates as $date) {
                $userCounts[] = $users->firstWhere('date', $date)->count ?? 0;
            }

            return [
                'labels' => $allDates->toArray(),
                'users' => $userCounts,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('Admin Users'),
                    'data' => $data['users'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}
