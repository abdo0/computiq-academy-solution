<?php

namespace App\Filament\Clusters\Templates\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Templates\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Email template created successfully');
    }

    protected function afterCreate(): void
    {
        // If this template is set as default, unset other defaults for the same purpose
        if ($this->record->is_default && $this->record->purpose) {
            \App\Models\EmailTemplate::where('purpose', $this->record->purpose)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
