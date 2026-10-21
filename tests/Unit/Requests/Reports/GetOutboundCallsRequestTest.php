<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\GetOutboundCallsRequest;

it('builds the correct OData function URL with defaults', function () {
    $from = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $endpoint = (new GetOutboundCallsRequest($from, $to))->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportOutboundCalls/Pbx.GetOutboundCalls(')
        ->toContain('periodFrom=2024-03-15T00:00:00Z')
        ->toContain('periodTo=2024-03-16T00:00:00Z')
        ->toContain("trunkDns=''")
        ->toContain('callsType=0');
});

it('includes trunk and callsType filters when provided', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $endpoint = (new GetOutboundCallsRequest($from, $to, trunkDns: 'TrunkB', callsType: 2))->resolveEndpoint();

    expect($endpoint)
        ->toContain("trunkDns='TrunkB'")
        ->toContain('callsType=2');
});
