<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\System;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /xapi/v1/VoicemailSettings/Pbx.GetTranscribeLanguages()` — returns the
 * list of languages supported for call transcription by the configured AI provider.
 */
class GetTranscribeLanguagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/VoicemailSettings/Pbx.GetTranscribeLanguages()';
    }
}
