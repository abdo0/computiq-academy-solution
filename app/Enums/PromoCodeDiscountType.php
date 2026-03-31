<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PromoCodeDiscountType: string implements HasLabel
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FIXED => __('Fixed Amount'),
            self::PERCENTAGE => __('Percentage'),
        };
    }
}
