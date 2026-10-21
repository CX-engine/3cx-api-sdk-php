<?php

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportCallLogData/Pbx.DownloadCallLog(...)` — same filters as
 * {@see GetCallLogDataRequest} plus `clientTimeZone`, but returns a CSV file
 * (`Content-Type: text/csv`) with a trailing `Totals,<count>,...` row instead of
 * JSON. Useful when the destination is a flat file rather than something that
 * gets parsed further. Use `->body()` (not `->json()`) on the response.
 */
class DownloadCallLogRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly string $clientTimeZone = 'UTC',
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
            'clientTimeZone='.$this->odataString($this->clientTimeZone),
        ]);

        return "/xapi/v1/ReportCallLogData/Pbx.DownloadCallLog({$params})";
    }
}
