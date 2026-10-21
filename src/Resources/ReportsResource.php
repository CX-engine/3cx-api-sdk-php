<?php

namespace CXEngine\ThreeCx\Resources;

use Carbon\CarbonInterface;
use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\Reports as Endpoints;

/**
 * Wraps the `Report*` family of XAPI OData functions used for scheduled exports.
 */
class ReportsResource extends Resource
{
    /**
     * Full call log (JSON) for a period. See {@see Endpoints\GetCallLogDataRequest}
     * for the `callTimeFilterFrom`/`callTimeFilterTo` gotcha this method defaults
     * around.
     */
    public function callLog(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        int $sourceType = 0,
        string $sourceFilter = '',
        int $destinationType = 0,
        string $destinationFilter = '',
        int $callsType = 0,
        int $callTimeFilterType = 0,
        string $callTimeFilterFrom = '00:00:00',
        string $callTimeFilterTo = '23:59:59',
        bool $hidePcalls = false,
    ): Response {
        return $this->connector->send(new Endpoints\GetCallLogDataRequest(
            $periodFrom,
            $periodTo,
            $sourceType,
            $sourceFilter,
            $destinationType,
            $destinationFilter,
            $callsType,
            $callTimeFilterType,
            $callTimeFilterFrom,
            $callTimeFilterTo,
            $hidePcalls,
        ));
    }

    /**
     * Convenience wrapper: full previous-day call log in the given timezone.
     * This is the route for the "yesterday's data" morning export.
     */
    public function callLogForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->callLog($from, $to);
    }

    /**
     * Convenience wrapper: CSV variant of {@see callLogForDate()} for the given date.
     */
    public function downloadCallLogForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->downloadCallLog($from, $to, $timezone);
    }

    /**
     * CSV variant of {@see callLog()} — same filters, `Content-Type: text/csv` body.
     */
    public function downloadCallLog(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        string $clientTimeZone = 'UTC',
        int $sourceType = 0,
        string $sourceFilter = '',
        int $destinationType = 0,
        string $destinationFilter = '',
        int $callsType = 0,
        int $callTimeFilterType = 0,
        string $callTimeFilterFrom = '00:00:00',
        string $callTimeFilterTo = '23:59:59',
        bool $hidePcalls = false,
    ): Response {
        return $this->connector->send(new Endpoints\DownloadCallLogRequest(
            $periodFrom,
            $periodTo,
            $clientTimeZone,
            $sourceType,
            $sourceFilter,
            $destinationType,
            $destinationFilter,
            $callsType,
            $callTimeFilterType,
            $callTimeFilterFrom,
            $callTimeFilterTo,
            $hidePcalls,
        ));
    }

    /**
     * Per-agent, per-queue login/logout sessions for a period — "who was logged
     * into which queue, when, and for how long", including talk-time intervals.
     */
    public function agentLoginHistory(
        CarbonInterface $startDt,
        CarbonInterface $endDt,
        string $clientTimeZone = 'UTC',
        string $queueDnStr = '',
        string $agentDnStr = '',
    ): Response {
        return $this->connector->send(new Endpoints\GetAgentLoginHistoryDataRequest(
            $startDt,
            $endDt,
            $clientTimeZone,
            $queueDnStr,
            $agentDnStr,
        ));
    }

    /**
     * Convenience wrapper: previous-day agent login/logout history.
     */
    public function agentLoginHistoryForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->agentLoginHistory($from, $to);
    }

    /**
     * The cleanest source for "answered vs. total calls per queue" — one row per
     * queue: `CallsCount`, `AnsweredCount`, `RingTime`/`AvgRingTime`,
     * `TalkTime`/`AvgTalkTime`, `CallbacksCount`. Not-answered = `CallsCount -
     * AnsweredCount`. Prefer this over reconstructing counts from
     * {@see callLog()}'s raw segments — see
     * {@see Endpoints\GetDetailedQueueStatisticsDataRequest} for why that's
     * unreliable.
     */
    public function detailedQueueStatistics(
        CarbonInterface $startDt,
        CarbonInterface $endDt,
        string $queueDnStr = '',
        string $waitInterval = '00:00:00',
    ): Response {
        return $this->connector->send(new Endpoints\GetDetailedQueueStatisticsDataRequest(
            $startDt,
            $endDt,
            $queueDnStr,
            $waitInterval,
        ));
    }

    /**
     * Convenience wrapper: previous-day per-queue call/answered statistics.
     */
    public function detailedQueueStatisticsForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->detailedQueueStatistics($from, $to);
    }

    /**
     * Per queue, per agent: `QueueReceivedCount`/`QueueAnsweredCount` (repeated
     * per agent row) plus that agent's own `ExtensionAnsweredCount`/
     * `ExtensionDroppedCount`/`TalkTime`. Use {@see detailedQueueStatistics()}
     * instead if you only need the queue-level total/answered counts, not the
     * per-agent breakdown.
     */
    public function queuePerformanceOverview(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        string $queueDns = '',
        string $waitInterval = '00:00:00',
    ): Response {
        return $this->connector->send(new Endpoints\GetQueuePerformanceOverviewDataRequest(
            $periodFrom,
            $periodTo,
            $queueDns,
            $waitInterval,
        ));
    }

    /**
     * Convenience wrapper: previous-day per-queue, per-agent performance overview.
     */
    public function queuePerformanceOverviewForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->queuePerformanceOverview($from, $to);
    }
}
