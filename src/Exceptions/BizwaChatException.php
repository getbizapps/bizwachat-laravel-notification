<?php

namespace BizwaChat\LaravelNotification\Exceptions;

use RuntimeException;
use Throwable;

class BizwaChatException extends RuntimeException
{
    public function __construct(
        string $message,
        protected array $context = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function context(): array
    {
        return $this->context;
    }
}