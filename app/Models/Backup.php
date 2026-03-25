<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Backup extends Model
{
    use ActivityLoggable, HasTranslations;

    protected $fillable = ['path', 'name', 'disk', 'size'];

    public $translatable = ['name'];
}
