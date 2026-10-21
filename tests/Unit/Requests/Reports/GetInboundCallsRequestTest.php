<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\GetInboundCallsRequest;

it('builds the correct OData function URL with defaults', function () {
    $from = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new GetInboundCallsRequest($from, $to))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportInboundCalls/Pbx.GetInboundCalls(')
        ->toContain('periodFrom=2024-03-15T00:00:00Z')
        ->toContain('periodTo=2024-03-16T00:00:00Z')
        ->toContain("trunkDns=''")
        ->toContain('callsType=0');
});

it('includes trunk and callsType filters when provided', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $endpoint = (new GetInboundCallsRequest($from, $to, trunkDns: 'TrunkA', callsType: 1))->resolveEndpoint();

    expect($endpoint)
        ->toContain("trunkDns='TrunkA'")
        ->toContain('callsType=1');
});
