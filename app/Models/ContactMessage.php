<?php

namespace App\Models;

use App\Enums\ContactMessageSubject;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'subject' => ContactMessageSubject::class,
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }
}

