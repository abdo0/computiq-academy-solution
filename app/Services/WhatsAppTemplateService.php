<?php

namespace App\Services;

use App\Enums\SmsTemplatePurpose;
use App\Jobs\SendWhatsAppMessage;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Log;

class WhatsAppTemplateService
{
    /**
     * Send WhatsApp message using SMS template
     */
    public static function sendTemplate(
        SmsTemplatePurpose $purpose,
        string $phone,
        array $variables = [],
        ?int $priority = 10,
        ?string $referenceId = null,
        ?string $locale = null
    ): bool {
        try {
            // Get the default template for this purpose
            $template = SmsTemplate::defaultForPurpose($purpose)->first();

            if (! $template) {
                Log::warning('No SMS template found for purpose', [
                    'purpose' => $purpose->value,
                    'phone' => $phone,
                ]);

                return false;
            }

            // Get user's locale or default to app locale
            $locale = $locale ?? app()->getLocale();

            // Get template content in the user's locale
            $content = $template->getTranslation('content', $locale, false);

            // If translation not found, try to get from available locales
            if (! $content) {
                $availableLocales = ['en', 'ar', 'ku'];
                foreach ($availableLocales as $availableLocale) {
                    $content = $template->getTranslation('content', $availableLocale, false);
                    if ($content) {
                        break;
                    }
                }
            }

            if (! $content) {
                Log::error('No content found for SMS template', [
                    'template_id' => $template->id,
                    'purpose' => $purpose->value,
                ]);

                return false;
            }

            // Replace variables in content
            $message = self::replaceVariables($content, $variables);

            // Add platform header to message
            $message = self::addPlatformHeader($message, $locale);

            // Send via WhatsApp queue
            SendWhatsAppMessage::dispatch(
                phone: $phone,
                message: $message,
                priority: $priority,
                referenceId: $referenceId
            );

            Log::info('WhatsApp template message queued', [
                'purpose' => $purpose->value,
                'phone' => $phone,
                'template_code' => $template->code,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp template message', [
                'purpose' => $purpose->value,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Replace variables in template content
     */
    protected static function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }

    /**
     * Add platform header to message
     */
    protected static function addPlatformHeader(string $message, string $locale): string
    {
        // Get platform name from settings
        $platformName = self::getPlatformName($locale);

        // Create header
        $header = "━━━━━━━━━━━━━━━━━━━━\n";
        $header .= "📱 {$platformName}\n";
        $header .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        return $header.$message;
    }

    /**
     * Get platform name in the specified locale
     */
    protected static function getPlatformName(string $locale): string
    {
        // Try to get company name from settings
        $companyName = settings('company_name');

        // Handle multilingual company name
        if (is_string($companyName) && isJsonString($companyName)) {
            $companyName = json_decode($companyName, true);
        }

        if (is_array($companyName)) {
            return $companyName[$locale] ?? $companyName['ar'] ?? $companyName['en'] ?? 'نخوة';
        }

        if (is_string($companyName) && ! empty($companyName)) {
            return $companyName;
        }

        // Fallback to default names based on locale
        return match ($locale) {
            'ar' => 'نخوة',
            'ku' => 'نخوة',
            'en' => 'Nakhwaa',
            default => 'نخوة',
        };
    }

    /**
     * Check if string is valid JSON
     */
    protected static function isJsonString(string $string): bool
    {
        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Send OTP verification code via WhatsApp
     */
    public static function sendOTP(string $phone, string $otpCode, int $expiryMinutes = 10, ?string $locale = null): bool
    {
        return self::sendTemplate(
            purpose: SmsTemplatePurpose::OTP_VERIFICATION,
            phone: $phone,
            variables: [
                'otp_code' => $otpCode,
                'expiry_minutes' => (string) $expiryMinutes,
            ],
            priority: 20, // Higher priority for OTP
            referenceId: null,
            locale: $locale
        );
    }

    /**
     * Send welcome message via WhatsApp
     */
    public static function sendWelcome(string $phone, string $userName, ?string $locale = null): bool
    {
        return self::sendTemplate(
            purpose: SmsTemplatePurpose::WELCOME,
            phone: $phone,
            variables: [
                'user_name' => $userName,
            ],
            priority: 10,
            referenceId: null,
            locale: $locale
        );
    }
}
