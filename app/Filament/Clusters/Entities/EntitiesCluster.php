<?php

namespace App\Filament\Clusters\Entities;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class EntitiesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Entities');
    }
}
