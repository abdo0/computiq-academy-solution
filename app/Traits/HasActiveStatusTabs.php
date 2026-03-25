<?php

namespace App\Traits;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatusTabs
{
    /**
     * Automatically configure tabs and default tab for active status resources
     */
    public function bootHasActiveStatusTabs(): void
    {
        // This method will be called automatically when the trait is used
    }

    /**
     * Get tabs - automatically returns active status tabs
     */
    public function getTabs(): array
    {
        $model = static::getModel();

        return [
            __('All') => Tab::make()
                ->icon('heroicon-o-squares-2x2')
                ->badge($model::count())
                ->badgeColor('primary'),

            __('Active') => Tab::make()
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($model::where('is_active', true)->count())
                ->badgeColor('success'),

            __('Inactive') => Tab::make()
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($model::where('is_active', false)->count())
                ->badgeColor('warning'),

            __('Trashed') => Tab::make()
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($model::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
}
