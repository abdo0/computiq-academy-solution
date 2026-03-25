<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Article Information'))
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('Title'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText(__('URL-friendly identifier'))
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('excerpt')
                                            ->label(__('Excerpt'))
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText(__('Short summary of the article')),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('content')
                                            ->label(__('Content'))
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Select::make('article_category_id')
                                    ->label(__('Category'))
                                    ->relationship('category', 'name', fn ($query) => $query->active()->orderBy('sort_order'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Select::make('author_id')
                                    ->label(__('Author'))
                                    ->relationship('author', 'name', fn ($query) => $query->orderBy('name'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->default(fn () => auth()->id()),
                            ])
                            ->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Publishing'))
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->label(__('Featured Image'))
                                    ->image()
                                    ->directory('articles')
                                    ->maxSize(5120)
                                    ->columnSpanFull(),

                                Toggle::make('is_published')
                                    ->label(__('Published'))
                                    ->default(false)
                                    ->helperText(__('Make this article visible to the public')),

                                DatePicker::make('published_at')
                                    ->label(__('Publish Date'))
                                    ->default(now())
                                    ->visible(fn ($get) => $get('is_published')),
                            ])
                            ->columns(1),

                        Section::make(__('SEO'))
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label(__('Meta Title'))
                                    ->maxLength(255)
                                    ->helperText(__('SEO meta title')),

                                \Filament\Forms\Components\Textarea::make('meta_description')
                                    ->label(__('Meta Description'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText(__('SEO meta description')),
                            ])
                            ->columns(1)
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ]);
    }
}
