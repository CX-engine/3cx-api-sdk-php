<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\Recordings\DownloadRecordingRequest;
use Saloon\Enums\Method;

it('builds the path-style download endpoint', function () {
    expect((new DownloadRecordingRequest(12345))->resolveEndpoint())
        ->toBe('/xapi/v1/Recordings/Pbx.DownloadRecording(recId=12345)');
});

it('uses GET method', function () {
    expect((new DownloadRecordingRequest(1))->getMethod())->toBe(Method::GET);
});
