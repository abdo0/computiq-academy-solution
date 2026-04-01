<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                $schema->translate([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required(),
                    Textarea::make('comment')
                        ->label(__('Comment'))
                        ->required(),
                ]),
                TextInput::make('rating')
                    ->label(__('Rating'))
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }
}
