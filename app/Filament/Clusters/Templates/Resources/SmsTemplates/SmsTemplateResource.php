<?php

namespace App\Filament\Clusters\Templates\Resources\SmsTemplates;

use App\Filament\Clusters\Templates\Resources\SmsTemplates\Pages\CreateSmsTemplate;
use App\Filament\Clusters\Templates\Resources\SmsTemplates\Pages\EditSmsTemplate;
use App\Filament\Clusters\Templates\Resources\SmsTemplates\Pages\ListSmsTemplates;
use App\Filament\Clusters\Templates\TemplatesCluster;
use App\Filament\Resources\SmsTemplates\Schemas\SmsTemplateForm;
use App\Filament\Resources\SmsTemplates\Tables\SmsTemplatesTable;
use App\Models\SmsTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;

    protected static ?string $cluster = TemplatesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('SMS Templates');
    }

    public static function getModelLabel(): string
    {
        return __('SMS Template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('SMS Templates');
    }

    public static function form(Schema $schema): Schema
    {
        return SmsTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsTemplatesTable::configure($table);
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
            'index' => ListSmsTemplates::route('/'),
            'create' => CreateSmsTemplate::route('/create'),
            'edit' => EditSmsTemplate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
