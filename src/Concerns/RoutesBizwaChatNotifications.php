<?php

namespace BizwaChat\LaravelNotification\Concerns;

trait RoutesBizwaChatNotifications
{
    public function routeNotificationForBizwaChat(): array
    {
        $phoneField = config('bizwachat-notification.route.phone_fields.0', 'phone');
        $subdomainField = config('bizwachat-notification.route.subdomain_field', 'bizwachat_subdomain');
        $fromPhoneNumberIdField = config('bizwachat-notification.route.from_phone_number_id_field', 'bizwachat_from_phone_number_id');

        return array_filter([
            'phone_number' => data_get($this, $phoneField),
            'subdomain' => data_get($this, $subdomainField),
            'from_phone_number_id' => data_get($this, $fromPhoneNumberIdField),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}