<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Recordings\TranscribeRecordingsRequest;
use Saloon\Enums\Method;

it('targets the TranscribeRecordings action endpoint', function () {
    expect((new TranscribeRecordingsRequest([1]))->resolveEndpoint())
        ->toBe('/xapi/v1/Recordings/Pbx.TranscribeRecordings');
});

it('uses POST method', function () {
    expect((new TranscribeRecordingsRequest([1]))->getMethod())->toBe(Method::POST);
});

it('sends the IDs in the JSON body', function () {
    $body = (new ReflectionMethod($r = new TranscribeRecordingsRequest([12345, 67890]), 'defaultBody'))->invoke($r);

    expect($body)->toBe(['ids' => [12345, 67890]]);
});
