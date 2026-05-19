<?php

namespace BizwaChat\LaravelNotification\Resources;

use BizwaChat\LaravelNotification\Exceptions\BizwaChatConfigurationException;
use BizwaChat\LaravelNotification\Http\BizwaChatClient;

abstract class AbstractResource
{
    public function __construct(protected BizwaChatClient $client)
    {
    }

    protected function endpoint(?string $subdomain, string $path): string
    {
        return '/api/v3/'.$this->resolveSubdomain($subdomain).'/'.ltrim($path, '/');
    }

    protected function resolveSubdomain(?string $subdomain): string
    {
        $resolved = $subdomain ?: (string) config('bizwachat-notification.subdomain', '');

        if ($resolved === '') {
            throw new BizwaChatConfigurationException('BizwaChat subdomain is not configured.');
        }

        return $resolved;
    }
}