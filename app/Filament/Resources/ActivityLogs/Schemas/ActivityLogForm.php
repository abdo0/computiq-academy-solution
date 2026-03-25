<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewField::make('activity_log_details')
                    ->view('filament.resources.activity-log-resource.activity-log-details')
                    ->columnSpanFull(),
            ]);
    }
}
