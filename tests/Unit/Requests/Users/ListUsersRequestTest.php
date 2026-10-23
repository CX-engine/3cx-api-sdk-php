<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Users\ListUsersRequest;

it('resolves to the Users OData entity path', function () {
    expect((new ListUsersRequest())->resolveEndpoint())->toBe('/xapi/v1/Users');
});

it('selects the presence fields by default', function () {
    $request = new ListUsersRequest();
    $query   = (new ReflectionMethod($request, 'defaultQuery'))->invoke($request);

    expect($query['$select'])->toBe('Id,Number,FirstName,LastName,CurrentProfileName,QueueStatus,IsRegistered');
});

it('accepts a custom $select and omits it when empty', function () {
    $custom = new ListUsersRequest(['Number', 'CurrentProfileName']);
    $none   = new ListUsersRequest([]);

    expect((new ReflectionMethod($custom, 'defaultQuery'))->invoke($custom))->toBe(['$select' => 'Number,CurrentProfileName'])
        ->and((new ReflectionMethod($none, 'defaultQuery'))->invoke($none))->toBe([]);
});
