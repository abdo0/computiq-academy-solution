<?php

namespace App\Filament\Clusters\BannedEntities\Resources\BannedDonors;

use App\Enums\DonorStatus;
use App\Filament\Clusters\BannedEntities\BannedEntitiesCluster;
use App\Filament\Clusters\BannedEntities\Resources\BannedDonors\Pages\ListBannedDonors;
use App\Filament\Resources\Donors\Schemas\DonorForm;
use App\Filament\Resources\Donors\Tables\DonorsTable;
use App\Models\Donor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannedDonorResource extends Resource
{
    protected static ?string $model = Donor::class;

    protected static ?string $cluster = BannedEntitiesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserMinus;

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Banned Donors');
    }

    public static function getModelLabel(): string
    {
        return __('Banned Donor');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Banned Donors');
    }

    public static function form(Schema $schema): Schema
    {
        return DonorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonorsTable::configure($table);
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
            'index' => ListBannedDonors::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', DonorStatus::BANNED)
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
