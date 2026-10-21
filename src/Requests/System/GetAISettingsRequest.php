<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\System;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /xapi/v1/AISettings` — returns the AI provider configuration for this
 * PBX (provider type, enabled features, etc.).
 */
class GetAISettingsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/AISettings';
    }
}
