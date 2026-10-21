<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Recordings;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * `POST /xapi/v1/Recordings/Pbx.TranscribeRecordings` — trigger on-demand
 * transcription for one or more recordings.
 *
 * Requires transcription to be enabled for the user/queue/service on the PBX,
 * and an AI provider configured (3CX AI, OpenAI, Google, etc.).
 *
 * This is the only write (POST) operation in the SDK. After calling this,
 * poll `RecordingsResource::find()` until `IsTranscribed` is `true` before
 * reading `Transcription`, `Summary`, and `SentimentScore`.
 *
 * @param int[] $ids Recording IDs to transcribe.
 */
class TranscribeRecordingsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param int[] $ids */
    public function __construct(
        protected readonly array $ids,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/Recordings/Pbx.TranscribeRecordings';
    }

    protected function defaultBody(): array
    {
        return ['ids' => $this->ids];
    }
}
