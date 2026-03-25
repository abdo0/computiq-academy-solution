<?php

namespace App\Filament\Clusters\BannedEntities\Resources\BannedDonors\Pages;

use App\Filament\Clusters\BannedEntities\Resources\BannedDonors\BannedDonorResource;
use Filament\Resources\Pages\ListRecords;

class ListBannedDonors extends ListRecords
{
    protected static string $resource = BannedDonorResource::class;

    public function getTitle(): string
    {
        return __('Banned Donors');
    }
}
