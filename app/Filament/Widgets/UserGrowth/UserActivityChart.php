<?php

namespace App\Filament\Widgets\UserGrowth;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class UserActivityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return __('User Activity Distribution');
    }

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $cacheKey = 'user_activity_chart_edu';

        $data = Cache::remember($cacheKey, 300, function () {
            $activeUsers = User::where('is_active', true)->count();
            $inactiveUsers = User::where('is_active', false)->count();

            return [
                'labels' => [
                    __('Active Users'),
                    __('Inactive Users'),
                ],
                'data' => [
                    $activeUsers,
                    $inactiveUsers,
                ],
                'colors' => [
                    '#10b981', // Active Users - green
                    '#ef4444', // Inactive Users - red
                ],
            ];
        });

        return [
            'datasets' => [
                [
                    'data' => $data['data'],
                    'backgroundColor' => $data['colors'],
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

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
