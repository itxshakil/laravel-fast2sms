<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Clients;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

use function is_array;

use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Exceptions\ApiException;
use Shakil\Fast2sms\Exceptions\Fast2smsException;
use Shakil\Fast2sms\Exceptions\NetworkException;
use Shakil\Fast2sms\Responses\ResponseFactory;

use Throwable;

use function usleep;

class HttpClient implements ClientInterface
{
    /**
     * @param string $apiKey  The API key for Fast2sms.
     * @param string $baseUrl The base URL for the API.
     * @param int    $timeout The timeout for the requests.
     */
    public function __construct(
        protected string $apiKey,
        protected string $baseUrl,
        protected int $timeout = 30,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function post(string $path, array $payload): ResponseInterface
    {
        return $this->executeRequest(
            fn () => $this->http()->post($path, $payload),
            $payload,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, array $payload = []): ResponseInterface
    {
        return $this->executeRequest(
            fn () => $this->http()->get($path, $payload),
            $payload,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, array $payload = []): ResponseInterface
    {
        return $this->executeRequest(
            fn () => $this->http()->delete($path, $payload),
            $payload,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws Fast2smsException
     */
    public function upload(string $path, string $filePath, array $payload = []): ResponseInterface
    {
        if (! file_exists($filePath)) {
            throw new Fast2smsException("File not found: {$filePath}");
        }

        if (! is_readable($filePath)) {
            throw new Fast2smsException("File is not readable: {$filePath}");
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            throw new Fast2smsException("Failed to read file: {$filePath}");
        }

        return $this->executeRequest(
            fn () => $this->http()
                ->attach('file', $contents, basename($filePath))
                ->post($path, $payload),
            $payload,
        );
    }

    /**
     * Make an HTTP client for Fast2sms.
     * Retries up to 3 times with 100ms delay, only on connection errors or 5xx responses.
     */
    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders(['Authorization' => $this->apiKey]);
    }

    /**
     * Execute an HTTP request with retry on connection errors and 5xx responses.
     * 4xx responses are never retried and throw immediately.
     *
     * @param Closure():Response   $call
     * @param array<string, mixed> $payload
     *
     * @throws ApiException
     * @throws NetworkException
     */
    private function executeRequest(Closure $call, array $payload): ResponseInterface
    {
        $maxAttempts = 3;
        $sleepMicroseconds = 100_000;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $call();

                if ($response->successful()) {
                    return ResponseFactory::make($payload, $response->json());
                }

                $body = $response->json() ?? [];
                $message = is_array($body) && isset($body['message'])
                    ? (string) $body['message']
                    : 'Unknown Fast2sms API error.';

                $exception = ApiException::fromStatus($response->status(), "Fast2sms API Error: {$message}", $body);

                if ($response->clientError()) {
                    throw $exception;
                }

                $lastException = $exception;
            } catch (ConnectionException $e) {
                $lastException = new NetworkException("Fast2sms network error: {$e->getMessage()}", $e);
            } catch (ApiException|NetworkException $e) {
                throw $e;
            } catch (Throwable $e) {
                $lastException = new NetworkException("Fast2sms request failed: {$e->getMessage()}", $e);
            }

            if ($attempt < $maxAttempts) {
                usleep($sleepMicroseconds);
            }
        }

        /** @var ApiException|NetworkException $lastException */
        throw $lastException;
    }
}
