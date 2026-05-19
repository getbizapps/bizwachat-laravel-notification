<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class StatusesResource extends AbstractResource
{
    public function create(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->post($this->endpoint($subdomain, 'statuses'), $payload);
    }

    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'statuses'), $filters);
    }

    public function find(int|string $statusId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'statuses/'.$statusId));
    }

    public function update(int|string $statusId, array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->put($this->endpoint($subdomain, 'statuses/'.$statusId), $payload);
    }

    public function delete(int|string $statusId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->delete($this->endpoint($subdomain, 'statuses/'.$statusId));
    }
}