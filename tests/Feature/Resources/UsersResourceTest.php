<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use CXEngine\ThreeCx\Requests\Users\ListUsersRequest;
use CXEngine\ThreeCx\ThreeCxConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->connector = new ThreeCxConnector('pbx.example.com', 'client-id', 'client-secret');

    $this->mock = new MockClient([
        TokenRequest::class     => MockResponse::make(['access_token' => 'tok', 'expires_in' => 3600]),
        ListUsersRequest::class => MockResponse::make(['value' => [
            ['Number' => '100', 'CurrentProfileName' => 'Available', 'QueueStatus' => 'LoggedIn', 'IsRegistered' => true],
        ]]),
    ]);

    $this->connector->withMockClient($this->mock);
});

it('list dispatches ListUsersRequest with the given $select', function () {
    $response = $this->connector->users()->list(['Number', 'QueueStatus']);

    expect($response->json('value.0.Number'))->toBe('100');
    $this->mock->assertSent(fn ($request) => $request instanceof ListUsersRequest
        && $request->query()->get('$select') === 'Number,QueueStatus');
});

it('connector exposes a users() resource factory', function () {
    expect($this->connector->users())->toBeInstanceOf(\CXEngine\ThreeCx\Resources\UsersResource::class);
});

it('forgetAccessToken makes the next call request a new token', function () {
    $this->connector->getAccessToken();
    $this->connector->forgetAccessToken();
    $this->connector->getAccessToken();

    $this->mock->assertSentCount(2, TokenRequest::class);
});
