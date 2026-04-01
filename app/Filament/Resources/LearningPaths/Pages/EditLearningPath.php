<?php

namespace App\Filament\Resources\LearningPaths\Pages;

use App\Filament\Resources\LearningPaths\LearningPathResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningPath extends EditRecord
{
    protected static string $resource = LearningPathResource::class;

    public function getTitle(): string
    {
        return __('Edit Learning Path');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('Delete Learning Path')),
            ForceDeleteAction::make()
                ->label(__('Force Delete Learning Path')),
            RestoreAction::make()
                ->label(__('Restore Learning Path')),
        ];
    }
}
