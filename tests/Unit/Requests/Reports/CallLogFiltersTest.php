<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Reports\CallLogFilters;

it('has sensible defaults that avoid the empty-string zero-row gotcha', function () {
    $filters = new CallLogFilters();

    expect($filters->callTimeFilterFrom)->toBe('00:00:00')
        ->and($filters->callTimeFilterTo)->toBe('23:59:59')
        ->and($filters->sourceType)->toBe(0)
        ->and($filters->destinationType)->toBe(0)
        ->and($filters->callsType)->toBe(0)
        ->and($filters->callTimeFilterType)->toBe(0)
        ->and($filters->sourceFilter)->toBe('')
        ->and($filters->destinationFilter)->toBe('')
        ->and($filters->hidePcalls)->toBeFalse();
});

it('accepts custom filter values', function () {
    $filters = new CallLogFilters(
        sourceType: 1,
        sourceFilter: '1000',
        destinationType: 2,
        destinationFilter: '2000',
        callsType: 3,
        callTimeFilterType: 1,
        callTimeFilterFrom: '08:00:00',
        callTimeFilterTo: '18:00:00',
        hidePcalls: true,
    );

    expect($filters->sourceType)->toBe(1)
        ->and($filters->sourceFilter)->toBe('1000')
        ->and($filters->destinationType)->toBe(2)
        ->and($filters->destinationFilter)->toBe('2000')
        ->and($filters->callTimeFilterFrom)->toBe('08:00:00')
        ->and($filters->callTimeFilterTo)->toBe('18:00:00')
        ->and($filters->hidePcalls)->toBeTrue();
});
