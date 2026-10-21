<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\CallLogFilters;
use CXEngine\ThreeCx\Requests\Reports\DownloadCallLogRequest;

it('builds the CSV download OData URL with default values', function () {
    $from = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new DownloadCallLogRequest($from, $to))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportCallLogData/Pbx.DownloadCallLog(')
        ->toContain("periodFrom=2024-03-15T00:00:00Z")
        ->toContain("periodTo=2024-03-16T00:00:00Z")
        ->toContain("clientTimeZone='UTC'")
        ->toContain("callTimeFilterFrom='00:00:00'")
        ->toContain("callTimeFilterTo='23:59:59'");
});

it('includes custom timezone and filters', function () {
    $from    = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to      = Carbon::parse('2024-03-16 00:00:00', 'UTC');
    $filters = new CallLogFilters(hidePcalls: true);

    $endpoint = (new DownloadCallLogRequest($from, $to, 'Europe/Paris', $filters))->resolveEndpoint();

    expect($endpoint)
        ->toContain("clientTimeZone='Europe/Paris'")
        ->toContain('hidePcalls=true');
});
