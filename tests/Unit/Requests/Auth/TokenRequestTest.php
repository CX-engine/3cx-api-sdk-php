<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use Saloon\Enums\Method;

it('targets the connect/token endpoint', function () {
    $request = new TokenRequest('my-client', 'my-secret');

    expect($request->resolveEndpoint())->toBe('/connect/token');
});

it('uses POST method', function () {
    $request = new TokenRequest('my-client', 'my-secret');

    expect($request->getMethod())->toBe(Method::POST);
});

it('sends client credentials in the form body', function () {
    $request = new TokenRequest('my-client', 'my-secret');

    $body = (new ReflectionMethod($request, 'defaultBody'))->invoke($request);

    expect($body)->toBe([
        'grant_type'    => 'client_credentials',
        'client_id'     => 'my-client',
        'client_secret' => 'my-secret',
    ]);
});
