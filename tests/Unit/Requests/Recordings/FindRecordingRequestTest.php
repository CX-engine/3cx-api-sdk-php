<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Recordings\FindRecordingRequest;

it('resolves to the Recordings path', function () {
    expect((new FindRecordingRequest(12345))->resolveEndpoint())
        ->toBe('/xapi/v1/Recordings');
});

it('filters by Id eq in the query', function () {
    $query = (new ReflectionMethod($r = new FindRecordingRequest(12345), 'defaultQuery'))->invoke($r);

    expect($query['$filter'])->toBe('Id eq 12345');
});

it('selects all transcription-related fields', function () {
    $query = (new ReflectionMethod($r = new FindRecordingRequest(1), 'defaultQuery'))->invoke($r);

    foreach (['Transcription', 'Summary', 'SentimentScore', 'IsTranscribed', 'TranscriptionResult'] as $field) {
        expect($query['$select'])->toContain($field);
    }
});
