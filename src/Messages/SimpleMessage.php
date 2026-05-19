<?php

namespace BizwaChat\LaravelNotification\Messages;

use BizwaChat\LaravelNotification\BizwaChatManager;
use BizwaChat\LaravelNotification\Support\ApiResponse;

class SimpleMessage extends AbstractMessage
{
    public function __construct(protected string $messageBody)
    {
    }

    public static function make(string $messageBody): self
    {
        return new self($messageBody);
    }

    public function body(string $messageBody): self
    {
        $this->messageBody = $messageBody;

        return $this;
    }

    public function payload(array $route): array
    {
        return $this->mergeBasePayload([
            'message_body' => $this->messageBody,
        ], $route);
    }

    public function send(BizwaChatManager $manager, array $route): ApiResponse
    {
        return $manager->messages()->sendSimple($this->payload($route), $this->subdomain($route));
    }
}