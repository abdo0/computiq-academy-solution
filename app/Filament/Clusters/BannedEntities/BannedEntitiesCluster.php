<?php

namespace App\Filament\Clusters\BannedEntities;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class BannedEntitiesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldExclamation;

    public static function getNavigationGroup(): ?string
    {
        return __('Trust & Safety');
    }

    public static function getNavigationLabel(): string
    {
        return __('Banned Donors/Orgs');
    }
}
