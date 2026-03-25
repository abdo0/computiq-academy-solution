<?php

namespace App\Services;

use App\Enums\EmailTemplatePurpose;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    /**
     * Send email using email template
     */
    public static function sendTemplate(
        EmailTemplatePurpose $purpose,
        string $to,
        array $variables = [],
        ?string $locale = null
    ): bool {
        try {
            // Get the default template for this purpose
            $template = EmailTemplate::defaultForPurpose($purpose)->first();

            if (! $template) {
                Log::warning('No email template found for purpose', [
                    'purpose' => $purpose->value,
                    'to' => $to,
                ]);

                return false;
            }

            // Get user's locale or default to app locale
            $locale = $locale ?? app()->getLocale();

            // Get template subject and body in the user's locale
            $subject = $template->getTranslation('subject', $locale, false);
            $body = $template->getTranslation('body', $locale, false);

            // If translation not found, try to get from available locales
            if (! $subject || ! $body) {
                $availableLocales = ['en', 'ar', 'ku'];
                foreach ($availableLocales as $availableLocale) {
                    if (! $subject) {
                        $subject = $template->getTranslation('subject', $availableLocale, false);
                    }
                    if (! $body) {
                        $body = $template->getTranslation('body', $availableLocale, false);
                    }
                    if ($subject && $body) {
                        break;
                    }
                }
            }

            if (! $subject || ! $body) {
                Log::error('No content found for email template', [
                    'template_id' => $template->id,
                    'purpose' => $purpose->value,
                ]);

                return false;
            }

            // Replace variables in subject and body
            $subject = self::replaceVariables($subject, $variables);
            $body = self::replaceVariables($body, $variables);

            // Set locale context for email translation functions
            $originalLocale = app()->getLocale();
            app()->setLocale($locale);

            // Set email direction based on locale
            $direction = in_array($locale, ['ar', 'ku']) ? 'rtl' : 'ltr';
            if (function_exists('context')) {
                context()->add('email_language', $locale);
                context()->add('email_direction', $direction);
            }

            // Render email with layout (header and footer)
            $html = view('emails.template', [
                'title' => $subject,
                'subtitle' => $template->getTranslation('name', $locale, false) ?: $subject,
                'body' => $body,
            ])->render();

            // Restore original locale
            app()->setLocale($originalLocale);

            // Send email
            Mail::html($html, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });

            Log::info('Email template sent', [
                'purpose' => $purpose->value,
                'to' => $to,
                'template_code' => $template->code,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email template', [
                'purpose' => $purpose->value,
                'to' => $to,
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
}
