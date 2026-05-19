<?php

namespace BizwaChat\LaravelNotification\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Throwable;

class ApiResponse implements Arrayable, JsonSerializable
{
    public function __construct(
        protected bool $successful,
        protected int $status,
        protected array $body = [],
        protected array $headers = [],
        protected ?Throwable $exception = null,
    ) {
    }

    public static function failed(int $status = 0, array $body = [], ?Throwable $exception = null): self
    {
        return new self(false, $status, $body, [], $exception);
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    public function isFailed(): bool
    {
        return ! $this->successful();
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function message(?string $default = null): ?string
    {
        return $this->body['message'] ?? $default;
    }

    public function data(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->body['data'] ?? [];

        if ($key === null) {
            return $data;
        }

        return data_get($data, $key, $default);
    }

    public function exception(): ?Throwable
    {
        return $this->exception;
    }

    public function toArray(): array
    {
        return [
            'successful' => $this->successful,
            'status' => $this->status,
            'message' => $this->message(),
            'body' => $this->body,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}