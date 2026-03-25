<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run if the application is installed and the settings table exists
        try {
            if (settings('mail_mailer')) {
                $this->configureMailSettings();
            }
        } catch (Exception $e) {
            // If settings function doesn't exist or database is not connected, do nothing
        }
    }

    /**
     * Configure mail settings from the database
     */
    protected function configureMailSettings(): void
    {
        // Set the default mailer
        Config::set('mail.default', settings('mail_mailer', Config::get('mail.default')));

        // Configure the SMTP settings
        Config::set('mail.mailers.smtp.host', settings('mail_host', Config::get('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', settings('mail_port', Config::get('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', settings('mail_username', Config::get('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', settings('mail_password', Config::get('mail.mailers.smtp.password')));
        Config::set('mail.mailers.smtp.encryption', settings('mail_encryption', Config::get('mail.mailers.smtp.encryption')));

        // Set the global "from" address and name
        Config::set('mail.from.address', settings('mail_from_address', Config::get('mail.from.address')));
        Config::set('mail.from.name', settings('mail_from_name', Config::get('mail.from.name')));
    }
}
