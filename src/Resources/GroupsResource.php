<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class GroupsResource extends AbstractResource
{
    public function create(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->post($this->endpoint($subdomain, 'groups'), $payload);
    }

    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'groups'), $filters);
    }

    public function find(int|string $groupId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'groups/'.$groupId));
    }

    public function update(int|string $groupId, array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->put($this->endpoint($subdomain, 'groups/'.$groupId), $payload);
    }

    public function delete(int|string $groupId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->delete($this->endpoint($subdomain, 'groups/'.$groupId));
    }
}