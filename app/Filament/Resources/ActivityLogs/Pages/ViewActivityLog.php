<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewField::make('activity_log_details')
                    ->view('filament.resources.activity-log-resource.activity-log-details')
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // No edit or delete actions needed
        ];
    }
}
