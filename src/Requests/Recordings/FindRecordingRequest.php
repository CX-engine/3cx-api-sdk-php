<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Recordings;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /xapi/v1/Recordings?$filter=Id eq {id}` — fetch a single recording by
 * its ID, including all transcription fields. Use `->json('value.0')` on the
 * response to get the row directly.
 *
 * The XAPI does not expose a `/Recordings({id})` singleton route; filtering by
 * `Id eq` is the documented approach.
 */
class FindRecordingRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly int $id,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/Recordings';
    }

    protected function defaultQuery(): array
    {
        return [
            '$filter' => "Id eq {$this->id}",
            '$select' => implode(',', [
                'Id', 'StartTime', 'FromDn', 'FromCallerNumber',
                'ToDn', 'ToCallerNumber', 'IsTranscribed', 'CanBeTranscribed',
                'Transcription', 'Summary', 'SentimentScore', 'TranscriptionResult',
            ]),
        ];
    }
}
