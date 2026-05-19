<?php

namespace BizwaChat\LaravelNotification\Messages;

use BizwaChat\LaravelNotification\BizwaChatManager;
use BizwaChat\LaravelNotification\Support\ApiResponse;

abstract class AbstractMessage
{
    protected ?string $phoneNumber = null;

    protected ?string $subdomain = null;

    protected ?string $fromPhoneNumberId = null;

    protected array $contact = [];

    public function to(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function onSubdomain(string $subdomain): static
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    public function fromPhoneNumberId(string $fromPhoneNumberId): static
    {
        $this->fromPhoneNumberId = $fromPhoneNumberId;

        return $this;
    }

    public function contact(array|ContactData $contact): static
    {
        $this->contact = $contact instanceof ContactData ? $contact->toArray() : $contact;

        return $this;
    }

    public function routeData(): array
    {
        return array_filter([
            'phone_number' => $this->phoneNumber,
            'subdomain' => $this->subdomain,
            'from_phone_number_id' => $this->fromPhoneNumberId,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    abstract public function send(BizwaChatManager $manager, array $route): ApiResponse;

    protected function mergeBasePayload(array $payload, array $route): array
    {
        $payload = array_merge([
            'phone_number' => $this->phoneNumber ?? $route['phone_number'] ?? null,
        ], $payload);

        $fromPhoneNumberId = $this->fromPhoneNumberId ?? $route['from_phone_number_id'] ?? null;

        if ($fromPhoneNumberId !== null && $fromPhoneNumberId !== '') {
            $payload['from_phone_number_id'] = $fromPhoneNumberId;
        }

        if ($this->contact !== []) {
            $payload['contact'] = $this->contact;
        }

        return array_filter($payload, fn (mixed $value): bool => $value !== null && $value !== '');
    }

    protected function subdomain(array $route): ?string
    {
        return $this->subdomain ?? $route['subdomain'] ?? null;
    }
}