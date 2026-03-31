<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'type' => $this->type?->value,
            'logo' => $this->getFirstMediaUrl('logo') ?: null,
            'logoThumb' => $this->getFirstMediaUrl('logo', 'thumb') ?: null,
            'isActive' => (bool) $this->is_active,
        ];
    }
}
