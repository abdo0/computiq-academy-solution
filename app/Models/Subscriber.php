<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = ['email', 'status'];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\SubscriberStatus::class,
        ];
    }
}
