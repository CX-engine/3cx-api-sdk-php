<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Reports\CallLogFilters;
use CXEngine\ThreeCx\Requests\Reports\GetCallLogDataRequest;

it('builds the OData function URL with default filters', function () {
    $from = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to   = Carbon::parse('2024-03-16 00:00:00', 'UTC');

    $request = new GetCallLogDataRequest($from, $to);
    $endpoint = $request->resolveEndpoint();

    expect($endpoint)
        ->toContain('/xapi/v1/ReportCallLogData/Pbx.GetCallLogData(')
        ->toContain("periodFrom=2024-03-15T00:00:00Z")
        ->toContain("periodTo=2024-03-16T00:00:00Z")
        ->toContain("callTimeFilterFrom='00:00:00'")
        ->toContain("callTimeFilterTo='23:59:59'")
        ->toContain("hidePcalls=false")
        ->toContain("sourceType=0");
});

it('includes custom filter values in the URL', function () {
    $from    = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to      = Carbon::parse('2024-03-16 00:00:00', 'UTC');
    $filters = new CallLogFilters(
        sourceFilter: '1000',
        callTimeFilterFrom: '08:00:00',
        callTimeFilterTo: '18:00:00',
        hidePcalls: true,
    );

    $request  = new GetCallLogDataRequest($from, $to, $filters);
    $endpoint = $request->resolveEndpoint();

    expect($endpoint)
        ->toContain("sourceFilter='1000'")
        ->toContain("callTimeFilterFrom='08:00:00'")
        ->toContain("callTimeFilterTo='18:00:00'")
        ->toContain("hidePcalls=true");
});

it('converts non-UTC dates to UTC in the endpoint', function () {
    $from = Carbon::parse('2024-03-15 08:00:00', 'Europe/Paris');  // UTC+1 → 07:00 UTC
    $to   = Carbon::parse('2024-03-16 08:00:00', 'Europe/Paris');

    $endpoint = (new GetCallLogDataRequest($from, $to))->resolveEndpoint();

    expect($endpoint)
        ->toContain('periodFrom=2024-03-15T07:00:00Z')
        ->toContain('periodTo=2024-03-16T07:00:00Z');
});

it('escapes single quotes in string filters', function () {
    $from    = Carbon::parse('2024-03-15', 'UTC');
    $to      = Carbon::parse('2024-03-16', 'UTC');
    $filters = new CallLogFilters(sourceFilter: "O'Brien");

    $endpoint = (new GetCallLogDataRequest($from, $to, $filters))->resolveEndpoint();

    expect($endpoint)->toContain("sourceFilter='O''Brien'");
});
