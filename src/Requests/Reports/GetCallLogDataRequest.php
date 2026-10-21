<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportCallLogData/Pbx.GetCallLogData(...)` — per-segment call log, JSON.
 *
 * This is what 3CX's own web Reports page uses, and proved more reliable than the
 * raw `CallHistoryView` entity (the latter returned empty/`0`-count results on some
 * hosts regardless of filters).
 *
 * `sourceType`/`destinationType`/`callsType`/`callTimeFilterType` are plain integers
 * with no enum documented anywhere in the 3CX OpenAPI spec; `0` ("no filter") is
 * assumed for all of them based on live testing.
 */
class GetCallLogDataRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly CallLogFilters $filters = new CallLogFilters(),
    ) {}

    public function resolveEndpoint(): string
    {
        $f = $this->filters;

        $params = implode(',', [
            'periodFrom=' . $this->formatDateTime($this->periodFrom),
            'periodTo=' . $this->formatDateTime($this->periodTo),
            'sourceType=' . $f->sourceType,
            'sourceFilter=' . $this->odataString($f->sourceFilter),
            'destinationType=' . $f->destinationType,
            'destinationFilter=' . $this->odataString($f->destinationFilter),
            'callsType=' . $f->callsType,
            'callTimeFilterType=' . $f->callTimeFilterType,
            'callTimeFilterFrom=' . $this->odataString($f->callTimeFilterFrom),
            'callTimeFilterTo=' . $this->odataString($f->callTimeFilterTo),
            'hidePcalls=' . ($f->hidePcalls ? 'true' : 'false'),
        ]);

        return "/xapi/v1/ReportCallLogData/Pbx.GetCallLogData({$params})";
    }
}
