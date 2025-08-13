<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shakil\Fast2sms\DataTransferObjects\SmsParameters;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Fast2sms;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly SmsParameters $parameters
    ) {}

    /**
     * @throws Fast2smsException
     */
    public function handle(Fast2sms $fast2sms): void
    {
        $fast2sms
            ->to($this->parameters->numbers)
            ->message($this->parameters->message)
            ->route($this->parameters->route);

        if ($this->parameters->language) {
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
