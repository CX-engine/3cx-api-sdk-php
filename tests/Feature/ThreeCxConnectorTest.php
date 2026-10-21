<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Exceptions\AuthenticationException;
use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use CXEngine\ThreeCx\Requests\Reports\GetCallLogDataRequest;
use CXEngine\ThreeCx\Requests\System\SystemStatusRequest;
use CXEngine\ThreeCx\ThreeCxConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function makeConnector(): ThreeCxConnector
{
    return new ThreeCxConnector('pbx.example.com', 'client-id', 'client-secret');
}

it('resolves the base URL from the hostname', function () {
    expect(makeConnector()->resolveBaseUrl())->toBe('https://pbx.example.com');
});

it('exposes resource factories', function () {
    $connector = makeConnector();

    expect($connector->reports())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\ReportsResource::class)
        ->and($connector->callHistory())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\CallHistoryResource::class)
        ->and($connector->queues())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\QueuesResource::class)
        ->and($connector->system())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\SystemResource::class)
        ->and($connector->activeCalls())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\ActiveCallsResource::class);
});

it('fetches and caches an access token on first use', function () {
    $connector = makeConnector();
    $mock = new MockClient([
        TokenRequest::class => MockResponse::make(['access_token' => 'tok-abc', 'expires_in' => 3600]),
    ]);
    $connector->withMockClient($mock);

    $token = $connector->getAccessToken();

    expect($token)->toBe('tok-abc');
    // Second call must return the same token without hitting the mock again.
    expect($connector->getAccessToken())->toBe('tok-abc');
    $mock->assertSentCount(1);
});

it('re-authenticates when the cached token has expired', function () {
    $connector = makeConnector();
    $mock = new MockClient([
        TokenRequest::class => MockResponse::make(['access_token' => 'tok-new', 'expires_in' => 3600]),
    ]);
    $connector->withMockClient($mock);

    // Simulate an already-expired token.
    $reflAccessToken        = new ReflectionProperty($connector, 'accessToken');
    $reflAccessTokenExpiry  = new ReflectionProperty($connector, 'accessTokenExpiresAt');
    $reflAccessToken->setValue($connector, 'tok-old');
    $reflAccessTokenExpiry->setValue($connector, time() - 1);

    $token = $connector->getAccessToken();

    expect($token)->toBe('tok-new');
    $mock->assertSentCount(1);
});

it('throws AuthenticationException when the token request fails', function () {
    $connector = makeConnector();
    $mock = new MockClient([
        TokenRequest::class => MockResponse::make([], 401),
    ]);
    $connector->withMockClient($mock);

    expect(fn () => $connector->getAccessToken())->toThrow(AuthenticationException::class);
});

it('sends requests with an Accept: application/json header', function () {
    $connector = makeConnector();
    $mock = new MockClient([
        TokenRequest::class     => MockResponse::make(['access_token' => 'tok', 'expires_in' => 3600]),
        SystemStatusRequest::class => MockResponse::make(['value' => []]),
    ]);
    $connector->withMockClient($mock);

    $connector->getAccessToken(); // prime the cache
    $connector->system()->status();

    $mock->assertSent(SystemStatusRequest::class, function ($request) {
        return $request->headers()->get('Accept') === 'application/json';
    });
});
