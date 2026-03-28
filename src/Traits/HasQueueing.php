<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Shakil\Fast2sms\Exceptions\ConfigurationException;

trait HasQueueing
{
    protected ?string $queueConnection = null;

    protected ?string $queueName = null;

    protected ?int $queueDelay = null;

    /**
     * Set the queue connection to be used.
     *
     * @throws ConfigurationException if queuing is not enabled in config.
     */
    public function onConnection(string $connection): self
    {
        $this->ensureQueuingEnabled();
        $this->queueConnection = $connection;

        return $this;
    }

    /**
     * Set the queue name to be used.
     *
     * @throws ConfigurationException if queuing is not enabled in config.
     */
    public function onQueue(string $queue): self
    {
        $this->ensureQueuingEnabled();
        $this->queueName = $queue;

        return $this;
    }

    /**
     * Set the queue delay.
     */
    public function delay(int $seconds): self
    {
        $this->queueDelay = $seconds;

        return $this;
    }

    protected function resetQueueConfig(): void
    {
        $this->queueConnection = null;
        $this->queueName = null;
        $this->queueDelay = null;
    }

    /**
     * Ensure queuing is enabled in config before setting queue options.
     *
     * @throws ConfigurationException
     */
    private function ensureQueuingEnabled(): void
    {
        $enabled = config('fast2sms.queue.enabled', config('fast2sms.queue', false));

        if (! $enabled) {
            throw ConfigurationException::queuingNotEnabled();
        }
    }
}
