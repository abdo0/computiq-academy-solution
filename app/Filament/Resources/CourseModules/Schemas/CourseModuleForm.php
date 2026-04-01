<?php

namespace App\Filament\Resources\CourseModules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Module Details'))
                ->columns(1)
                ->schema([
                    $schema->translate([
                        TextInput::make('title')
                            ->label(__('Module Title'))
                            ->required()
                            ->maxLength(255),
                    ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
