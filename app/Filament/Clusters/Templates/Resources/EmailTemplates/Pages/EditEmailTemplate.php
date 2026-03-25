<?php

namespace App\Filament\Clusters\Templates\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Templates\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Email template updated successfully');
    }

    protected function afterSave(): void
    {
        // If this template is set as default, unset other defaults for the same purpose
        if ($this->record->is_default && $this->record->purpose) {
            \App\Models\EmailTemplate::where('purpose', $this->record->purpose)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
