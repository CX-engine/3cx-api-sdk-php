<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Requests\Queues;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /Queues` — configuration only (name/number/polling strategy/agents list).
 * No live "calls waiting" or "agents logged in now" fields exist on this schema;
 * this is reference/lookup data (e.g. to map queue DN -> display name), not a
 * source of live or historical activity.
 */
class ListQueuesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly bool $expandAgents = true,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/Queues';
    }

    protected function defaultQuery(): array
    {
        return $this->expandAgents ? ['$expand' => 'Agents'] : [];
    }
}
