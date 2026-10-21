<?php

namespace CXEngine\ThreeCx\Requests\System;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /SystemStatus` — singleton, no envelope. Useful as a connectivity/auth
 * health check (also confirms the API client's Department/Role are correct,
 * since a misconfigured client 403s here just like everywhere else).
 */
class SystemStatusRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/SystemStatus';
    }
}
