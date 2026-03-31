<?php

namespace App\Filament\Resources\PaymentGatewayFees\Pages;

use App\Filament\Resources\PaymentGatewayFees\PaymentGatewayFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentGatewayFee extends EditRecord
{
    protected static string $resource = PaymentGatewayFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
