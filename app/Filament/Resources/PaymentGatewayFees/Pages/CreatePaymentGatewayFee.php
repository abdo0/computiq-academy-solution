<?php

namespace App\Filament\Resources\PaymentGatewayFees\Pages;

use App\Filament\Resources\PaymentGatewayFees\PaymentGatewayFeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentGatewayFee extends CreateRecord
{
    protected static string $resource = PaymentGatewayFeeResource::class;
}
