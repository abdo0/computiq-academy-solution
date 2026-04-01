<?php

namespace App\Filament\Resources\CourseModules\RelationManagers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedPlayCircle;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Lessons');
    }

    public static function getModelLabel(): string
    {
        return __('Lesson');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Lessons');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Lesson Details'))
                ->columns(2)
                ->schema([
                    $schema->translate([
                        TextInput::make('title')
                            ->label(__('Lesson Title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(4),
                    ]),
                    TextInput::make('duration_minutes')
                        ->label(__('Duration (Minutes)'))
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Select::make('content_type')
                        ->label(__('Content Type'))
                        ->options([
                            'video' => __('Video'),
                        ])
                        ->default('video')
                        ->required(),
                    Select::make('video_source_type')
                        ->label(__('Video Source'))
                        ->options([
                            'upload' => __('Uploaded Video'),
                            'embed' => __('External Embed'),
                        ])
                        ->default('upload')
                        ->required()
                        ->live(),
                    Toggle::make('is_free')
                        ->label(__('Free Lesson'))
                        ->default(false),
                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true),
                ]),
            Section::make(__('Lesson Media'))
                ->columns(2)
                ->schema([
                    TextInput::make('video_url')
                        ->label(__('External Video URL'))
                        ->url()
                        ->visible(fn (Get $get) => $get('video_source_type') === 'embed')
                        ->required(fn (Get $get) => $get('video_source_type') === 'embed')
                        ->helperText(__('Supported: YouTube, Vimeo, Loom, Wistia'))
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('lesson_video')
                        ->collection('video')
                        ->label(__('Uploaded Video'))
                        ->acceptedFileTypes([
                            'video/mp4',
                            'video/quicktime',
                            'video/webm',
                            'video/x-msvideo',
                            'video/x-matroska',
                        ])
                        ->maxSize(512000)
                        ->visible(fn (Get $get) => $get('video_source_type') === 'upload')
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('lesson_documents')
                        ->collection('documents')
                        ->label(__('Lesson Documents'))
                        ->multiple()
                        ->downloadable()
                        ->openable()
                        ->maxFiles(12)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Lesson'))
                    ->icon(Heroicon::PlayCircle)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()) ?: $record->getTranslation('title', 'en')),
                TextColumn::make('video_source_type')
                    ->label(__('Video Source'))
                    ->icon(Heroicon::VideoCamera)
                    ->formatStateUsing(fn (string $state): string => $state === 'embed' ? __('External Embed') : __('Uploaded Video'))
                    ->badge(),
                TextColumn::make('duration_minutes')
                    ->label(__('Minutes'))
                    ->icon(Heroicon::Clock)
                    ->numeric(),
                TextColumn::make('documents_count')
                    ->label(__('Docs'))
                    ->icon(Heroicon::DocumentText)
                    ->state(fn ($record) => $record->getMedia('documents')->count()),
                IconColumn::make('is_free')
                    ->label(__('Free'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Lesson'))
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->modalWidth(Width::FiveExtraLarge),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit Lesson'))
                    ->slideOver()
                    ->modalWidth(Width::FiveExtraLarge),
                DeleteAction::make()
                    ->label(__('Delete Lesson')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
