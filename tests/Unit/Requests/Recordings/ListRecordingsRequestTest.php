<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Recordings\ListRecordingsRequest;

it('resolves to the Recordings OData entity path', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    expect((new ListRecordingsRequest($from, $to))->resolveEndpoint())
        ->toBe('/xapi/v1/Recordings');
});

it('builds a date range filter in the OData query', function () {
    $from    = Carbon::parse('2024-03-15 00:00:00', 'UTC');
    $to      = Carbon::parse('2024-03-16 00:00:00', 'UTC');
    $request = new ListRecordingsRequest($from, $to);

    $query = (new ReflectionMethod($request, 'defaultQuery'))->invoke($request);

    expect($query['$filter'])
        ->toContain('StartTime ge 2024-03-15T00:00:00Z')
        ->toContain('StartTime lt 2024-03-16T00:00:00Z');

    expect($query['$top'])->toBe(200);
    expect($query['$orderby'])->toBe('StartTime desc');
    expect($query)->not->toHaveKey('$select');
});

it('adds a caller number contains() filter when provided', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(
        $r = new ListRecordingsRequest($from, $to, callerNumber: '0612345678'),
        'defaultQuery'
    ))->invoke($r);

    expect($query['$filter'])->toContain("contains(FromCallerNumber,'0612345678')");
    expect($query['$filter'])->not->toContain('ToCallerNumber');
});

it('adds a callee number contains() filter when provided', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(
        $r = new ListRecordingsRequest($from, $to, calleeNumber: '101'),
        'defaultQuery'
    ))->invoke($r);

    expect($query['$filter'])->toContain("contains(ToCallerNumber,'101')");
});

it('adds both caller and callee filters when both provided', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(
        $r = new ListRecordingsRequest($from, $to, callerNumber: '0600000000', calleeNumber: '101'),
        'defaultQuery'
    ))->invoke($r);

    expect($query['$filter'])
        ->toContain("contains(FromCallerNumber,'0600000000')")
        ->toContain("contains(ToCallerNumber,'101')");
});

it('includes a $select clause when select fields are provided', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(
        $r = new ListRecordingsRequest($from, $to, select: ['Id', 'Transcription']),
        'defaultQuery'
    ))->invoke($r);

    expect($query['$select'])->toBe('Id,Transcription');
});

it('respects a custom $top limit', function () {
    $from  = Carbon::parse('2024-03-15', 'UTC');
    $to    = Carbon::parse('2024-03-16', 'UTC');
    $query = (new ReflectionMethod(
        $r = new ListRecordingsRequest($from, $to, top: 50),
        'defaultQuery'
    ))->invoke($r);

    expect($query['$top'])->toBe(50);
});
