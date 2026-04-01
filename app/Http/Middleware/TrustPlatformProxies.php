<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class TrustPlatformProxies extends Middleware
{
    /**
     * Trust all upstream proxies from managed platforms like Railway/Cloudflare.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Use forwarded headers to recover the original HTTPS scheme and host.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_PREFIX |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
