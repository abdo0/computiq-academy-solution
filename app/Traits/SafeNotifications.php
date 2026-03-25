<?php

namespace App\Traits;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait SafeNotifications
{
    /**
     * Send a safe notification that avoids translation system issues
     */
    public function sendSafeNotification(string $title, string $body, string $type = 'success', int $duration = 5000): void
    {
        try {
            Notification::make()
                ->title($title)
                ->body($body)
                ->{$type}()
                ->duration($duration)
                ->send();
        } catch (Exception $e) {
            // Fallback: Log the notification if sending fails
            Log::info('Notification fallback:', [
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a success notification with safe handling
     */
    public function sendSuccessNotification(string $title, string $body, int $duration = 5000): void
    {
        $this->sendSafeNotification($title, $body, 'success', $duration);
    }

    /**
     * Send an error notification with safe handling
     */
    public function sendErrorNotification(string $title, string $body, int $duration = 8000): void
    {
        $this->sendSafeNotification($title, $body, 'danger', $duration);
    }

    /**
     * Send a warning notification with safe handling
     */
    public function sendWarningNotification(string $title, string $body, int $duration = 6000): void
    {
        $this->sendSafeNotification($title, $body, 'warning', $duration);
    }
}
