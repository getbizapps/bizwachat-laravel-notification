<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class TemplateBotsResource extends AbstractResource
{
    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'templatebots'), $filters);
    }

    public function find(int|string $templateBotId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'templatebots/'.$templateBotId));
    }
}