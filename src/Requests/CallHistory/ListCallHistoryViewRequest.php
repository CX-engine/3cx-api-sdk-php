<?php

namespace CXEngine\ThreeCx\Requests\CallHistory;

use Carbon\CarbonInterface;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * `GET /CallHistoryView` — raw OData entity, one row per call segment/leg.
 *
 * Always filter by date range: an unfiltered/wide pull times out (504) on real
 * data volumes — this is documented 3CX behavior. Prefer
 * {@see \CXEngine\ThreeCx\Requests\Reports\GetCallLogDataRequest} where possible:
 * on lacostedbe.on3cx.fr this entity returned an empty set (`@odata.count: 0`)
 * even unfiltered, while `ReportCallLogData` returned thousands of real rows for
 * the same period — this view may not be populated/enabled on every host.
 */
class ListCallHistoryViewRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly CarbonInterface $from,
        protected readonly CarbonInterface $to,
        protected readonly int $top = 200,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/CallHistoryView';
    }

    protected function defaultQuery(): array
    {
        return [
            '$filter' => sprintf(
                'SegmentStartTime ge %s and SegmentStartTime lt %s',
                $this->from->clone()->utc()->format('Y-m-d\TH:i:s\Z'),
                $this->to->clone()->utc()->format('Y-m-d\TH:i:s\Z'),
            ),
            '$orderby' => 'SegmentStartTime desc',
            '$top' => $this->top,
        ];
    }
}
