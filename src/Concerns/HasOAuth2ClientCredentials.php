<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Concerns;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\PendingRequest;
use CXEngine\ThreeCx\Exceptions\AuthenticationException;
use CXEngine\ThreeCx\Requests\Auth\TokenRequest;

trait HasOAuth2ClientCredentials
{
    protected ?string $accessToken = null;

    protected ?int $accessTokenExpiresAt = null;

    protected function authenticatePendingRequest(PendingRequest $pendingRequest): void
    {
        if ($pendingRequest->getRequest() instanceof TokenRequest) {
            return;
        }

        if ($pendingRequest->hasMockClient()) {
            return;
        }

        $pendingRequest->authenticate(new TokenAuthenticator($this->getAccessToken()));
    }

    public function getAccessToken(): string
    {
        if ($this->accessToken === null || $this->accessTokenExpiresAt <= time()) {
            $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    protected function refreshAccessToken(): void
    {
        $response = $this->send(new TokenRequest($this->clientId, $this->clientSecret));

        if ($response->failed()) {
            throw new AuthenticationException(
                "Failed to authenticate with 3CX XAPI on {$this->hostName} ({$response->status()}). " .
                'Check client_id/client_secret, and that the API client\'s Department (Admin Console > ' .
                'Integrations > API) is the tenant\'s own operating department with the System Owner role.'
            );
        }

        $body = $response->json();

        $this->accessToken = $body['access_token'];
        // Renew a little early so a request started right at the edge of expiry doesn't 401 mid-flight.
        $this->accessTokenExpiresAt = time() + ($body['expires_in'] ?? 3600) - 30;
    }
}
