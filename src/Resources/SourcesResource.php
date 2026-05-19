<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class SourcesResource extends AbstractResource
{
    public function create(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->post($this->endpoint($subdomain, 'sources'), $payload);
    }

    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'sources'), $filters);
    }

    public function find(int|string $sourceId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'sources/'.$sourceId));
    }

    public function update(int|string $sourceId, array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->put($this->endpoint($subdomain, 'sources/'.$sourceId), $payload);
    }

    public function delete(int|string $sourceId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->delete($this->endpoint($subdomain, 'sources/'.$sourceId));
    }
}