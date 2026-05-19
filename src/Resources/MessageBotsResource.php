<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class MessageBotsResource extends AbstractResource
{
    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'messagebots'), $filters);
    }

    public function find(int|string $messageBotId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'messagebots/'.$messageBotId));
    }
}