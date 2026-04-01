<?php

namespace App\Traits;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

trait HasSoftDeleteTabs
{
    public function getTabs(): array
    {
        $model = static::getModel();

        return [
            'all' => Tab::make(__('All'))
                ->icon('heroicon-o-squares-2x2')
                ->badge($model::count())
                ->badgeColor('primary'),

            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($model::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
