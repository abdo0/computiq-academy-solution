<?php

namespace App\Console\Commands;

use App\Services\UltraMsgService;
use Illuminate\Console\Command;

class TestWhatsAppMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test 
                            {phone : Phone number to send the message to (with country code)}
                            {--message= : Custom message to send}
                            {--image= : URL of image to send}
                            {--document= : URL of document to send}
                            {--caption= : Caption for image or document}
                            {--priority=10 : Message priority (1-100)}
                            {--check-status : Check instance status before sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp message sending via UltraMsg API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $phone = $this->argument('Phone');
        $message = $this->option('message') ?? __('Test message from Nakhwaa platform');
        $image = $this->option('image');
        $document = $this->option('document');
        $caption = $this->option('caption') ?? '';
        $priority = (int) $this->option('priority');
        $checkStatus = $this->option('check-status');

        $this->info(__('Testing WhatsApp Integration'));
        $this->newLine();

        // Check instance status if requested
        if ($checkStatus) {
            $this->info(__('Checking UltraMsg instance status...'));
            try {
                $service = UltraMsgService::default();
                $status = $service->getInstanceStatus();

                if (isset($status['status'])) {
                    $this->info(__('Instance Status: :status', ['status' => $status['status']]));
                } else {
                    $this->warn(__('Could not retrieve instance status'));
                    $this->line(json_encode($status, JSON_PRETTY_PRINT));
                }
                $this->newLine();
            } catch (\Exception $e) {
                $this->error(__('Failed to check instance status: :error', ['error' => $e->getMessage()]));
                $this->newLine();
            }
        }

        // Display test information
        $this->table(
            [__('Setting'), __('Value')],
            [
                [__('Phone Number'), $phone],
                [__('Test Mode'), config('ultramsg.test_mode') ? __('Enabled') : __('Disabled')],
                [__('Test Phone Number'), config('ultramsg.test_phone_number') ?: __('Not set')],
                [__('Priority'), $priority],
                [__('Message Type'), $image ? __('Image') : ($document ? __('Document') : __('Text'))],
            ]
        );
        $this->newLine();

        try {
            $service = UltraMsgService::default();

            if ($image) {
                $this->info(__('Sending image message...'));
                $response = $service->sendImage($phone, $image, $caption, $priority);
            } elseif ($document) {
                $this->info(__('Sending document message...'));
                $filename = basename(parse_url($document, PHP_URL_PATH)) ?: 'document.pdf';
                $response = $service->sendDocument($phone, $document, $filename, $priority);
            } else {
                $this->info(__('Sending text message...'));
                $response = $service->sendMessage($phone, $message, $priority);
            }

            if (isset($response['sent'])) {
                $this->newLine();
                $this->info('✓ '.__('Message sent successfully!'));
                $this->line(__('Message ID: :id', ['id' => $response['id'] ?? __('N/A')]));
                if (isset($response['to'])) {
                    $this->line(__('Sent to: :to', ['to' => $response['to']]));
                }
                if (config('ultramsg.test_mode')) {
                    $this->warn(__('Note: Test mode is enabled. Message was sent to test number.'));
                }

                return Command::SUCCESS;
            } elseif (isset($response['Error'])) {
                $this->newLine();
                $this->error('✗ '.__('Failed to send message'));
                $this->error(__('Error: :error', ['error' => $response['Error']]));

                return Command::FAILURE;
            } else {
                $this->newLine();
                $this->warn(__('Unexpected response format:'));
                $this->line(json_encode($response, JSON_PRETTY_PRINT));

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ '.__('Failed to send message'));
            $this->error(__('Exception: :error', ['error' => $e->getMessage()]));
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
