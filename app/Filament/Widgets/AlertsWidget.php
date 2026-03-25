<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected string $view = 'filament.widgets.alerts-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getAlerts(): array
    {
        return [
            [
                'type' => 'info',
                'title' => __('Total Users'),
                'count' => \App\Models\User::where('is_active', true)->count(),
                'icon' => 'heroicon-o-users',
                'color' => 'info',
                'url' => route('filament.admin.resources.users.index'),
            ],
        ];
    }
}
