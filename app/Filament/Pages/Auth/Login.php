<?php

namespace App\Filament\Pages\Auth;

use Illuminate\Contracts\Support\Htmlable;

class Login extends \Filament\Auth\Pages\Login
{
    protected function getCredentialsFromFormData(array $data): array
    {
        $credentials = parent::getCredentialsFromFormData($data);

        return $credentials;
    }

    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function hasLogo(): bool
    {
        // Hide the default Filament logo since we're using our own
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        // Return empty string to hide the default heading
        return '';
    }
}
