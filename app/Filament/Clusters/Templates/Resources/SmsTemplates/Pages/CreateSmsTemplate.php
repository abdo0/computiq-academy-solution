<?php

namespace App\Filament\Clusters\Templates\Resources\SmsTemplates\Pages;

use App\Filament\Clusters\Templates\Resources\SmsTemplates\SmsTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsTemplate extends CreateRecord
{
    protected static string $resource = SmsTemplateResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('SMS template created successfully');
    }

    protected function afterCreate(): void
    {
        // If this template is set as default, unset other defaults for the same purpose
        if ($this->record->is_default && $this->record->purpose) {
            \App\Models\SmsTemplate::where('purpose', $this->record->purpose)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
