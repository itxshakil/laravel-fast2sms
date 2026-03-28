<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Traits;

use function is_array;

use Shakil\Fast2sms\Events\WhatsAppFailed;
use Shakil\Fast2sms\Responses\WhatsAppResponse;
use Throwable;

trait ManagesWhatsAppActions
{
    /**
     * Send an interactive message.
     *
     * @throws Throwable
     */
    public function sendInteractive(array $interactive): WhatsAppResponse
    {
        try {
            return $this->sendMetaMessage($this->getTo() ?? '', [
                'type' => 'interactive',
                'interactive' => $interactive,
            ]);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send a location message.
     *
     * @throws Throwable
     */
    public function sendLocation(float $latitude, float $longitude, ?string $name = null, ?string $address = null): WhatsAppResponse
    {
        $location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($name) {
            $location['name'] = $name;
        }

        if ($address) {
            $location['address'] = $address;
        }

        try {
            return $this->sendMetaMessage($this->getTo() ?? '', [
                'type' => 'location',
                'location' => $location,
            ]);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Send a reaction message.
     *
     * @throws Throwable
     */
    public function sendReaction(string $messageId, string $emoji): WhatsAppResponse
    {
        try {
            return $this->sendMetaMessage($this->getTo() ?? '', [
                'type' => 'reaction',
                'reaction' => [
                    'message_id' => $messageId,
                    'emoji' => $emoji,
                ],
            ]);
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Block one or more users.
     *
     * @param string|array<int, string> $numbers
     *
     * @throws Throwable
     */
    public function block(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $users = array_map(fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;

        $payload = [
            'messaging_product' => 'whatsapp',
            'block_users' => $users,
        ];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->post("/whatsapp/{$this->version}/{$phoneNumberId}/block_users", $payload)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Unblock one or more users.
     *
     * @param string|array<int, string> $numbers
     *
     * @throws Throwable
     */
    public function unblock(string|array $numbers): WhatsAppResponse
    {
        $numbers = is_array($numbers) ? $numbers : [$numbers];
        $users = array_map(static fn (string $n): array => ['input' => $n], $numbers);

        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;

        $payload = [
            'messaging_product' => 'whatsapp',
            'block_users' => $users,
        ];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->delete("/whatsapp/{$this->version}/{$phoneNumberId}/block_users", $payload)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get the list of blocked users.
     *
     * @throws Throwable
     */
    public function getBlockedUsers(): WhatsAppResponse
    {
        $phoneNumberId = $this->getFromPhoneNumberId() ?: $this->defaultPhoneNumberId;

        $payload = ['phone_number_id' => $phoneNumberId];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->get("/whatsapp/{$this->version}/{$phoneNumberId}/block_users")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Get delivery report for a specific request ID.
     *
     * @throws Throwable
     */
    public function getDeliveryReport(string $requestId): WhatsAppResponse
    {
        $payload = ['request_id' => $requestId];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->get("/whatsapp/{$requestId}")->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Get WhatsApp logs.
     *
     * @throws Throwable
     */
    public function getLogs(string $from, string $to): WhatsAppResponse
    {
        $payload = ['from' => $from, 'to' => $to];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->get('/whatsapp_logs', $payload)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Get WhatsApp logs summary.
     *
     * @throws Throwable
     */
    public function getSummary(string $from, string $to): WhatsAppResponse
    {
        $payload = ['from' => $from, 'to' => $to];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->get('/whatsapp_summary', $payload)->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }

    /**
     * Upload media to WhatsApp.
     *
     * @throws Throwable
     */
    public function uploadMedia(string $filePath, string $type): WhatsAppResponse
    {
        $payload = ['file_path' => $filePath, 'type' => $type, 'messaging_product' => 'whatsapp'];

        try {
            return $this->makeWhatsAppResponse($payload, $this->client->upload("/whatsapp/{$this->version}/{$this->defaultPhoneNumberId}/media", $filePath, [
                'type' => $type,
                'messaging_product' => 'whatsapp',
            ])->getRawData());
        } catch (Throwable $e) {
            if (config('fast2sms.events.enabled', true)) {
                event(new WhatsAppFailed($payload, $e));
            }
            throw $e;
        }
    }
}
