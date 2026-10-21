<?php

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportCallLogData/Pbx.GetCallLogData(...)` — per-segment call log, JSON.
 *
 * This is what 3CX's own web Reports page uses, and proved more reliable than the
 * raw `CallHistoryView` entity in testing (the latter returned an empty/`0`-count
 * result on at least one live host regardless of filters).
 *
 * `sourceType`/`destinationType`/`callsType`/`callTimeFilterType` are plain integers
 * with no enum documented anywhere in the 3CX OpenAPI spec; `0` ("no filter") is
 * assumed for all of them based on live testing.
 *
 * Gotcha confirmed live against lacostedbe.on3cx.fr: leaving `callTimeFilterFrom`/
 * `callTimeFilterTo` as empty strings silently returns ZERO rows for any period,
 * regardless of `callTimeFilterType` — this is what made the whole endpoint look
 * broken/empty in earlier testing against other hosts. They must be real
 * `HH:MM:SS` time-of-day literals spanning the full day (`'00:00:00'`..`'23:59:59'`
 * by default here) even when you don't want to filter by time-of-day.
 */
class GetCallLogDataRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly int $sourceType = 0,
        protected readonly string $sourceFilter = '',
        protected readonly int $destinationType = 0,
        protected readonly string $destinationFilter = '',
        protected readonly int $callsType = 0,
        protected readonly int $callTimeFilterType = 0,
        protected readonly string $callTimeFilterFrom = '00:00:00',
        protected readonly string $callTimeFilterTo = '23:59:59',
        protected readonly bool $hidePcalls = false,
    ) {
    }

    public function resolveEndpoint(): string
    {
        $params = implode(',', [
            'periodFrom='.$this->formatDateTime($this->periodFrom),
            'periodTo='.$this->formatDateTime($this->periodTo),
            'sourceType='.$this->sourceType,
            'sourceFilter='.$this->odataString($this->sourceFilter),
            'destinationType='.$this->destinationType,
            'destinationFilter='.$this->odataString($this->destinationFilter),
            'callsType='.$this->callsType,
            'callTimeFilterType='.$this->callTimeFilterType,
            'callTimeFilterFrom='.$this->odataString($this->callTimeFilterFrom),
            'callTimeFilterTo='.$this->odataString($this->callTimeFilterTo),
            'hidePcalls='.($this->hidePcalls ? 'true' : 'false'),
        ]);

        return "/xapi/v1/ReportCallLogData/Pbx.GetCallLogData({$params})";
    }
}
