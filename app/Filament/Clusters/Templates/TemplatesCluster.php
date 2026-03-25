<?php

namespace App\Filament\Clusters\Templates;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class TemplatesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Templates');
    }
}
