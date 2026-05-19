<?php

namespace BizwaChat\LaravelNotification\Channels;

use BizwaChat\LaravelNotification\BizwaChatManager;
use BizwaChat\LaravelNotification\Concerns\LogsExceptions;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatConfigurationException;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatException;
use BizwaChat\LaravelNotification\Messages\AbstractMessage;
use BizwaChat\LaravelNotification\Messages\SimpleMessage;
use BizwaChat\LaravelNotification\Support\ApiResponse;
use BizwaChat\LaravelNotification\Support\BizwaChatRouteResolver;
use Illuminate\Notifications\Notification;
use Throwable;

class BizwaChatChannel
{
    use LogsExceptions;

    public function __construct(
        protected BizwaChatManager $manager,
        protected BizwaChatRouteResolver $routeResolver,
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): ?ApiResponse
    {
        if (! method_exists($notification, 'toBizwaChat')) {
            return null;
        }

        try {
            $message = $notification->toBizwaChat($notifiable);

            if ($message === null) {
                return null;
            }

            if (is_string($message)) {
                $message = SimpleMessage::make($message);
            }

            if (! $message instanceof AbstractMessage) {
                throw new BizwaChatConfigurationException(
                    'BizwaChat notifications must return a string or an instance of '.AbstractMessage::class.'.'
                );
            }

            $route = $this->routeResolver->resolve($notifiable, $notification, $message);

            return $message->send($this->manager, $route);
        } catch (Throwable $exception) {
            $this->logException('Failed to send BizwaChat notification.', $exception, [
                'notifiable_class' => is_object($notifiable) ? $notifiable::class : gettype($notifiable),
                'notification_class' => $notification::class,
            ]);

            if ($exception instanceof BizwaChatException) {
                throw $exception;
            }

            throw new BizwaChatException('Failed to send BizwaChat notification.', [], 0, $exception);
        }
    }
}