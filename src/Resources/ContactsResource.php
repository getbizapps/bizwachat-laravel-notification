<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Support\ApiResponse;

class ContactsResource extends AbstractResource
{
    public function create(array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->post($this->endpoint($subdomain, 'contacts'), $payload);
    }

    public function list(array $filters = [], ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'contacts'), $filters);
    }

    public function find(int|string $contactId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->get($this->endpoint($subdomain, 'contacts/'.$contactId));
    }

    public function update(int|string $contactId, array $payload, ?string $subdomain = null): ApiResponse
    {
        return $this->client->put($this->endpoint($subdomain, 'contacts/'.$contactId), $payload);
    }

    public function delete(int|string $contactId, ?string $subdomain = null): ApiResponse
    {
        return $this->client->delete($this->endpoint($subdomain, 'contacts/'.$contactId));
    }
}