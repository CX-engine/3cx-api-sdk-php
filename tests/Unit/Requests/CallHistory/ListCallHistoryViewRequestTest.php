<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\CallHistory\ListCallHistoryViewRequest;

it('resolves to the CallHistoryView OData entity path', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    expect((new ListCallHistoryViewRequest($from, $to))->resolveEndpoint())
        ->toBe('/xapi/v1/CallHistoryView');
});

it('builds a date range filter in the OData query string', function () {
    $from    = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to      = Carbon::parse('2024-03-16 00:00:00', 'UTC');
    $request = new ListCallHistoryViewRequest($from, $to);

    // Access via reflection — defaultQuery() is protected.
    $query = (new ReflectionMethod($request, 'defaultQuery'))->invoke($request);

    expect($query['$filter'])
        ->toContain('SegmentStartTime ge 2024-03-15T00:00:00Z')
        ->toContain('SegmentStartTime lt 2024-03-16T00:00:00Z');

    expect($query['$top'])->toBe(200);
    expect($query['$orderby'])->toBe('SegmentStartTime desc');
});

it('respects a custom $top limit', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(new ListCallHistoryViewRequest($from, $to, top: 500), 'defaultQuery'))
        ->invoke(new ListCallHistoryViewRequest($from, $to, top: 500));

    expect($query['$top'])->toBe(500);
});
