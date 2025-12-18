<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use Shakil\Fast2sms\Events\LowBalanceDetected;
use Override;
use Shakil\Fast2sms\Contracts\Fast2smsInterface;
use Shakil\Fast2sms\Enums\DltManagerType;
use Shakil\Fast2sms\Enums\SmsLanguage;
use Shakil\Fast2sms\Enums\SmsRoute;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\Fast2smsResponse;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;
use Shakil\Fast2sms\Traits\HandlesFaking;
use Shakil\Fast2sms\Traits\ManagesSmsParameters;
use Shakil\Fast2sms\Traits\QueuesSms;

/**
 * Main service class for interacting with the Fast2sms API.
 *
 * This class provides methods for sending various types of SMS messages
 * such as Quick, DLT, and OTP, as well as checking wallet balance and
 * retrieving DLT manager details.
 */
class Fast2sms extends BaseFast2smsService implements Fast2smsInterface
{
    use HandlesFaking;
    use ManagesSmsParameters;
    use QueuesSms;

    /**
     * Create a new Fast2sms instance.
     *
     * @throws Fast2smsException
     */
    public function __construct()
    {
        parent::__construct();

        $this->senderId = config('fast2sms.default_sender_id');
        $this->route = SmsRoute::from(config('fast2sms.default_route'));
        $this->language = SmsLanguage::ENGLISH;

        self::$sentMessages ??= collect();
    }

    /**
     * Send an SMS using the currently configured parameters.
     *
     *
     * @throws Fast2smsException If required parameters are missing or invalid.
     */
    public function send(): Fast2smsResponse
    {
        return $this->executeSend();
    }

    /**
     * Execute an SMS send request to Fast2sms.
     *
     *
     * @throws Fast2smsException If validation fails or API call fails.
     */
    private function executeSend(): Fast2smsResponse
    {
        $this->validateForRoute();

        $payload = $this->buildPayloadForRoute();

        return $this->executeApiCall($payload);
    }

    /**
     * Validate parameters required for the selected route.
     *
     * @throws Fast2smsException If validation fails.
     */
    private function validateForRoute(): void
    {
        $this->assertNotEmpty($this->apiKey, 'Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.');
        $this->assertNotEmpty($this->numbers, 'Recipient number(s) are required. Use ->to().');

        match ($this->route) {
            SmsRoute::QUICK => $this->assertNotEmpty($this->message, 'Message content is required for Quick SMS.'),
            SmsRoute::DLT, SmsRoute::DLT_MANUAL => $this->validateDltParameters(),
            SmsRoute::OTP => $this->assertNotEmpty($this->message, 'OTP value is required for OTP SMS.'),
            default => null
        };
    }

    /**
     * Assert that a value is not empty.
     *
     * @param  mixed  $value  The value to check.
     * @param  string  $message  The error message if the value is empty.
     *
     * @throws Fast2smsException If the value is empty.
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
     * @throws Fast2smsException If validation fails.
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

    /**
     * Build the API payload based on the selected route.
     *
     * @return array<string, mixed>
     */
    private function buildPayloadForRoute(): array
    {
        $base = [
            'route' => $this->route->value,
            'numbers' => implode(',', $this->numbers),
            'flash' => $this->flash ? 1 : 0,
        ];

        return array_merge($base, match ($this->route) {
            SmsRoute::DLT, SmsRoute::DLT_MANUAL => $this->payloadForDlt(),
            SmsRoute::OTP => ['variables_values' => $this->message],
            SmsRoute::QUICK => $this->payloadForQuick(),
            default => [],
        }, $this->scheduleTime ? ['schedule_time' => $this->scheduleTime] : []);
    }

    /**
     * Build the payload for a DLT message.
     *
     * @return array<string, mixed>
     */
    private function payloadForDlt(): array
    {
        return [
            'sender_id' => $this->senderId,
            'message' => $this->message,
            'entity_id' => $this->entityId,
            'template_id' => $this->templateId,
            'variables_values' => $this->variablesValues,
        ];
    }

    /**
     * Build the payload for a Quick SMS message.
     *
     * @return array<string, mixed>
     */
    private function payloadForQuick(): array
    {
        return [
            'message' => $this->message,
            'language' => $this->language->value,
        ];
    }

    /**
     * Quickly send an SMS with minimal configuration.
     *
     * @param  string|array  $numbers  One or more recipient numbers.
     * @param  string  $message  The SMS message content.
     * @param  SmsLanguage|null  $language  Optional message language.
     *
     * @throws Fast2smsException If validation fails.
     */
    public function quick(string|array $numbers, string $message, ?SmsLanguage $language = null): Fast2smsResponse
    {
        $this->setQuick($numbers, $message, $language);

        return $this->send();
    }

    /**
     * Send an SMS via DLT route.
     *
     * @param  string|array  $numbers  One or more recipient numbers.
     * @param  string  $templateId  The registered DLT template ID.
     * @param  array|string  $variablesValues  Template variable values.
     * @param  string|null  $senderId  Optional sender ID.
     * @param  string|null  $entityId  Optional entity ID (required for DLT_MANUAL route).
     *
     * @throws Fast2smsException If validation fails.
     */
    public function dlt(string|array $numbers, string $templateId, array|string $variablesValues, ?string $senderId = null, ?string $entityId = null): Fast2smsResponse
    {
        $this->setDlt($numbers, $templateId, $variablesValues, $senderId, $entityId);

        return $this->send();
    }

    /**
     * Send an OTP SMS.
     *
     * @param  string|array  $numbers  One or more recipient numbers.
     * @param  string  $otpValue  The OTP code to send.
     *
     * @throws Fast2smsException If validation fails.
     */
    public function otp(string|array $numbers, string $otpValue): Fast2smsResponse
    {
        $this->setOtp($numbers, $otpValue);

        return $this->send();
    }

    /**
     * Retrieve the wallet balance from Fast2sms.
     *
     * @param  float|null  $threshold  Optional threshold to check for low balance
     *
     * @throws Fast2smsException If the API call fails.
     */
    public function checkBalance(?float $threshold = null): Fast2smsResponse
    {
        /**
         * @var WalletBalanceResponse $response
         */
        $response = $this->executeApiCall([], '/wallet');

        if ($threshold !== null) {
            $balance = $response->balance;
            if ($balance <= $threshold) {
                event(new LowBalanceDetected($balance, $threshold));
            }
        }

        return $response;
    }

    /**
     * Retrieve DLT manager details from Fast2sms.
     *
     * @param  DltManagerType  $type  The type of DLT manager data ('sender' or 'template').
     *
     * @throws Fast2smsException If validation fails or API call fails.
     */
    public function dltManager(DltManagerType $type): Fast2smsResponse
    {
        $this->validateDltManagerType();

        return $this->executeApiCall(['type' => $type->value], '/dlt_manager');
    }

    /**
     * Validate the DLT manager type value.
     *
     *
     * @throws Fast2smsException If the value is invalid.
     */
    private function validateDltManagerType(): void
    {
        $this->assertNotEmpty($this->apiKey, 'Fast2sms API Key is not configured.');
    }

    /**
     * Hook method called after every API call.
     *
     * Used to reset SMS parameters for the next request.
     */
    #[Override]
    protected function afterApiCall(): void
    {
        $this->resetParameters();
    }
}
