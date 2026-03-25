<?php

namespace App\Jobs;

use App\Services\UltraMsgService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        protected string $phone,
        protected string $message,
        protected ?array $serviceCredentials = null,
        protected ?int $priority = 10,
        protected ?string $referenceId = null
    ) {
        $this->onQueue('whatsapp_messages');
    }

    public function handle(): void
    {
        Log::info('WhatsApp message job started', [
            'phone' => $this->phone,
            'reference_id' => $this->referenceId,
            'priority' => $this->priority,
        ]);

        try {
            $response = $this->buildService()->sendMessage(
                to: $this->phone,
                body: $this->message,
                priority: $this->priority ?? (int) config('ultramsg.default_priority', 10),
                referenceId: $this->referenceId
            );
            Log::info('WhatsApp message job completed', [
                'response' => $response,
                'phone' => $this->phone,
                'reference_id' => $this->referenceId,
            ]);
        } catch (\Throwable $exception) {
            Log::error('WhatsApp message job failed', [
                'phone' => $this->phone,
                'reference_id' => $this->referenceId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp message job failed permanently', [
            'phone' => $this->phone,
            'reference_id' => $this->referenceId,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function buildService(): UltraMsgService
    {
        if ($this->serviceCredentials) {
            return new UltraMsgService(
                $this->serviceCredentials['instance_id'] ?? null,
                $this->serviceCredentials['token'] ?? null
            );
        }

        return UltraMsgService::default();
    }
}
