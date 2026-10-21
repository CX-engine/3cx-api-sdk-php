<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Resources;

use Carbon\CarbonInterface;
use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\Reports as Endpoints;
use CXEngine\ThreeCx\Requests\Reports\CallLogFilters;
use CXEngine\ThreeCx\Requests\Reports\GetInboundCallsRequest;
use CXEngine\ThreeCx\Requests\Reports\GetOutboundCallsRequest;

/**
 * Wraps the `Report*` family of XAPI OData functions used for scheduled exports.
 */
class ReportsResource extends Resource
{
    /**
     * Full call log (JSON) for a period. See {@see Endpoints\GetCallLogDataRequest}
     * for the `callTimeFilterFrom`/`callTimeFilterTo` gotcha this method defaults around.
     */
    public function callLog(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        CallLogFilters $filters = new CallLogFilters(),
    ): Response {
        return $this->connector->send(new Endpoints\GetCallLogDataRequest($periodFrom, $periodTo, $filters));
    }

    /**
     * Convenience wrapper: full previous-day call log.
     */
    public function callLogForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->callLog($from, $to);
    }

    /**
     * CSV variant of {@see callLog()} — same filters, `Content-Type: text/csv` body.
     */
    public function downloadCallLog(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        string $clientTimeZone = 'UTC',
        CallLogFilters $filters = new CallLogFilters(),
    ): Response {
        return $this->connector->send(new Endpoints\DownloadCallLogRequest($periodFrom, $periodTo, $clientTimeZone, $filters));
    }

    /**
     * Convenience wrapper: CSV variant of {@see callLogForDate()}.
     */
    public function downloadCallLogForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->downloadCallLog($from, $to, $timezone);
    }

    /**
     * Per-agent, per-queue login/logout sessions for a period — who was logged
     * into which queue, when, and for how long, including talk-time intervals.
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
     * The cleanest source for "answered vs. total calls per queue" — one row per queue:
     * `CallsCount`, `AnsweredCount`, `RingTime`/`AvgRingTime`, `TalkTime`/`AvgTalkTime`,
     * `CallbacksCount`. Prefer this over reconstructing counts from {@see callLog()}'s
     * raw segments — see {@see Endpoints\GetDetailedQueueStatisticsDataRequest} for why
     * that's unreliable.
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
     * Per queue, per agent: `QueueReceivedCount`/`QueueAnsweredCount` (repeated per agent row)
     * plus that agent's own `ExtensionAnsweredCount`/`ExtensionDroppedCount`/`TalkTime`.
     * Use {@see detailedQueueStatistics()} instead if you only need queue-level totals.
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

    /**
     * Inbound call report for a period. One row per call with trunk and duration info.
     */
    public function inboundCalls(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        string $trunkDns = '',
        int $callsType = 0,
    ): Response {
        return $this->connector->send(new GetInboundCallsRequest($periodFrom, $periodTo, $trunkDns, $callsType));
    }

    /**
     * Convenience wrapper: previous-day inbound call report.
     */
    public function inboundCallsForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->inboundCalls($from, $to);
    }

    /**
     * Outbound call report for a period. One row per call with trunk and duration info.
     */
    public function outboundCalls(
        CarbonInterface $periodFrom,
        CarbonInterface $periodTo,
        string $trunkDns = '',
        int $callsType = 0,
    ): Response {
        return $this->connector->send(new GetOutboundCallsRequest($periodFrom, $periodTo, $trunkDns, $callsType));
    }

    /**
     * Convenience wrapper: previous-day outbound call report.
     */
    public function outboundCallsForDate(CarbonInterface $date, string $timezone = 'UTC'): Response
    {
        $from = $date->clone()->setTimezone($timezone)->startOfDay();
        $to = $from->clone()->addDay();

        return $this->outboundCalls($from, $to);
    }
}
