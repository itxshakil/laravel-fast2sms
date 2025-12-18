<?php

declare(strict_types=1);

namespace Shakil\Fast2sms;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Shakil\Fast2sms\Events\SmsFailed;
use Shakil\Fast2sms\Events\SmsSent;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Responses\DltManagerResponse;
use Shakil\Fast2sms\Responses\Fast2smsResponse;
use Shakil\Fast2sms\Responses\SmsResponse;
use Shakil\Fast2sms\Responses\WalletBalanceResponse;
use Throwable;

/**
 * Base class for Fast2sms service, handling HTTP client setup and API execution.
 */
abstract class BaseFast2smsService
{
    /**
     * The API key for Fast2sms.
     */
    protected string $apiKey;

    /**
     * @throws Fast2smsException
     */
    public function __construct()
    {
        $apiKey = config('fast2sms.api_key');

        if ($apiKey === null || $apiKey === '') {
            throw new Fast2smsException('Fast2sms API Key is not configured. Please set FAST2SMS_API_KEY in your .env file.');
        }

        $this->apiKey = $apiKey;
    }

    /**
     * Executes the API call to Fast2sms and returns the mapped response.
     *
     * @param  array  $payload  The request payload.
     * @param  string  $path  The API endpoint path (default: /bulkV2).
     *
     * @throws Fast2smsException
     */
    protected function executeApiCall(array $payload = [], string $path = '/bulkV2'): Fast2smsResponse
    {
        $response = null;
        $multipart = collect($payload)
            ->map(fn ($v, $k): array => ['name' => $k, 'contents' => $v])
            ->values()
            ->toArray();

        try {
            $response = $this->http()->post($path, $multipart);

            if ($response->successful()) {
                return $this->handleSuccessResponse($payload, $response);
            }

            $error = $response->json('message', 'Unknown Fast2sms API error.');
            $exception = new Fast2smsException("Fast2sms API Error: $error", $response->status());

            Event::dispatch(new SmsFailed($payload, $exception, $response->json()));
            throw $exception;
        } catch (Throwable $e) {
            if (! isset($exception)) {
                $exception = new Fast2smsException(
                    "Fast2sms API call failed: {$e->getMessage()}",
                    $e->getCode(),
                    $e
                );
                Event::dispatch(new SmsFailed($payload, $exception, $response?->json()));
            }
            throw $exception;
        } finally {
            $this->afterApiCall();
        }
    }

    /**
     * Make an HTTP client for Fast2sms.
     */
    protected function http(): PendingRequest
    {
        return Http::retry(3, 100)->baseUrl(config('fast2sms.base_url'))
            ->timeout(config('fast2sms.timeout'))
            ->withHeaders(['Authorization' => $this->apiKey])
            ->asMultipart();
    }

    public function handleSuccessResponse(array $payload, PromiseInterface|Response $response): Fast2smsResponse
    {
        // TODO: Handle response based on the payload and response structure.
        return $this->mapApiResponse($payload, $response->json());
    }

    /**
     * Maps API response data to the correct response object.
     * @param array<string, mixed> $data
     */
    private function mapApiResponse(array $payload, array $data): Fast2smsResponse
    {
        if (isset($data['wallet'])) {
            return new WalletBalanceResponse($data);
        }

        if (isset($data['request_id'])) {
            $smsResponse = new SmsResponse($data);
            Event::dispatch(new SmsSent($payload, $smsResponse));

            return $smsResponse;
        }

        if (isset($data['success'], $data['data'])) {
            return new DltManagerResponse($data);
        }

        return new Fast2smsResponse($data);
    }

    /**
     * Hook method executed after every API call.
     *
     * Child classes can override this to reset state or perform
     * post-request cleanup.
     */
    protected function afterApiCall(): void
    {
        // Default: no action. Override in subclasses.
    }
}
