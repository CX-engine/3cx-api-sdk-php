<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Resources;

use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\System\GetAISettingsRequest;
use CXEngine\ThreeCx\Requests\System\GetTranscribeLanguagesRequest;
use CXEngine\ThreeCx\Requests\System\SystemStatusRequest;

class SystemResource extends Resource
{
    /**
     * PBX health check — Version, CallsActive, ExtensionsRegistered, etc.
     */
    public function status(): Response
    {
        return $this->connector->send(new SystemStatusRequest());
    }

    /**
     * Languages supported for call transcription by the configured AI provider.
     */
    public function transcribeLanguages(): Response
    {
        return $this->connector->send(new GetTranscribeLanguagesRequest());
    }

    /**
     * AI provider configuration (provider type, enabled features, etc.).
     */
    public function aiSettings(): Response
    {
        return $this->connector->send(new GetAISettingsRequest());
    }
}
