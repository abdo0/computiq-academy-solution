<?php

namespace App\Traits;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatusTabs
{
    public function getTabs(): array
    {
        $model = static::getModel();

        return [
            'all' => Tab::make(__('All'))
                ->icon('heroicon-o-squares-2x2')
                ->badge($model::count())
                ->badgeColor('primary'),

            'active' => Tab::make(__('Active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($model::where('is_active', true)->count())
                ->badgeColor('success'),

            'inactive' => Tab::make(__('Inactive'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($model::where('is_active', false)->count())
                ->badgeColor('warning'),

            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($model::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
