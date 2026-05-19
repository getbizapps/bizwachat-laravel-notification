<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class TemplatesResource extends AbstractResource
{
    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'templates'), $filters);
    }

    public function find(int|string $templateId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'templates/'.$templateId));
    }
}