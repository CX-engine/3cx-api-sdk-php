<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\System\GetAISettingsRequest;
use Saloon\Enums\Method;

it('resolves to the AISettings entity path', function () {
    expect((new GetAISettingsRequest())->resolveEndpoint())
        ->toBe('/xapi/v1/AISettings');
});

it('uses GET method', function () {
    expect((new GetAISettingsRequest())->getMethod())->toBe(Method::GET);
});
