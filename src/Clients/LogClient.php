<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Clients;

use Illuminate\Support\Facades\Log;
use Shakil\Fast2sms\Contracts\ClientInterface;
use Shakil\Fast2sms\Contracts\ResponseInterface;
use Shakil\Fast2sms\Responses\ResponseFactory;

class LogClient implements ClientInterface
{
    /**
     * Send a POST request to the API.
     *
     * @param  string               $path    The API endpoint path.
     * @param  array<string, mixed> $payload The request payload.
     * @return ResponseInterface    The API response.
     */
    public function post(string $path, array $payload): ResponseInterface
    {
        Log::info("Fast2sms SMS Log [Path: {$path}]:", $payload);

        return $this->mockResponse($path, $payload);
    }

    /**
     * Send a GET request to the API.
     *
     * @param  string               $path    The API endpoint path.
     * @param  array<string, mixed> $payload The query parameters.
     * @return ResponseInterface    The API response.
     */
    public function get(string $path, array $payload = []): ResponseInterface
    {
        Log::info("Fast2sms GET Log [Path: {$path}]:", $payload);

        return $this->mockResponse($path, $payload);
    }

    public function delete(string $path, array $payload = []): ResponseInterface
    {
        Log::info("Fast2sms DELETE Log [Path: {$path}]:", $payload);

        return $this->mockResponse($path, $payload);
    }

    public function upload(string $path, string $filePath, array $payload = []): ResponseInterface
    {
        Log::info("Fast2sms UPLOAD Log [Path: {$path}, File: {$filePath}]:", $payload);

        return $this->mockResponse($path, $payload);
    }

    /**
     * Create a mock response based on the path.
     *
     * @param array<string, mixed> $payload
     */
    protected function mockResponse(string $path, array $payload): ResponseInterface
    {
        $mockData = [
            'return' => true,
            'request_id' => 'log-' . uniqid('', true),
            'message' => ['SMS sent successfully (logged)'],
        ];

        if ($path === '/wallet') {
            $mockData = [
                'return' => true,
                'wallet' => 1000,
            ];
        }

        if ($path === '/dlt_manager') {
            $mockData = [
                'success' => true,
                'data' => [],
            ];
        }

        return ResponseFactory::make($payload, $mockData);
    }
}
