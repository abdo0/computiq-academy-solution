<?php

namespace App\Filament\Clusters\BannedEntities\Resources\BannedOrganizations\Pages;

use App\Filament\Clusters\BannedEntities\Resources\BannedOrganizations\BannedOrganizationResource;
use Filament\Resources\Pages\ListRecords;

class ListBannedOrganizations extends ListRecords
{
    protected static string $resource = BannedOrganizationResource::class;

    public function getTitle(): string
    {
        return __('Banned Organizations');
    }
}
