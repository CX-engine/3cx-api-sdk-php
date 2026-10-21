<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Requests\ActiveCalls;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * `GET /ActiveCalls` — live, per-call snapshot of every call currently in
 * progress on the PBX (confirmed live: only `Status: "Talking"` observed so
 * far, i.e. answered calls; ringing/waiting calls have no known equivalent
 * here). One row per call:
 *
 * ```json
 * {
 *   "Id": 33421,
 *   "Caller": "1000 Alice",
 *   "Callee": "230 Bob",
 *   "Status": "Talking",
 *   "LastChangeStatus": "2026-09-18T10:53:24Z",
 *   "EstablishedAt": "2026-09-18T10:53:24Z",
 *   "ServerNow": "2026-09-18T11:02:10.0723574Z"
 * }
 * ```
 *
 * `Caller`/`Callee` are `"{DN} {Name}"` (or `"{DN} {Name} ({external number})"`
 * for a trunk leg) — same leading-DN convention as everywhere else in this
 * package. Neither side is reliably "the agent": for an outbound call the
 * agent is `Caller`, for inbound it's `Callee`. There is also no field
 * attributing a call back to the queue it arrived through, so identifying
 * "is this agent currently busy for queue X" means extracting the leading DN
 * from both `Caller` and `Callee` and checking membership against that
 * queue's own `Agents` list (see `QueuesResource::list()`), not just trusting
 * one side. `EstablishedAt` is answer time, not ring time; use
 * `ServerNow - EstablishedAt` for live talk duration instead of the client's
 * own clock.
 */
class ListActiveCallsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/xapi/v1/ActiveCalls';
    }
}
