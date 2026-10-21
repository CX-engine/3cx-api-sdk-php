<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Recordings;

use Carbon\CarbonInterface;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /xapi/v1/Recordings` — list call recordings with optional filters.
 *
 * Transcription, Summary, and SentimentScore are fields on the Recording entity
 * itself; there is no separate transcription endpoint. Check `IsTranscribed`
 * before reading those fields — call `TranscribeRecordingsRequest` first if false.
 *
 * Uses standard OData query params (`$filter`, `$select`, `$orderby`, `$top`),
 * not the path-style function syntax used by Report* endpoints.
 *
 * Note: `contains()` on phone numbers may be rejected on some PBX builds.
 * If so, filter by date only and post-filter in PHP.
 */
class ListRecordingsRequest extends Request
{
    protected Method $method = Method::GET;

    /** @param string[] $select */
    public function __construct(
        protected readonly CarbonInterface $from,
        protected readonly CarbonInterface $to,
        protected readonly ?string $callerNumber = null,
        protected readonly ?string $calleeNumber = null,
        protected readonly array $select = [],
        protected readonly int $top = 200,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/Recordings';
    }

    protected function defaultQuery(): array
    {
        $fromUtc = $this->from->clone()->utc()->format('Y-m-d\TH:i:s\Z');
        $toUtc   = $this->to->clone()->utc()->format('Y-m-d\TH:i:s\Z');

        $filter = "StartTime ge {$fromUtc} and StartTime lt {$toUtc}";

        if ($this->callerNumber !== null) {
            $filter .= " and contains(FromCallerNumber,'{$this->callerNumber}')";
        }

        if ($this->calleeNumber !== null) {
            $filter .= " and contains(ToCallerNumber,'{$this->calleeNumber}')";
        }

        $query = [
            '$filter'  => $filter,
            '$orderby' => 'StartTime desc',
            '$top'     => $this->top,
        ];

        if ($this->select !== []) {
            $query['$select'] = implode(',', $this->select);
        }

        return $query;
    }
}
