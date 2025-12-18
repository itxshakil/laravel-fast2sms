<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Jobs;

use Shakil\Fast2sms\Enums\SmsLanguage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;

/**
 * Job for handling asynchronous SMS sending through Fast2sms.
 *
 * This job class is responsible for processing queued SMS requests using the Fast2sms service.
 * It takes an SmsParameters object containing all necessary SMS configuration and sends
 * the message using the injected Fast2sms service instance.
 *
 * @implements ShouldQueue
 */
class SendSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  SmsParameters  $parameters  Data transfer object containing all SMS parameters
     */
    public function __construct(
        public readonly SmsParameters $parameters
    ) {}

    /**
     * Execute the job.
     *
     * Configures the Fast2sms instance with the stored parameters and sends the SMS.
     * All optional parameters are only set if they have values to maintain clean configuration.
     *
     * @param  Fast2sms  $fast2sms  The Fast2sms service instance
     *
     * @throws Fast2smsException If SMS sending fails or validation fails
     */
    public function handle(Fast2sms $fast2sms): void
    {
        $fast2sms
            ->to($this->parameters->numbers)
            ->message($this->parameters->message)
            ->route($this->parameters->route);

        if ($this->parameters->language instanceof SmsLanguage) {
            $fast2sms->language($this->parameters->language);
        }

        if ($this->parameters->senderId) {
            $fast2sms->senderId($this->parameters->senderId);
        }

        if ($this->parameters->entityId) {
            $fast2sms->entityId($this->parameters->entityId);
        }

        if ($this->parameters->templateId) {
            $fast2sms->templateId($this->parameters->templateId);
        }

        if ($this->parameters->variablesValues) {
            $fast2sms->variables($this->parameters->variablesValues);
        }

        if ($this->parameters->flash) {
            $fast2sms->flash();
        }

        if ($this->parameters->scheduleTime) {
            $fast2sms->schedule($this->parameters->scheduleTime);
        }

        $fast2sms->send();
    }
}
