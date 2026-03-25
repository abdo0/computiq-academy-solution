<?php

namespace App\Filament\Imports;

use App\Models\EmailTemplate;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmailTemplateImporter extends Importer
{
    protected static ?string $model = EmailTemplate::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label(__('Code'))
                ->required()
                ->rules(['required', 'string', 'max:50', 'unique:email_templates,code']),

            ImportColumn::make('name')
                ->label(__('Name (JSON)'))
                ->helperText(__('JSON format: {"en":"English Name","ar":"Arabic Name","ku":"Kurdish Name"}'))
                ->rules(['required', 'string']),

            ImportColumn::make('purpose')
                ->label(__('Purpose'))
                ->rules(['required', 'string'])
                ->example('welcome'),

            ImportColumn::make('subject')
                ->label(__('Subject (JSON)'))
                ->helperText(__('JSON format: {"en":"Subject","ar":"الموضوع","ku":"بابەت"}'))
                ->rules(['required', 'string']),

            ImportColumn::make('body')
                ->label(__('Body (JSON)'))
                ->helperText(__('JSON format: {"en":"Body","ar":"المحتوى","ku":"ناوەڕۆک"}'))
                ->rules(['required', 'string']),

            ImportColumn::make('is_default')
                ->label(__('Is Default'))
                ->boolean()
                ->rules(['boolean']),

            ImportColumn::make('is_active')
                ->label(__('Is Active'))
                ->boolean()
                ->rules(['boolean']),

            ImportColumn::make('sort_order')
                ->label(__('Sort Order'))
                ->numeric()
                ->rules(['integer', 'min:0']),
        ];
    }

    public function resolveRecord(): ?EmailTemplate
    {
        return EmailTemplate::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('Your email template import has completed and :count :rows imported.', [
            'count' => Number::format($import->successful_rows),
            'rows' => str('row')->plural($import->successful_rows),
        ]);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' '.__('failed to import.');
        }

        return $body;
    }
}
