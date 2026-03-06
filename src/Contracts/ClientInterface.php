<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Contracts;

/**
 * Interface for the client that handles communication with the Fast2sms API.
 */
interface ClientInterface
{
    /**
     * Send a POST request to the API.
     *
     * @param  string               $path    The API endpoint path.
     * @param  array<string, mixed> $payload The request payload.
     * @return ResponseInterface    The API response.
     */
    public function post(string $path, array $payload): ResponseInterface;

    /**
     * Send a GET request to the API.
     *
     * @param  string               $path    The API endpoint path.
     * @param  array<string, mixed> $payload The query parameters.
     * @return ResponseInterface    The API response.
     */
    public function get(string $path, array $payload = []): ResponseInterface;

    /**
     * Send a DELETE request to the API.
     *
     * @param  string               $path    The API endpoint path.
     * @param  array<string, mixed> $payload The request payload.
     * @return ResponseInterface    The API response.
     */
    public function delete(string $path, array $payload = []): ResponseInterface;

    /**
     * Upload a file to the API.
     *
     * @param  string               $path     The API endpoint path.
     * @param  string               $filePath The path to the file.
     * @param  array<string, mixed> $payload  The additional payload.
     * @return ResponseInterface    The API response.
     */
    public function upload(string $path, string $filePath, array $payload = []): ResponseInterface;
}
