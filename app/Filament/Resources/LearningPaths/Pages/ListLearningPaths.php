<?php

namespace App\Filament\Resources\LearningPaths\Pages;

use App\Filament\Resources\LearningPaths\LearningPathResource;
use App\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningPaths extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = LearningPathResource::class;

    public function getTitle(): string
    {
        return __('Learning Paths');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Create Learning Path')),
        ];
    }
}
