<?php

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportDetailedQueueStatistics/Pbx.GetDetailedQueueStatisticsData(...)`
 *
 * The cleanest source for "answered vs. total calls per queue": **one row per
 * queue** with `CallsCount` (total received), `AnsweredCount`, `RingTime`/
 * `AvgRingTime`, `TalkTime`/`AvgTalkTime`, `CallbacksCount`. Not-answered count is
 * `CallsCount - AnsweredCount` (3CX doesn't return it as its own field).
 *
 * Cross-checked live against lacostedbe.on3cx.fr for one day: `CallsCount`/
 * `AnsweredCount` here matched `QueueReceivedCount`/`QueueAnsweredCount` from
 * {@see GetQueuePerformanceOverviewDataRequest} exactly (queue 80300: 71/37 both
 * routes), and `AnsweredCount` summed across all queues (402) matched the count of
 * `CallType=Queue` segments with `Status="Waiting"` in the raw
 * `ReportCallLogData` output for the same day — i.e. this is a reliable,
 * pre-aggregated alternative to reconstructing answered/unanswered from raw CDR
 * segments, which is NOT straightforward: a queue segment's own `Answered` field
 * is `true` for every queue leg regardless of outcome (it means "this segment
 * handed off", not "a human picked up") and its `Status` is only ever
 * `Unanswered` or `Waiting` — never `Answered` — so the real answered/not-answered
 * signal is a segment or two downstream (`CallType=Extension`) in the same
 * `MainCallHistoryId` chain and there's no cheap way to determine which queue such
 * an extension pickup was "for" when a call fans out to multiple destinations.
 *
 * Same `waitInterval=''`-returns-nothing gotcha as
 * {@see GetQueuePerformanceOverviewDataRequest} — defaults to `'00:00:00'` here.
 */
class GetDetailedQueueStatisticsDataRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $startDt,
        protected readonly CarbonInterface $endDt,
        protected readonly string $queueDnStr = '',
        protected readonly string $waitInterval = '00:00:00',
    ) {
    }

    public function resolveEndpoint(): string
    {
        $params = implode(',', [
            'queueDnStr='.$this->odataString($this->queueDnStr),
            'startDt='.$this->formatDateTime($this->startDt),
            'endDt='.$this->formatDateTime($this->endDt),
            'waitInterval='.$this->odataString($this->waitInterval),
        ]);

        return "/xapi/v1/ReportDetailedQueueStatistics/Pbx.GetDetailedQueueStatisticsData({$params})";
    }
}
