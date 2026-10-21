<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use CXEngine\ThreeCx\Concerns\HasOAuth2ClientCredentials;

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
    use HasOAuth2ClientCredentials;

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
