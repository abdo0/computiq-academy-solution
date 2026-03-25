<?php

namespace App\Filament\Clusters\BannedEntities\Resources\BannedOrganizations;

use App\Enums\OrganizationStatus;
use App\Filament\Clusters\BannedEntities\BannedEntitiesCluster;
use App\Filament\Clusters\BannedEntities\Resources\BannedOrganizations\Pages\ListBannedOrganizations;
use App\Filament\Resources\Organizations\Schemas\OrganizationForm;
use App\Filament\Resources\Organizations\Tables\OrganizationsTable;
use App\Models\Organization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannedOrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $cluster = BannedEntitiesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldExclamation;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('Banned Organizations');
    }

    public static function getModelLabel(): string
    {
        return __('Banned Organization');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Banned Organizations');
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBannedOrganizations::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', OrganizationStatus::BANNED)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
