<?php

namespace BizwaChat\LaravelNotification\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

trait LogsExceptions
{
    protected function logException(string $message, Throwable $exception, array $context = []): void
    {
        $context = array_merge($context, [
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
        ]);

        $channel = config('bizwachat-notification.log_channel', 'stack');

        if ($channel) {
            Log::channel($channel)->error($message, $context);

            return;
        }

        Log::error($message, $context);
    }
}