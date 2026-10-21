<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use CXEngine\ThreeCx\Requests\System\GetAISettingsRequest;
use CXEngine\ThreeCx\Requests\System\GetTranscribeLanguagesRequest;
use CXEngine\ThreeCx\ThreeCxConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->connector = new ThreeCxConnector('pbx.example.com', 'client-id', 'client-secret');

    $this->mock = new MockClient([
        TokenRequest::class                  => MockResponse::make(['access_token' => 'tok', 'expires_in' => 3600]),
        GetTranscribeLanguagesRequest::class => MockResponse::make(['value' => []]),
        GetAISettingsRequest::class          => MockResponse::make(['value' => []]),
    ]);

    $this->connector->withMockClient($this->mock);
    $this->connector->getAccessToken();
});

it('transcribeLanguages dispatches GetTranscribeLanguagesRequest', function () {
    $response = $this->connector->system()->transcribeLanguages();

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(GetTranscribeLanguagesRequest::class);
});

it('aiSettings dispatches GetAISettingsRequest', function () {
    $response = $this->connector->system()->aiSettings();

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(GetAISettingsRequest::class);
});

it('connector exposes a system() resource factory', function () {
    expect($this->connector->system())
        ->toBeInstanceOf(\CXEngine\ThreeCx\Resources\SystemResource::class);
});
