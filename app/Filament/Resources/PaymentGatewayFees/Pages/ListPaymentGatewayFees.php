<?php

namespace App\Filament\Resources\PaymentGatewayFees\Pages;

use App\Filament\Resources\PaymentGatewayFees\PaymentGatewayFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentGatewayFees extends ListRecords
{
    protected static string $resource = PaymentGatewayFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
