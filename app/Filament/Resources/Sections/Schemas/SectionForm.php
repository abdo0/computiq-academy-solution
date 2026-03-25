<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('key')
                    ->required()
                    ->disabled()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
