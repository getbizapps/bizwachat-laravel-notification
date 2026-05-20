<?php

namespace BizwaChat\LaravelNotification\Http;

use BizwaChat\LaravelNotification\Concerns\LogsExceptions;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatApiException;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatConfigurationException;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatException;
use BizwaChat\LaravelNotification\Support\ApiResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use SplFileInfo;
use Throwable;

class BizwaChatClient
{
    use LogsExceptions;

    public function request(
        string $method,
        string $uri,
        array $payload = [],
        array $query = [],
        array $headers = [],
        bool $multipart = false,
    ): ApiResponse {
        try {
            $request = $this->baseRequest($headers);
            $normalizedUri = $this->normalizeUri($uri);
            $response = $multipart
                ? $this->sendMultipartRequest($request, $method, $normalizedUri, $payload, $query)
                : $request->send($method, $normalizedUri, $this->jsonOptions($method, $payload, $query));
        } catch (Throwable $exception) {
            $this->logException('BizwaChat API transport request failed.', $exception, [
                'method' => $method,
                'uri' => $uri,
                'multipart' => $multipart,
            ]);

            if ($this->shouldThrow()) {
                throw new BizwaChatException('BizwaChat API transport request failed.', [
                    'method' => $method,
                    'uri' => $uri,
                ], 0, $exception);
            }

            return ApiResponse::failed(0, ['message' => $exception->getMessage()], $exception);
        }

        return $this->toApiResponse($response, $method, $uri);
    }

    public function get(string $uri, array $query = [], array $headers = []): ApiResponse
    {
        return $this->request('GET', $uri, [], $query, $headers);
    }

    public function post(string $uri, array $payload = [], array $headers = []): ApiResponse
    {
        return $this->request('POST', $uri, $payload, [], $headers);
    }

    public function put(string $uri, array $payload = [], array $headers = []): ApiResponse
    {
        return $this->request('PUT', $uri, $payload, [], $headers);
    }

    public function delete(string $uri, array $query = [], array $headers = []): ApiResponse
    {
        return $this->request('DELETE', $uri, [], $query, $headers);
    }

    public function postMultipart(string $uri, array $payload = [], array $query = [], array $headers = []): ApiResponse
    {
        return $this->request('POST', $uri, $payload, $query, $headers, true);
    }

    public function postAuto(string $uri, array $payload = [], array $query = [], array $headers = []): ApiResponse
    {
        if ($this->containsFilePayload($payload)) {
            return $this->postMultipart($uri, $payload, $query, $headers);
        }

        return $this->request('POST', $uri, $payload, $query, $headers);
    }

    protected function toApiResponse(Response $response, string $method, string $uri): ApiResponse
    {
        try {
            $body = $response->json();
            $body = is_array($body) ? $body : [];
            $apiResponse = new ApiResponse($response->successful(), $response->status(), $body, $response->headers());

            if ($response->failed()) {
                throw new BizwaChatApiException(
                    $body['message'] ?? 'BizwaChat API request failed.',
                    $response->status(),
                    $body,
                    [
                        'method' => $method,
                        'uri' => $uri,
                    ],
                );
            }

            return $apiResponse;
        } catch (BizwaChatApiException $exception) {
            $this->logException('BizwaChat API returned an error response.', $exception, [
                'method' => $method,
                'uri' => $uri,
                'status' => $exception->status(),
                'response_body' => $exception->responseBody(),
            ]);

            if ($this->shouldThrow()) {
                throw $exception;
            }

            return ApiResponse::failed($exception->status(), $exception->responseBody(), $exception);
        } catch (Throwable $exception) {
            $this->logException('BizwaChat API response parsing failed.', $exception, [
                'method' => $method,
                'uri' => $uri,
                'status' => $response->status(),
            ]);

            if ($this->shouldThrow()) {
                throw new BizwaChatException('BizwaChat API response parsing failed.', [
                    'method' => $method,
                    'uri' => $uri,
                    'status' => $response->status(),
                ], 0, $exception);
            }

            return ApiResponse::failed($response->status(), ['message' => $exception->getMessage()], $exception);
        }
    }

    protected function baseRequest(array $headers = []): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout((int) config('bizwachat-notification.timeout', 30))
            ->connectTimeout((int) config('bizwachat-notification.connect_timeout', 10))
            ->withToken($this->apiToken())
            ->withHeaders($headers);
    }

    protected function sendMultipartRequest(
        PendingRequest $request,
        string $method,
        string $uri,
        array $payload,
        array $query = [],
    ): Response {
        foreach ($this->buildMultipartPayload($payload) as $part) {
            $request = $request->attach(
                $part['name'],
                $part['contents'],
                $part['filename'] ?? null,
                $part['headers'] ?? []
            );
        }

        return $request->send($method, $uri, [
            'query' => $query,
            'multipart' => [],
        ]);
    }

    protected function buildMultipartPayload(array $payload, string $prefix = ''): array
    {
        $parts = [];

        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }

            $name = $prefix === '' ? (string) $key : $prefix.'['.$key.']';

            if (is_array($value)) {
                $parts = array_merge($parts, $this->buildMultipartPayload($value, $name));

                continue;
            }

            $parts[] = $this->normalizeMultipartPart($name, $value);
        }

        return $parts;
    }

    protected function normalizeMultipartPart(string $name, mixed $value): array
    {
        if ($value instanceof UploadedFile) {
            return [
                'name' => $name,
                'contents' => fopen($value->getRealPath(), 'r'),
                'filename' => $value->getClientOriginalName(),
                'headers' => [
                    'Content-Type' => $value->getMimeType() ?: 'application/octet-stream',
                ],
            ];
        }

        if ($value instanceof SplFileInfo) {
            return [
                'name' => $name,
                'contents' => fopen($value->getRealPath(), 'r'),
                'filename' => $value->getFilename(),
            ];
        }

        if (is_string($value) && is_file($value)) {
            return [
                'name' => $name,
                'contents' => fopen($value, 'r'),
                'filename' => basename($value),
            ];
        }

        if (is_resource($value)) {
            return [
                'name' => $name,
                'contents' => $value,
            ];
        }

        return [
            'name' => $name,
            'contents' => $this->normalizeScalar($value),
        ];
    }

    protected function jsonOptions(string $method, array $payload = [], array $query = []): array
    {
        $options = [];

        if ($query !== []) {
            $options['query'] = $query;
        }

        if (in_array(strtoupper($method), ['GET', 'DELETE'], true)) {
            if ($payload !== []) {
                $options['query'] = array_merge($options['query'] ?? [], $payload);
            }

            return $options;
        }

        if ($payload !== []) {
            $options['json'] = $payload;
        }

        return $options;
    }

    protected function normalizeScalar(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value) => (string) $value,
            default => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '',
        };
    }

    protected function containsFilePayload(array $payload): bool
    {
        foreach ($payload as $value) {
            if (is_array($value) && $this->containsFilePayload($value)) {
                return true;
            }

            if ($value instanceof UploadedFile || $value instanceof SplFileInfo) {
                return true;
            }

            if (is_string($value) && is_file($value)) {
                return true;
            }

            if (is_resource($value)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeUri(string $uri): string
    {
        return '/'.ltrim($uri, '/');
    }

    protected function shouldThrow(): bool
    {
        return (bool) config('bizwachat-notification.throw', true);
    }

    protected function baseUrl(): string
    {
        $baseUrl = rtrim((string) config('bizwachat-notification.base_url', 'https://bizwachat.com'), '/');

        if ($baseUrl === '') {
            throw new BizwaChatConfigurationException('BizwaChat base URL is not configured.');
        }

        return $baseUrl;
    }

    protected function apiToken(): string
    {
        $token = (string) config('bizwachat-notification.api_token', '');

        if ($token === '') {
            throw new BizwaChatConfigurationException('BizwaChat API token is not configured.');
        }

        return $token;
    }
}