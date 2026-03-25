<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

// Illuminate\Database\Eloquent\Model is not strictly needed for the changes below
// but can be kept if already present or for other methods.

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $temporaryPermissions = [];

    public function mount(int|string $record): void
    {
        // Call parent to load the record and fill the form initially.
        parent::mount($record);

        // Get the current state of the form
        $existingFormData = $this->form->getState();

        // Prepare the permission names to be loaded into the form
        $permissionNames = [];
        if ($this->record && method_exists($this->record, 'permissions')) {
            $permissionNames = $this->record->permissions->pluck('name')->toArray();
        }

        // Use the same grouping logic as the form
        $groupedPermissions = [];
        $customPermissions = [];

        foreach ($permissionNames as $permissionName) {
            // Check if this is a custom permission by looking up the permission in the database
            $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();

            if ($permission && $permission->group === 'Custom') {
                $customPermissions[] = $permissionName;
            } else {
                // Group regular permissions using the same logic as the form
                $nameParts = explode('_', $permissionName);
                if (count($nameParts) > 1) {
                    array_shift($nameParts); // Remove the action verb (e.g., 'access', 'create')
                    $groupKey = implode('_', $nameParts);
                } else {
                    $groupKey = $permissionName; // For single-word permissions like 'dashboard'
                }

                if (! isset($groupedPermissions[$groupKey])) {
                    $groupedPermissions[$groupKey] = [];
                }
                $groupedPermissions[$groupKey][] = $permissionName;
            }
        }

        // Add permissions to form data
        $formDataToSet = $existingFormData;

        // Add regular permissions
        foreach ($groupedPermissions as $groupKey => $permissions) {
            $fieldName = "permissions_{$groupKey}";
            $formDataToSet[$fieldName] = $permissions;
        }

        // Add custom permissions
        if (! empty($customPermissions)) {
            $formDataToSet['permissions_custom'] = $customPermissions;
        }

        // Fill the form with the combined data
        $this->form->fill($formDataToSet);
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        // $this->record is the updated Role model instance
        $this->record->syncPermissions($this->temporaryPermissions);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label(__('Delete'))
                ->before(function () {
                    if ($this->record->name === 'admin') {
                        return false;
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
