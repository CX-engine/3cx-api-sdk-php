<?php

declare(strict_types=1);

use CXEngine\ThreeCx\Requests\System\GetTranscribeLanguagesRequest;
use Saloon\Enums\Method;

it('resolves to the GetTranscribeLanguages action', function () {
    expect((new GetTranscribeLanguagesRequest())->resolveEndpoint())
        ->toBe('/xapi/v1/VoicemailSettings/Pbx.GetTranscribeLanguages()');
});

it('uses GET method', function () {
    expect((new GetTranscribeLanguagesRequest())->getMethod())->toBe(Method::GET);
});
