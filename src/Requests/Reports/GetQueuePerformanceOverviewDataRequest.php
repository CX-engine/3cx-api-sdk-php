<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportQueuePerformanceOverview/Pbx.GetQueuePerformanceOverviewData(...)`
 *
 * Per queue, broken down **per agent**: `QueueReceivedCount`/`QueueAnsweredCount`
 * (queue-level totals, repeated on every agent row for that queue) plus
 * `ExtensionAnsweredCount`/`ExtensionDroppedCount`/`TalkTime` (that agent's own
 * contribution). A queue with no traffic in the period only has
 * `QueueDn`/`QueueDisplayName`/`ExtensionDn`/`ExtensionDisplayName`/`SortOrder` —
 * the count/time fields are simply absent, not zero.
 *
 * For a single "answered vs. total per queue" number without the per-agent
 * breakdown, prefer {@see GetDetailedQueueStatisticsDataRequest} — one row per
 * queue, no fan-out.
 *
 * Gotcha (same class of bug as
 * {@see GetCallLogDataRequest}'s `callTimeFilterFrom`/`callTimeFilterTo`):
 * `waitInterval=''` silently returns **zero rows** regardless of the period or
 * `queueDns`. A real duration literal (`'00:00:00'` for "no minimum wait
 * threshold") returns real data.
 */
class GetQueuePerformanceOverviewDataRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly string $queueDns = '',
        protected readonly string $waitInterval = '00:00:00',
    ) {
    }

    public function resolveEndpoint(): string
    {
        $params = implode(',', [
            'periodFrom='.$this->formatDateTime($this->periodFrom),
            'periodTo='.$this->formatDateTime($this->periodTo),
            'queueDns='.$this->odataString($this->queueDns),
            'waitInterval='.$this->odataString($this->waitInterval),
        ]);

        return "/xapi/v1/ReportQueuePerformanceOverview/Pbx.GetQueuePerformanceOverviewData({$params})";
    }
}
