<?php

namespace App\Filament\Resources\CourseExams\RelationManagers;

use App\Models\CourseExamQuestion;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedQuestionMarkCircle;

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Questions');
    }

    public static function getModelLabel(): string
    {
        return __('Question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Questions');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Question Details'))
                ->schema([
                    $schema->translate([
                        TextInput::make('question')
                            ->label(__('Question'))
                            ->required()
                            ->maxLength(500),
                    ]),
                ]),
            Section::make(__('Answer Options'))
                ->description(__('Add at least two options and mark one correct answer.'))
                ->schema([
                    Repeater::make('options')
                        ->relationship('options')
                        ->label(__('Options'))
                        ->minItems(2)
                        ->defaultItems(4)
                        ->orderColumn('sort_order')
                        ->reorderableWithButtons()
                        ->schema([
                            $schema->translate([
                                TextInput::make('option_text')
                                    ->label(__('Option Text'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            Toggle::make('is_correct')
                                ->label(__('Correct Answer'))
                                ->default(false),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('question')
                    ->label(__('Question'))
                    ->icon(Heroicon::QuestionMarkCircle)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('question', app()->getLocale()) ?: $record->getTranslation('question', 'en'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('options_count')
                    ->label(__('Options'))
                    ->icon(Heroicon::ListBullet)
                    ->counts('options')
                    ->badge(),
                TextColumn::make('options')
                    ->label(__('Correct Answer'))
                    ->icon(Heroicon::CheckCircle)
                    ->state(function (CourseExamQuestion $record): string {
                        $correctOption = $record->options->firstWhere('is_correct', true);

                        return $correctOption?->getTranslation('option_text', app()->getLocale())
                            ?: $correctOption?->getTranslation('option_text', 'en')
                            ?: __('Not set');
                    })
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Question'))
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->modalWidth(Width::FiveExtraLarge)
                    ->after(fn (CourseExamQuestion $record) => $record->normalizeCorrectOptions()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit Question'))
                        ->slideOver()
                        ->modalWidth(Width::FiveExtraLarge)
                        ->after(fn (CourseExamQuestion $record) => $record->normalizeCorrectOptions()),
                    DeleteAction::make()
                        ->label(__('Delete Question')),
                ])
                    ->label(__('Question Actions'))
                    ->tooltip(__('Question Actions'))
                    ->icon(Heroicon::EllipsisVertical)
                    ->iconButton(),
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
