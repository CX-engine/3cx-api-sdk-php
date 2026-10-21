<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\GetDetailedQueueStatisticsDataRequest;

it('builds the correct OData function URL with default wait interval', function () {
    $start = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $end   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new GetDetailedQueueStatisticsDataRequest($start, $end))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportDetailedQueueStatistics/Pbx.GetDetailedQueueStatisticsData(')
        ->toContain("startDt=2024-03-15T00:00:00Z")
        ->toContain("endDt=2024-03-16T00:00:00Z")
        ->toContain("queueDnStr=''")
        // Default must be '00:00:00', not '' — empty string silently returns zero rows.
        ->toContain("waitInterval='00:00:00'");
});

it('filters by queue DN when provided', function () {
    $start = Carbon::parse('2024-03-15', 'UTC');
    $end   = Carbon::parse('2024-03-16', 'UTC');

    $endpoint = (new GetDetailedQueueStatisticsDataRequest($start, $end, queueDnStr: '80300'))->resolveEndpoint();

    expect($endpoint)->toContain("queueDnStr='80300'");
});
