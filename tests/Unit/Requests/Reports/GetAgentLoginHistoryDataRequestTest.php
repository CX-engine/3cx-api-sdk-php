<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\GetAgentLoginHistoryDataRequest;

it('builds the correct OData function URL', function () {
    $start = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $end   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new GetAgentLoginHistoryDataRequest($start, $end))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportAgentLoginHistory/Pbx.GetAgentLoginHistoryData(')
        ->toContain("startDt=2024-03-15T00:00:00Z")
        ->toContain("endDt=2024-03-16T00:00:00Z")
        ->toContain("clientTimeZone='UTC'")
        ->toContain("queueDnStr=''")
        ->toContain("agentDnStr=''");
});

it('includes queue and agent filters when provided', function () {
    $start = Carbon::parse('2024-03-15', 'UTC');
    $end   = Carbon::parse('2024-03-16', 'UTC');

    $endpoint = (new GetAgentLoginHistoryDataRequest(
        startDt: $start,
        endDt: $end,
        queueDnStr: '80300',
        agentDnStr: '40006,40007',
    ))->resolveEndpoint();

    expect($endpoint)
        ->toContain("queueDnStr='80300'")
        ->toContain("agentDnStr='40006,40007'");
});
