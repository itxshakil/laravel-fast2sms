<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

/**
 * Trait HasQueueing.
 *
 * Provides shared queue configuration logic for SMS and WhatsApp messages.
 */
trait HasQueueing
{
    protected ?string $queueConnection = null;

    protected ?string $queueName = null;

    protected ?int $queueDelay = null;

    /**
     * Set the queue connection to be used.
     */
    public function onConnection(string $connection): self
    {
        $this->queueConnection = $connection;

        return $this;
    }

    /**
     * Set the queue name to be used.
     */
    public function onQueue(string $queue): self
    {
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

    /**
     * Reset queue configuration.
     */
    protected function resetQueueConfig(): void
    {
        $this->queueConnection = null;
        $this->queueName = null;
        $this->queueDelay = null;
    }
}
