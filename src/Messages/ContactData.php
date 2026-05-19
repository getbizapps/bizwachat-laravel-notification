<?php

namespace BizwaChat\LaravelNotification\Messages;

class ContactData
{
    protected array $attributes = [];

    public static function make(): self
    {
        return new self();
    }

    public function firstname(string $firstname): self
    {
        $this->attributes['firstname'] = $firstname;

        return $this;
    }

    public function lastname(string $lastname): self
    {
        $this->attributes['lastname'] = $lastname;

        return $this;
    }

    public function email(string $email): self
    {
        $this->attributes['email'] = $email;

        return $this;
    }

    public function country(string $country): self
    {
        $this->attributes['country'] = $country;

        return $this;
    }

    public function statusId(int $statusId): self
    {
        $this->attributes['status_id'] = $statusId;

        return $this;
    }

    public function sourceId(int $sourceId): self
    {
        $this->attributes['source_id'] = $sourceId;

        return $this;
    }

    public function assignedId(int $assignedId): self
    {
        $this->attributes['assigned_id'] = $assignedId;

        return $this;
    }

    public function groups(array|string $groups): self
    {
        $this->attributes['groups'] = is_array($groups) ? implode(',', $groups) : $groups;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter($this->attributes, fn (mixed $value): bool => $value !== null && $value !== '');
    }
}