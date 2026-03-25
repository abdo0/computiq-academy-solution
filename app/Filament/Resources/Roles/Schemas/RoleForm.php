<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        // Fetch all permissions and group them
        $allPermissions = Permission::where('group', '!=', 'Custom')->get();
        $customPermissions = Permission::where('group', 'Custom')->get();

        // Group regular permissions
        $groupedPermissions = $allPermissions->mapToGroups(function ($permission) {
            $nameParts = explode('_', $permission->name);
            if (count($nameParts) > 1) {
                array_shift($nameParts); // Remove the action verb (e.g., 'access', 'create')
                $groupKey = implode('_', $nameParts);
            } else {
                $groupKey = $permission->name; // For single-word permissions like 'dashboard'
            }

            return [$groupKey => $permission];
        })->sortKeys();

        $permissionFieldsets = [];

        foreach ($groupedPermissions as $groupKey => $permissionsInGroup) {
            $groupLabel = Str::of($groupKey)->replace('_', ' ')->title()->toString();

            $options = $permissionsInGroup->mapWithKeys(function (Permission $permission) {
                // Generate a human-readable label for the permission item
                $itemLabel = Str::of($permission->name)->replace('_', ' ')->toString();
                $itemLabel = ucwords($itemLabel);

                return [$permission->name => __($itemLabel)]; // Value is permission name, label is human-readable
            })->all();

            $permissionFieldsets[] = Fieldset::make($groupKey)
                ->label(__($groupLabel))
                ->schema([
                    CheckboxList::make("permissions_{$groupKey}") // Unique name for each group
                        ->options($options)
                        ->hiddenLabel()
                        ->bulkToggleable()
                        ->gridDirection('row')
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull();
        }

        // Custom permissions
        if ($customPermissions->isNotEmpty()) {
            $options = $customPermissions->mapWithKeys(function (Permission $permission) {
                // Generate a human-readable label for the permission item
                $itemLabel = Str::of($permission->name)->replace('_', ' ')->toString();
                $itemLabel = ucwords($itemLabel);

                return [$permission->name => __($itemLabel)]; // Value is permission name, label is human-readable
            })->all();

            $permissionFieldsets[] = Fieldset::make('custom')
                ->label(__('Custom'))
                ->schema([
                    CheckboxList::make('permissions_custom') // Unique name for custom permissions
                        ->options($options)
                        ->hiddenLabel()
                        ->bulkToggleable()
                        ->gridDirection('row')
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull();
        }

        return $schema
            ->components([
                Group::make([
                    Section::make(__('Role Details'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                        ])->compact(),
                ]),

                Group::make([
                    Section::make(__('Permissions'))
                        ->schema($permissionFieldsets)
                        ->collapsible()
                        ->compact(),
                ]),
            ])->columns(1);
    }
}
