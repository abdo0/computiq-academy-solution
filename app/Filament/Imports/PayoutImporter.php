<?php

namespace App\Filament\Imports;

use App\Enums\PayoutStatus;
use App\Models\Currency;
use App\Models\Organization;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PayoutImporter extends Importer
{
    protected static ?string $model = Payout::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('payout_ref')
                ->label(__('Payout Reference'))
                ->rules(['required', 'string', 'max:255', 'unique:payouts,payout_ref']),

            ImportColumn::make('organization_id')
                ->label(__('Organization ID'))
                ->rules(['required', 'exists:organizations,id'])
                ->relationship(resolveUsing: fn ($value) => Organization::find($value)),

            ImportColumn::make('amount')
                ->label(__('Amount'))
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('currency')
                ->label(__('Currency'))
                ->rules(['nullable', 'string', 'max:3'])
                ->default(fn () => Currency::getDefaultCode()),

            ImportColumn::make('status')
                ->label(__('Status'))
                ->rules(['required', 'in:'.implode(',', array_column(PayoutStatus::cases(), 'value'))])
                ->castStateUsing(fn ($state) => PayoutStatus::tryFrom($state) ?? PayoutStatus::PENDING),

            ImportColumn::make('payment_method')
                ->label(__('Payment Method'))
                ->rules(['required', 'in:bank_transfer,check,cash,mobile_wallet']),

            ImportColumn::make('bank_name')
                ->label(__('Bank Name'))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('account_number')
                ->label(__('Account Number'))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('account_holder_name')
                ->label(__('Account Holder Name'))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('iban')
                ->label(__('IBAN'))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('swift_code')
                ->label(__('SWIFT Code'))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('transaction_id')
                ->label(__('Transaction ID'))
                ->rules(['nullable', 'exists:transactions,id'])
                ->relationship(resolveUsing: fn ($value) => $value ? Transaction::find($value) : null),

            ImportColumn::make('processed_by')
                ->label(__('Processed By User ID'))
                ->rules(['nullable', 'exists:users,id'])
                ->relationship(resolveUsing: fn ($value) => $value ? User::find($value) : null),

            ImportColumn::make('notes')
                ->label(__('Notes'))
                ->rules(['nullable', 'string', 'max:1000']),

            ImportColumn::make('failure_reason')
                ->label(__('Failure Reason'))
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): Payout
    {
        return new Payout;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your payout import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
