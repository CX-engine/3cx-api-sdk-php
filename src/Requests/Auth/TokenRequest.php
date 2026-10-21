<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Requests\Auth;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Contracts\Body\HasBody;

/**
 * OAuth2 client_credentials token exchange.
 *
 * Relative to the host root (`/connect/token`), NOT `/xapi/v1` — this is the one
 * request in the SDK that does not live under the XAPI base path.
 */
class TokenRequest extends Request implements HasBody
{
    use HasFormBody;

    protected Method $method = Method::POST;

    public function __construct(
        #[\SensitiveParameter]
        protected readonly string $clientId,
        #[\SensitiveParameter]
        protected readonly string $clientSecret,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/connect/token';
    }

    protected function defaultBody(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }
}
