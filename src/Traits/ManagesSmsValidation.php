<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Shakil\Fast2sms\Contracts\PayloadBuilderInterface;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\ValidationException;
use Shakil\Fast2sms\PayloadBuilders\DltPayloadBuilder;
use Shakil\Fast2sms\PayloadBuilders\OtpPayloadBuilder;
use Shakil\Fast2sms\PayloadBuilders\QuickPayloadBuilder;
use Shakil\Fast2sms\Rules\Fast2smsPhone;

trait ManagesSmsValidation
{
    /**
     * Validate parameters required for the selected route.
     *
     * @throws Fast2smsException
     */
    protected function validateForRoute(): void
    {
        $this->assertNotEmpty($this->config->apiKey, 'Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.');
        $this->assertNotEmpty($this->numbers, 'Recipient number(s) are required. Use ->to().');

        match ($this->route) {
            SmsRoute::QUICK => $this->assertNotEmpty($this->message, 'Message content is required for Quick SMS.'),
            SmsRoute::DLT, SmsRoute::DLT_MANUAL => $this->validateDltParameters(),
            SmsRoute::OTP => $this->assertNotEmpty($this->message, 'OTP value is required for OTP SMS.'),
        };
    }

    /**
     * Build the API payload based on the selected route.
     *
     * @return array<string, mixed>
     */
    protected function buildPayloadForRoute(): array
    {
        $base = [
            'route' => $this->route->value,
            'numbers' => implode(',', $this->numbers),
            'flash' => $this->flash ? 1 : 0,
        ];

        return array_merge(
            $base,
            $this->resolveBuilder()->build($this),
            $this->scheduleTime ? ['schedule_time' => $this->scheduleTime] : [],
        );
    }

    /**
     * Remove duplicate numbers from the recipient list.
     *
     * @param  array<int, string|int> $numbers
     * @return array<int, string|int>
     */
    protected function deduplicateRecipients(array $numbers): array
    {
        return array_values(array_unique($numbers));
    }

    /**
     * Split a recipient list into chunks of the configured batch size.
     *
     * @param  array<int, string|int>             $numbers
     * @return array<int, array<int, string|int>>
     */
    protected function chunkRecipients(array $numbers): array
    {
        $size = (int) config('fast2sms.recipients.batch_size', 0);

        if ($size <= 0) {
            return [$numbers];
        }

        return array_chunk($numbers, $size);
    }

    /**
     * Strip invalid Indian mobile numbers from the list, logging a warning for each removed number.
     * Throws ValidationException if all numbers are invalid.
     *
     * @param  array<int, string|int> $numbers
     * @return array<int, string|int>
     *
     * @throws ValidationException
     */
    protected function filterValidRecipients(array $numbers): array
    {
        $valid = [];

        foreach ($numbers as $number) {
            $v = Validator::make(['number' => (string) $number], ['number' => [new Fast2smsPhone()]]);

            if ($v->fails()) {
                Log::warning('[Fast2SMS] Stripped invalid recipient number.', ['number' => $number]);
            } else {
                $valid[] = $number;
            }
        }

        if ($valid === []) {
            throw ValidationException::allRecipientsInvalid();
        }

        return $valid;
    }

    private function resolveBuilder(): PayloadBuilderInterface
    {
        return match ($this->route) {
            SmsRoute::QUICK => new QuickPayloadBuilder(),
            SmsRoute::DLT, SmsRoute::DLT_MANUAL => new DltPayloadBuilder(),
            SmsRoute::OTP => new OtpPayloadBuilder(),
        };
    }

    /**
     * Assert that a value is not empty.
     *
     * @throws Fast2smsException
     */
    private function assertNotEmpty(mixed $value, string $message): void
    {
        if (empty($value)) {
            throw new Fast2smsException($message);
        }
    }

    /**
     * Validate parameters for a DLT route message.
     *
     * @throws Fast2smsException
     */
    private function validateDltParameters(): void
    {
        $this->assertNotEmpty($this->templateId, 'Template ID is required for DLT.');
        $this->assertNotEmpty($this->variablesValues, 'Variables values are required for DLT.');
        $this->assertNotEmpty($this->senderId, 'Sender ID is required for DLT.');
        if ($this->route === SmsRoute::DLT_MANUAL) {
            $this->assertNotEmpty($this->entityId, 'Entity ID is required for DLT.');
        }
    }
}
