<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageInfolist;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function form(Schema $schema): Schema
    {
        return ContactMessageInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_read')
                ->label(fn () => $this->record->is_read ? __('Mark as Unread') : __('Mark as Read'))
                ->icon(fn () => $this->record->is_read ? Heroicon::Envelope : Heroicon::CheckCircle)
                ->color(fn () => $this->record->is_read ? 'gray' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $newReadStatus = ! $this->record->is_read;
                    $this->record->update([
                        'is_read' => $newReadStatus,
                        'read_at' => $newReadStatus ? now() : null,
                    ]);

                    Notification::make()
                        ->title($newReadStatus ? __('Message marked as read') : __('Message marked as unread'))
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Auto-mark as read when viewing
        if (! $this->record->is_read) {
            $this->record->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}

