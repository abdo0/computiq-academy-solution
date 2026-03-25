<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;

class CustomAccountWidget extends BaseAccountWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';
}
