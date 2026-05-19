<?php

namespace BizwaChat\LaravelNotification\Support;

use BizwaChat\LaravelNotification\Concerns\LogsExceptions;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatConfigurationException;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatException;
use BizwaChat\LaravelNotification\Messages\AbstractMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class BizwaChatRouteResolver
{
    use LogsExceptions;

    public function resolve(mixed $notifiable = null, ?Notification $notification = null, ?AbstractMessage $message = null): array
    {
        try {
            $messageRoute = $message?->routeData() ?? [];
            $notificationRoute = $this->notificationRoute($notifiable, $notification);
            $configRoute = [
                'subdomain' => config('bizwachat-notification.subdomain'),
            ];
            $attributeRoute = [
                'phone_number' => $this->phoneFromAttributes($notifiable),
                'subdomain' => $this->subdomainFromAttributes($notifiable),
                'from_phone_number_id' => $this->fromPhoneNumberIdFromAttributes($notifiable),
            ];

            $resolved = array_merge(
                array_filter($configRoute, [$this, 'filledValue']),
                array_filter($attributeRoute, [$this, 'filledValue']),
                array_filter($notificationRoute, [$this, 'filledValue']),
                array_filter($messageRoute, [$this, 'filledValue']),
            );

            $phoneNumber = $resolved['phone_number'] ?? $resolved['phone'] ?? null;
            $subdomain = $resolved['subdomain'] ?? null;

            if (! $this->filledValue($phoneNumber)) {
                throw new BizwaChatConfigurationException('BizwaChat recipient phone number could not be resolved.');
            }

            if (! $this->filledValue($subdomain)) {
                throw new BizwaChatConfigurationException('BizwaChat subdomain is not configured.');
            }

            return [
                'phone_number' => (string) $phoneNumber,
                'subdomain' => (string) $subdomain,
                'from_phone_number_id' => $resolved['from_phone_number_id'] ?? null,
            ];
        } catch (Throwable $exception) {
            $this->logException('Failed to resolve BizwaChat route.', $exception, [
                'notifiable_class' => is_object($notifiable) ? $notifiable::class : gettype($notifiable),
                'notification_class' => $notification ? $notification::class : null,
                'message_class' => $message ? $message::class : null,
            ]);

            if ($exception instanceof BizwaChatException) {
                throw $exception;
            }

            throw new BizwaChatConfigurationException('Failed to resolve BizwaChat route.', [], 0, $exception);
        }
    }

    protected function notificationRoute(mixed $notifiable, ?Notification $notification): array
    {
        if (! is_object($notifiable) || ! method_exists($notifiable, 'routeNotificationFor')) {
            return [];
        }

        $route = $notifiable->routeNotificationFor('bizwachat', $notification);

        if (is_string($route)) {
            return ['phone_number' => $route];
        }

        return is_array($route) ? $route : [];
    }

    protected function phoneFromAttributes(mixed $notifiable): ?string
    {
        $fields = (array) config('bizwachat-notification.route.phone_fields', ['phone', 'phone_number', 'mobile']);

        if ($fields === []) {
            $fields = ['phone', 'phone_number', 'mobile'];
        }

        foreach ($fields as $field) {
            $value = data_get($notifiable, $field);

            if ($this->filledValue($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function subdomainFromAttributes(mixed $notifiable): ?string
    {
        $field = (string) config('bizwachat-notification.route.subdomain_field', 'bizwachat_subdomain');
        $value = data_get($notifiable, $field);

        return $this->filledValue($value) ? (string) $value : null;
    }

    protected function fromPhoneNumberIdFromAttributes(mixed $notifiable): ?string
    {
        $field = (string) config('bizwachat-notification.route.from_phone_number_id_field', 'bizwachat_from_phone_number_id');
        $value = data_get($notifiable, $field);

        return $this->filledValue($value) ? (string) $value : null;
    }

    protected function filledValue(mixed $value): bool
    {
        return ! in_array($value, [null, ''], true);
    }
}