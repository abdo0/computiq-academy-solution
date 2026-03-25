<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Message Information'))
                            ->schema([
                                Placeholder::make('name')
                                    ->label(__('Name'))
                                    ->content(fn ($record) => $record->name ?? '-'),

                                Placeholder::make('email')
                                    ->label(__('Email'))
                                    ->content(fn ($record) => $record->email ?? '-')
                                    ->copyable(),

                                Placeholder::make('subject')
                                    ->label(__('Subject'))
                                    ->content(fn ($record) => $record->subject?->getLabel() ?? __('No subject'))
                                    ->color(fn ($record) => $record->subject?->getColor() ?? 'gray')
                                    ->visible(fn ($record) => $record->subject !== null),

                                Placeholder::make('message')
                                    ->label(__('Message'))
                                    ->content(fn ($record) => $record->message ?? __('No message'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make(__('Read Status'))
                            ->schema([
                                Placeholder::make('is_read')
                                    ->label(__('Status'))
                                    ->content(fn ($record) => $record->is_read ? __('Read') : __('Unread'))
                                    ->color(fn ($record) => $record->is_read ? 'success' : 'gray'),

                                Placeholder::make('read_at')
                                    ->label(__('Read At'))
                                    ->content(fn ($record) => $record->read_at?->format('Y-m-d H:i:s') ?? __('Not read yet'))
                                    ->visible(fn ($record) => $record->is_read),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Timestamps'))
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('Created At'))
                                    ->content(fn ($record) => $record->created_at?->format('Y-m-d H:i:s') ?? '-'),

                                Placeholder::make('updated_at')
                                    ->label(__('Updated At'))
                                    ->content(fn ($record) => $record->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}

