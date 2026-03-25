<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactMessageSubject;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->icon(Heroicon::User)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->icon(Heroicon::Envelope)
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('message')
                    ->label(__('Message'))
                    ->icon(Heroicon::ChatBubbleLeftRight)
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('is_read')
                    ->label(__('Status'))
                    ->icon(fn ($record) => $record->is_read ? Heroicon::CheckCircle : Heroicon::Envelope)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Read') : __('Unread'))
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('read_at')
                    ->label(__('Read At'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Calendar)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->label(__('Subject'))
                    ->options(ContactMessageSubject::class)
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_read')
                    ->label(__('Read Status'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Read'))
                    ->falseLabel(__('Unread')),
            ])
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->recordActions([
                Action::make('view')
                    ->label(__('View'))
                    ->icon(Heroicon::Eye)
                    ->iconButton()
                    ->tooltip(__('View'))
                    ->url(fn ($record) => ContactMessageResource::getUrl('view', ['record' => $record])),

                Action::make('toggle_read')
                    ->label(fn ($record) => $record->is_read ? __('Mark as Unread') : __('Mark as Read'))
                    ->icon(fn ($record) => $record->is_read ? Heroicon::Envelope : Heroicon::CheckCircle)
                    ->iconButton()
                    ->tooltip(fn ($record) => $record->is_read ? __('Mark as Unread') : __('Mark as Read'))
                    ->color(fn ($record) => $record->is_read ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $newReadStatus = ! $record->is_read;
                        $record->update([
                            'is_read' => $newReadStatus,
                            'read_at' => $newReadStatus ? now() : null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title($newReadStatus ? __('Message marked as read') : __('Message marked as unread'))
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip(__('Delete')),
            ]);
    }
}

