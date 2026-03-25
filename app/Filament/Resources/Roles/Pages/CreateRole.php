<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

// Illuminate\Database\Eloquent\Model is not strictly needed for the changes below
// but can be kept if already present or for other methods.

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $temporaryPermissions = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Collect permissions from all permission fields
        $allPermissions = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_') && is_array($value)) {
                $allPermissions = array_merge($allPermissions, $value);
                unset($data[$key]); // Remove the permission field from data
            }
        }

        // Ensure values are unique and re-indexed
        $this->temporaryPermissions = array_values(array_unique($allPermissions));

        return $data;
    }

    protected function afterCreate(): void
    {
        // $this->record is the created Role model instance
        // Sync the stored permissions. If $temporaryPermissions is empty, all permissions will be detached.
        $this->record->syncPermissions($this->temporaryPermissions);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
