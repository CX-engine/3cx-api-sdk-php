<?php

namespace CXEngine\ThreeCx\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /Users` — every user (extension) on the PBX with its *current* state.
 * This is a point-in-time snapshot only: 3CX keeps no history of presence
 * changes, so time-in-status must be derived by polling this route and
 * storing the changes yourself (30 s polling caught every change within one
 * poll in testing). One row per user:
 *
 * ```json
 * {
 *   "Id": 42,
 *   "Number": "40006",
 *   "CurrentProfileName": "Available",
 *   "QueueStatus": "LoggedIn",
 *   "IsRegistered": true
 * }
 * ```
 *
 * - `CurrentProfileName`: the status profile name — "Available", "Away",
 *   "Out of office", "Custom 1", "Custom 2" (the last two are renamable in
 *   the 3CX UI, so map them per host rather than by label).
 * - `QueueStatus`: `LoggedIn`/`LoggedOut` — the user's *global* queue login,
 *   not per queue. Per-queue login lives in `ReportAgentLoginHistory`.
 * - `IsRegistered`: a phone/app is currently registered for this user.
 *
 * Always pass a `$select`: the full entity is large (forwarding rules,
 * phone provisioning, etc.) and slows a frequent poll down for nothing.
 */
class ListUsersRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string[]  $select
     */
    public function __construct(
        protected readonly array $select = ['Id', 'Number', 'FirstName', 'LastName', 'CurrentProfileName', 'QueueStatus', 'IsRegistered'],
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/Users';
    }

    protected function defaultQuery(): array
    {
        return $this->select ? ['$select' => implode(',', $this->select)] : [];
    }
}
