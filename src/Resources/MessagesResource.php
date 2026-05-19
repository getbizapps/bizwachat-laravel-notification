<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class MessagesResource extends AbstractResource
{
    public function sendSimple(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->post($this->endpoint($subdomain, 'messages/send'), $payload);
    }

    public function sendTemplate(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->postMultipart($this->endpoint($subdomain, 'messages/template'), $payload);
    }

    public function sendMedia(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->postMultipart($this->endpoint($subdomain, 'messages/media'), $payload);
    }
}