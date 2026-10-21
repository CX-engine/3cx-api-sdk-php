<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportCallLogData/Pbx.DownloadCallLog(...)` — same filters as
 * {@see GetCallLogDataRequest} plus `clientTimeZone`, but returns a CSV file
 * (`Content-Type: text/csv`) with a trailing `Totals,<count>,...` row instead of
 * JSON. Use `->body()` (not `->json()`) on the response.
 */
class DownloadCallLogRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly string $clientTimeZone = 'UTC',
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
            'clientTimeZone=' . $this->odataString($this->clientTimeZone),
        ]);

        return "/xapi/v1/ReportCallLogData/Pbx.DownloadCallLog({$params})";
    }
}
