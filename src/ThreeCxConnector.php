<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Auth\TokenAuthenticator;
use CXEngine\ThreeCx\Exceptions\AuthenticationException;
use CXEngine\ThreeCx\Requests\Auth\TokenRequest;

/**
 * Connector for a single 3CX host's XAPI (`/xapi/v1/...`, OData v4).
 *
 * Auth is OAuth2 client_credentials against `/connect/token` (host root, not
 * `/xapi/v1`). The client must be created in the Admin Console under
 * Integrations > API, with its Department set to the tenant's own operating
 * department (not a reseller/support department) and Role = System Owner —
 * otherwise every XAPI call 403s even with a valid token.
 *
 * Tokens last 1 hour with no refresh token; this connector re-authenticates
 * transparently on first use and whenever the cached token is about to expire.
 */
class ThreeCxConnector extends Connector
{
    protected ?string $accessToken = null;

    protected ?int $accessTokenExpiresAt = null;

    public function __construct(
        protected readonly string $hostName,
        #[\SensitiveParameter]
        protected readonly string $clientId,
        #[\SensitiveParameter]
        protected readonly string $clientSecret,
    ) {}

    public function resolveBaseUrl(): string
    {
        return "https://{$this->hostName}";
    }

    public function boot(PendingRequest $pendingRequest): void
    {
        $this->authenticatePendingRequest($pendingRequest);
    }

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
        if ($this->accessToken === null || $this->accessTokenExpiresAt === null || $this->accessTokenExpiresAt <= time()) {
            $this->refreshAccessToken();
        }

        return $this->accessToken ?? throw new AuthenticationException('Access token is not available after refresh.');
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

        $this->accessToken = (string) $body['access_token'];
        // Renew a little early so a request started right at the edge of expiry doesn't 401 mid-flight.
        $this->accessTokenExpiresAt = time() + (int) ($body['expires_in'] ?? 3600) - 30;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'timeout' => 60,
        ];
    }

    /**
     * Reports family (`Report*` OData functions): call log, agent login history, etc.
     */
    public function reports(): Resources\ReportsResource
    {
        return new Resources\ReportsResource($this);
    }

    /**
     * Raw `CallHistoryView` OData entity (per-segment CDR). Prefer `reports()->callLog()`
     * — `CallHistoryView` returns empty or 500s on date filters on some hosts.
     */
    public function callHistory(): Resources\CallHistoryResource
    {
        return new Resources\CallHistoryResource($this);
    }

    /**
     * Queue configuration (names, agents) — reference/lookup data, not live state.
     */
    public function queues(): Resources\QueuesResource
    {
        return new Resources\QueuesResource($this);
    }

    /**
     * PBX system status (singleton) — useful as a connectivity/health check.
     */
    public function system(): Resources\SystemResource
    {
        return new Resources\SystemResource($this);
    }

    /**
     * Live, per-call snapshot of every call currently in progress.
     */
    public function activeCalls(): Resources\ActiveCallsResource
    {
        return new Resources\ActiveCallsResource($this);
    }
}
