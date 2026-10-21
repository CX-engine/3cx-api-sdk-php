<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\GetQueuePerformanceOverviewDataRequest;

it('builds the correct OData function URL with default wait interval', function () {
    $from = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new GetQueuePerformanceOverviewDataRequest($from, $to))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportQueuePerformanceOverview/Pbx.GetQueuePerformanceOverviewData(')
        ->toContain("periodFrom=2024-03-15T00:00:00Z")
        ->toContain("periodTo=2024-03-16T00:00:00Z")
        ->toContain("queueDns=''")
        // Default must be '00:00:00', not '' — empty string silently returns zero rows.
        ->toContain("waitInterval='00:00:00'");
});

it('filters by queue DNS when provided', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $endpoint = (new GetQueuePerformanceOverviewDataRequest($from, $to, queueDns: '80300,80301'))->resolveEndpoint();

    expect($endpoint)->toContain("queueDns='80300,80301'");
});
