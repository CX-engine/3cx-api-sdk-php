<?php

declare(strict_types=1);

use Carbon\Carbon;
use CXEngine\ThreeCx\Requests\Auth\TokenRequest;
use CXEngine\ThreeCx\Requests\Recordings\DownloadRecordingRequest;
use CXEngine\ThreeCx\Requests\Recordings\FindRecordingRequest;
use CXEngine\ThreeCx\Requests\Recordings\ListRecordingsRequest;
use CXEngine\ThreeCx\Requests\Recordings\TranscribeRecordingsRequest;
use CXEngine\ThreeCx\ThreeCxConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->connector = new ThreeCxConnector('pbx.example.com', 'client-id', 'client-secret');

    $this->mock = new MockClient([
        TokenRequest::class              => MockResponse::make(['access_token' => 'tok', 'expires_in' => 3600]),
        ListRecordingsRequest::class     => MockResponse::make(['value' => []]),
        FindRecordingRequest::class      => MockResponse::make(['value' => []]),
        DownloadRecordingRequest::class  => MockResponse::make('audio-bytes', 200, ['Content-Type' => 'audio/wav']),
        TranscribeRecordingsRequest::class => MockResponse::make([], 200),
    ]);

    $this->connector->withMockClient($this->mock);
    $this->connector->getAccessToken();
});

it('list dispatches ListRecordingsRequest', function () {
    $from = Carbon::parse('2024-03-15', 'UTC');
    $to   = Carbon::parse('2024-03-16', 'UTC');

    $response = $this->connector->recordings()->list($from, $to);

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(ListRecordingsRequest::class);
});

it('find dispatches FindRecordingRequest', function () {
    $this->connector->recordings()->find(12345);

    $this->mock->assertSent(FindRecordingRequest::class);
});

it('download dispatches DownloadRecordingRequest', function () {
    $response = $this->connector->recordings()->download(12345);

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(DownloadRecordingRequest::class);
});

it('transcribe dispatches TranscribeRecordingsRequest', function () {
    $response = $this->connector->recordings()->transcribe([12345, 67890]);

    expect($response->successful())->toBeTrue();
    $this->mock->assertSent(TranscribeRecordingsRequest::class);
});

it('connector exposes a recordings() resource factory', function () {
    expect($this->connector->recordings())
        ->toBeInstanceOf(\CXEngine\ThreeCx\Resources\RecordingsResource::class);
});
