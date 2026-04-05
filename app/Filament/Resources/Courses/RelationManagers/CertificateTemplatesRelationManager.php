<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificateTemplates';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedAcademicCap;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Certificate Template');
    }

    public static function getModelLabel(): string
    {
        return __('Certificate Template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Certificate Templates');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Template Image'))
                ->compact()
                ->columns(1)
                ->schema([
                    SpatieMediaLibraryFileUpload::make('template_image')
                        ->collection('template_image')
                        ->label(__('Certificate Image'))
                        ->image()
                        ->imageEditor()
                        ->required()
                        ->columnSpanFull(),
                ]),
            Section::make(__('Name Rendering'))
                ->compact()
                ->columns(2)
                ->schema([
                    TextInput::make('text_color')
                        ->label(__('Text Color'))
                        ->default('#111827')
                        ->required(),
                    TextInput::make('font_size')
                        ->label(__('Font Size'))
                        ->numeric()
                        ->minValue(12)
                        ->maxValue(120)
                        ->default(42)
                        ->required(),
                    TextInput::make('font_family')
                        ->label(__('Font Family'))
                        ->default('DejaVu Sans')
                        ->required(),
                    Select::make('text_align')
                        ->label(__('Text Alignment'))
                        ->options([
                            'left' => __('Left'),
                            'center' => __('Center'),
                            'right' => __('Right'),
                        ])
                        ->default('center')
                        ->required(),
                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),
            Section::make(__('Name Area Coordinates'))
                ->compact()
                ->columns(2)
                ->schema([
                    TextInput::make('x1')
                        ->label(__('X1'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step('0.001')
                        ->default(0.22)
                        ->required()
                        ->extraAttributes(['data-cert-coordinate' => 'x1']),
                    TextInput::make('y1')
                        ->label(__('Y1'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step('0.001')
                        ->default(0.44)
                        ->required()
                        ->extraAttributes(['data-cert-coordinate' => 'y1']),
                    TextInput::make('x2')
                        ->label(__('X2'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step('0.001')
                        ->default(0.78)
                        ->required()
                        ->extraAttributes(['data-cert-coordinate' => 'x2']),
                    TextInput::make('y2')
                        ->label(__('Y2'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step('0.001')
                        ->default(0.58)
                        ->required()
                        ->extraAttributes(['data-cert-coordinate' => 'y2']),
                    ViewField::make('coordinate_picker')
                        ->label(__('Name Area Picker'))
                        ->view('filament.resources.courses.certificate-name-area-picker')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_id')
            ->columns([
                SpatieMediaLibraryImageColumn::make('template_image')
                    ->collection('template_image')
                    ->label(__('Preview'))
                    ->square(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('name_area')
                    ->label(__('Name Area'))
                    ->state(fn ($record) => sprintf('(%0.2f, %0.2f) → (%0.2f, %0.2f)', $record->x1, $record->y1, $record->x2, $record->y2))
                    ->wrap(),
                TextColumn::make('text_align')
                    ->label(__('Alignment'))
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Certificate Template'))
                    ->icon('heroicon-o-plus')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->visible(fn () => $this->getOwnerRecord()->certificateTemplate === null),
            ])
            ->recordActions([
                Action::make('configureNameArea')
                    ->label(__('Mark Name Area'))
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->fillForm(fn ($record) => [
                        'x1' => $record->x1,
                        'y1' => $record->y1,
                        'x2' => $record->x2,
                        'y2' => $record->y2,
                    ])
                    ->form([
                        TextInput::make('x1')
                            ->label(__('X1'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.001')
                            ->required()
                            ->extraAttributes(['data-cert-coordinate' => 'x1']),
                        TextInput::make('y1')
                            ->label(__('Y1'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.001')
                            ->required()
                            ->extraAttributes(['data-cert-coordinate' => 'y1']),
                        TextInput::make('x2')
                            ->label(__('X2'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.001')
                            ->required()
                            ->extraAttributes(['data-cert-coordinate' => 'x2']),
                        TextInput::make('y2')
                            ->label(__('Y2'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.001')
                            ->required()
                            ->extraAttributes(['data-cert-coordinate' => 'y2']),
                        ViewField::make('coordinate_picker')
                            ->label(__('Name Area Picker'))
                            ->view('filament.resources.courses.certificate-name-area-picker')
                            ->columnSpanFull(),
                    ])
                    ->action(fn (array $data, $record) => $record->update($data))
                    ->modalWidth(Width::SevenExtraLarge),
                EditAction::make()
                    ->label(__('Edit Template'))
                    ->modalWidth(Width::SevenExtraLarge),
                DeleteAction::make()
                    ->label(__('Delete Template')),
            ]);
    }
}
