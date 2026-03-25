<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class UltraMsgService
{
    protected WhatsAppApi $client;

    public function __construct(?string $instanceId = null, ?string $token = null)
    {
        [$defaultInstance, $defaultToken] = self::resolveDefaultCredentials();

        $instanceId = $instanceId ?: $defaultInstance;
        $token = $token ?: $defaultToken;

        if (! $instanceId || ! $token) {
            throw new \InvalidArgumentException('UltraMsg instance ID and token are required');
        }

        $this->client = new WhatsAppApi($token, $instanceId);

        if ($this->shouldLogRequests()) {
            Log::info('UltraMsg service initialized', [
                'instance_id' => $instanceId,
            ]);
        }
    }

    /**
     * Create service instance with default configuration
     */
    public static function default(): self
    {
        return new self;
    }

    /**
     * Send a text message
     */
    public function sendMessage(string $to, string $body, int $priority = 10, ?string $referenceId = null): array
    {
        try {
            $originalTo = $to;
            $to = $this->getDestinationNumber($to);

            if ($this->shouldLogRequests()) {
                Log::info('Sending WhatsApp message', [
                    'original_to' => $originalTo,
                    'to' => $to,
                    'body' => $body,
                    'priority' => $priority,
                    'reference_id' => $referenceId,
                    'is_test_mode' => $this->isTestMode(),
                ]);
            }

            $response = $this->client->sendChatMessage($to, $this->formatMessageForTestMode($body, $originalTo), $priority, $referenceId ?? '');

            if ($this->shouldLogResponses()) {
                Log::info('WhatsApp message response', [
                    'response' => $response,
                ]);
            }

            // Check if the response contains an error
            if (isset($response['Error'])) {
                throw new \RuntimeException('WhatsApp API error: '.$response['Error']);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'error' => $e->getMessage(),
                'to' => $to ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Send an image message
     */
    public function sendImage(string $to, string $imageUrl, string $caption = '', int $priority = 10, ?string $referenceId = null): array
    {
        try {
            $originalTo = $to;
            $to = $this->getDestinationNumber($to);

            if ($this->shouldLogRequests()) {
                Log::info('Sending WhatsApp image', [
                    'original_to' => $originalTo,
                    'to' => $to,
                    'image_url' => $imageUrl,
                    'caption' => $caption,
                    'is_test_mode' => $this->isTestMode(),
                ]);
            }

            $response = $this->client->sendImageMessage($to, $imageUrl, $this->formatMessageForTestMode($caption, $originalTo), $priority, $referenceId);

            if ($this->shouldLogResponses()) {
                Log::info('WhatsApp image response', [
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp image', [
                'error' => $e->getMessage(),
                'to' => $to ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Send a document message
     */
    public function sendDocument(string $to, string $documentUrl, string $filename = '', int $priority = 10, ?string $referenceId = null): array
    {
        try {
            $originalTo = $to;
            $to = $this->getDestinationNumber($to);

            if ($this->shouldLogRequests()) {
                Log::info('Sending WhatsApp document', [
                    'original_to' => $originalTo,
                    'to' => $to,
                    'document_url' => $documentUrl,
                    'filename' => $filename,
                    'is_test_mode' => $this->isTestMode(),
                ]);
            }

            $response = $this->client->sendDocumentMessage($to, $filename, $documentUrl, '', $priority, $referenceId);

            if ($this->shouldLogResponses()) {
                Log::info('WhatsApp document response', [
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp document', [
                'error' => $e->getMessage(),
                'to' => $to ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Get instance status
     */
    public function getInstanceStatus(): array
    {
        try {
            $response = $this->client->getInstanceStatus();

            if ($this->shouldLogResponses()) {
                Log::info('Instance status response', [
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get instance status', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get message statistics
     */
    public function getMessageStatistics(): array
    {
        try {
            $response = $this->client->getMessageStatistics();

            if ($this->shouldLogResponses()) {
                Log::info('Message statistics response', [
                    'response' => $response,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get message statistics', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Format message to include original recipient info when in test mode
     */
    protected function formatMessageForTestMode(string $message, string $originalTo): string
    {
        if (! $this->isTestMode()) {
            return $message;
        }

        $testPrefix = "🧪 **TEST MODE** 🧪\n";
        $testPrefix .= "📱 Original recipient: {$originalTo}\n";
        $testPrefix .= '🌍 Environment: '.app()->environment()."\n";
        $testPrefix .= str_repeat('-', 30)."\n\n";

        return $testPrefix.$message;
    }

    /**
     * Get the destination number (test number in test/local environments)
     */
    protected function getDestinationNumber(string $originalNumber): string
    {
        if ($this->isTestMode()) {
            $testNumber = $this->getTestPhoneNumber();
            if (! $testNumber) {
                Log::warning('Test mode is enabled but no test phone number is configured', [
                    'original_number' => $originalNumber,
                    'environment' => app()->environment(),
                ]);

                // Fall back to original number if no test number is configured
                return $this->formatPhoneNumber($originalNumber);
            }

            Log::info('Redirecting WhatsApp message to test number', [
                'original_number' => $originalNumber,
                'test_number' => $testNumber,
                'environment' => app()->environment(),
            ]);

            return $this->formatPhoneNumber($testNumber);
        }

        return $this->formatPhoneNumber($originalNumber);
    }

    /**
     * Check if we're in test mode (local or testing environment)
     */
    protected function isTestMode(): bool
    {
        $setting = self::getSetting('ultramsg_test_mode');

        if ($setting !== null) {
            return (bool) $setting;
        }

        return (bool) config('ultramsg.test_mode', false);
    }

    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters except +
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        // Handle Iraqi numbers specifically
        if (preg_match('/^(00964|0964|\+964|964)(\d{9})$/', $phoneNumber, $matches)) {
            // Iraqi number with country code - use just the 9 digits after country code
            return $matches[2];
        } elseif (preg_match('/^07(\d{8})$/', $phoneNumber, $matches)) {
            // Iraqi mobile number starting with 07 - remove the 0
            return '7'.$matches[1];
        } elseif (preg_match('/^7(\d{8})$/', $phoneNumber)) {
            // Iraqi mobile number starting with 7 - use as is
            return $phoneNumber;
        } elseif (preg_match('/^\+(\d+)$/', $phoneNumber, $matches)) {
            // International number with + - remove the +
            return $matches[1];
        } else {
            // Default: remove any leading zeros and use as is
            return ltrim($phoneNumber, '0');
        }
    }

    protected static function resolveDefaultCredentials(): array
    {
        $instance = self::getSetting('ultramsg_instance_id');
        $token = self::getSetting('ultramsg_token');

        if (! $instance || ! $token) {
            $instance = config('ultramsg.default_instance_id');
            $token = config('ultramsg.default_token');
        }

        return [$instance, $token];
    }

    protected static function getSetting(string $key): mixed
    {
        $settings = Setting::where('key', $key)->first();
        if ($settings) {
            return $settings->value;
        }

        return null;
    }

    protected function shouldLogRequests(): bool
    {
        $setting = self::getSetting('ultramsg_log_requests');

        if ($setting !== null) {
            return (bool) $setting;
        }

        return (bool) config('ultramsg.log_requests', true);
    }

    protected function shouldLogResponses(): bool
    {
        $setting = self::getSetting('ultramsg_log_responses');

        if ($setting !== null) {
            return (bool) $setting;
        }

        return (bool) config('ultramsg.log_responses', true);
    }

    protected function getTestPhoneNumber(): ?string
    {
        $setting = self::getSetting('ultramsg_test_phone_number');

        if ($setting) {
            return $setting;
        }

        return config('ultramsg.test_phone_number');
    }
}
