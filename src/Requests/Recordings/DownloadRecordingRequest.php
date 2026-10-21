<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Recordings;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /xapi/v1/Recordings/Pbx.DownloadRecording(recId=...)` — download the
 * audio file for a recording. Returns binary audio content; use `->body()`
 * on the response, not `->json()`.
 */
class DownloadRecordingRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly int $recId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/xapi/v1/Recordings/Pbx.DownloadRecording(recId={$this->recId})";
    }
}
