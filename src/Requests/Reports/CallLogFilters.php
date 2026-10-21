<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Reports;

/**
 * Optional filter parameters shared by GetCallLogDataRequest and DownloadCallLogRequest.
 *
 * All parameters default to "no filter" values. The `callTimeFilterFrom`/`callTimeFilterTo`
 * defaults span the full day — leaving them as empty strings causes the XAPI endpoint to
 * silently return zero rows regardless of the date range.
 */
final class CallLogFilters
{
    public function __construct(
        public readonly int $sourceType = 0,
        public readonly string $sourceFilter = '',
        public readonly int $destinationType = 0,
        public readonly string $destinationFilter = '',
        public readonly int $callsType = 0,
        public readonly int $callTimeFilterType = 0,
        public readonly string $callTimeFilterFrom = '00:00:00',
        public readonly string $callTimeFilterTo = '23:59:59',
        public readonly bool $hidePcalls = false,
    ) {}
}
