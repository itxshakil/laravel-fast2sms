<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Console\Commands;

use Illuminate\Console\Command;
use JsonException;
use Shakil\Fast2sms\Facades\Fast2sms;

/**
 * Lists all Fast2sms events that can be listened to.
 */
class ListEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fast2sms:events
                          {--json : Output the result as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Fast2sms events available for listening';

    /**
     * Execute the console command.
     *
     * @throws JsonException
     */
    public function handle(): int
    {
        $events = Fast2sms::events();

        if ($this->option('json')) {
            $this->line(json_encode($events, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $rows = array_map(
            fn (string $class, string $description): array => [$class, $description],
            array_keys($events),
            array_values($events),
        );

        $this->table(['Event', 'Description'], $rows);

        return self::SUCCESS;
    }
}
