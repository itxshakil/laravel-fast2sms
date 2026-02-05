<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Console\Commands;

use Illuminate\Console\Command;

use function in_array;

use Shakil\Fast2sms\Contracts\WhatsAppInterface;
use Shakil\Fast2sms\Exceptions\Fast2smsException;

class WhatsAppWabaDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:waba
                          {type=number : The type of details to fetch (number or template)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get WhatsApp WABA and Template details from Fast2SMS';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppInterface $whatsapp): int
    {
        $type = $this->argument('type');

        if (! in_array($type, ['number', 'template'])) {
            $this->error('Invalid type. Must be "number" or "template".');

            return self::FAILURE;
        }

        try {
            $this->info("Fetching WhatsApp $type details...");
            $response = $whatsapp->getWabaDetails($type);

            if ($response->isSuccess()) {
                $data = $response->getRawData();

                if ($data === []) {
                    $this->warn("No details found for type: {$type}");

                    return self::SUCCESS;
                }

                if ($type === 'number') {
                    $this->table(
                        ['WABA ID', 'Phone Number ID', 'Number', 'Verified Name', 'Status'],
                        array_map(fn (array $item): array => [
                            $item['waba_id'] ?? '',
                            $item['phone_number_id'] ?? '',
                            $item['number'] ?? '',
                            $item['verified_name'] ?? '',
                            $item['connection_status'] ?? '',
                        ], $data),
                    );
                } else {
                    $this->table(
                        ['Template Name', 'Message ID', 'Meta Template ID', 'Status', 'Category'],
                        array_map(fn (array $item): array => [
                            $item['template_name'] ?? '',
                            $item['message_id'] ?? '',
                            $item['meta_template_id'] ?? '',
                            $item['status'] ?? '',
                            $item['category'] ?? '',
                        ], $data),
                    );
                }

                return self::SUCCESS;
            }

            $this->error('Failed to fetch details: ' . $response->message);

            return self::FAILURE;

        } catch (Fast2smsException $e) {
            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
