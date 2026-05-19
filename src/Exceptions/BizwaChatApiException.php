<?php

namespace BizwaChat\LaravelNotification\Exceptions;

use Throwable;

class BizwaChatApiException extends BizwaChatException
{
    public function __construct(
        string $message,
        protected int $status,
        protected array $responseBody = [],
        array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $status, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function responseBody(): array
    {
        return $this->responseBody;
    }
}