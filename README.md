# 3CX XAPI PHP SDK

A PHP client for the 3CX **XAPI** (`/xapi/v1/...`, OData v4 REST). Built with
[Saloon](https://docs.saloon.dev/).

Covers call logs, agent status, queue statistics, live calls, recordings,
transcriptions, and AI-generated summaries. Mostly read-only `GET` requests,
with one write: `recordings()->transcribe()` to trigger on-demand transcription.

## Installation

```
composer require cx-engine/3cx-api-sdk-php
```

Requires PHP `>=8.2`.

## Authentication

3CX XAPI uses OAuth2 `client_credentials`. Create a client in the Admin Console
under **Integrations > API** — the "API key" shown once by the console is the
`client_secret` (paired with the `Client ID` you set).

```php
use CXEngine\ThreeCx\ThreeCxConnector;

$connector = new ThreeCxConnector(
    hostName: 'your-pbx.on3cx.com',
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
);
```

The connector fetches and caches the bearer token transparently (tokens last 1
hour, no refresh token — it re-authenticates as needed). If auth fails, a
`CXEngine\ThreeCx\Exceptions\AuthenticationException` is thrown.

**403 on every call despite valid credentials** almost always means the API
client's **Department** in Admin Console is scoped to a reseller/support
department instead of the tenant's own operating department. Fix: edit the
client, set Department to the tenant's real department (often `DEFAULT`), Role =
**System Owner**, and re-fetch the token (role changes don't apply retroactively
to an already-issued token).

## Usage

Each resource groups related requests and returns a Saloon `Response`
(`->json()`, `->body()`, `->status()`, `->failed()`, ...).

### Call log

```php
use Carbon\Carbon;

$response = $connector->reports()->callLogForDate(Carbon::yesterday('UTC'));
$calls = $response->json('value'); // array of per-segment call log rows
```

Lower-level call with explicit date range:

```php
$response = $connector->reports()->callLog(
    periodFrom: Carbon::yesterday('UTC')->startOfDay(),
    periodTo: Carbon::today('UTC'),
);
```

A CSV variant (`Content-Type: text/csv`, with a trailing `Totals,<count>,...` row)
is available via `$connector->reports()->downloadCallLog(...)` /
`downloadCallLogForDate(...)` — same filters, use `->body()` instead of `->json()`.

> **Gotcha:** the underlying `ReportCallLogData/Pbx.GetCallLogData` XAPI function
> silently returns **zero** rows for any period if `callTimeFilterFrom` /
> `callTimeFilterTo` are left as empty strings — regardless of `callTimeFilterType`.
> This SDK defaults them to `'00:00:00'`/`'23:59:59'` (full day) so "no
> time-of-day filter" actually means no filter. Don't pass empty strings for these
> unless you've confirmed the host behaves differently.

The raw `CallHistoryView` OData entity is also exposed
(`$connector->callHistory()->list($from, $to)`), but prefer `reports()->callLog()`.
On some hosts `CallHistoryView` returns empty results or 500s on date-range filters
while `ReportCallLogData` returns correct data for the same period. `CallHistoryView`
is kept available as a fallback.

### Agent queue login/logout status

```php
$response = $connector->reports()->agentLoginHistoryForDate(Carbon::yesterday('UTC'));
$sessions = $response->json('value');
```

Each row is one login session: `QueueNo`, `AgentNo`, `Agent` (display name),
`loggedInDt`, `LoggedOutDt`, and ISO-8601 durations `LoggedInInterval` /
`TalkingInterval` (this session) plus `LoggedInDayInterval` / `TalkingDayInterval`
(running total for that agent that day). Filter to a specific queue or agent with
`queueDnStr='80300'` / `agentDnStr='40006'` (comma-separated for multiple).

`*Interval` fields are ISO-8601 durations (`PT1H34M26.98S`) with fractional
seconds — parse with `Carbon\CarbonInterval::fromString()`, not PHP's native
`DateInterval` (which rejects the fractional-seconds format).

### Per-queue call stats (answered / not-answered / total)

```php
$response = $connector->reports()->detailedQueueStatisticsForDate(Carbon::yesterday('UTC'));
$queues = $response->json('value');
// [{"QueueDnNumber": "80300", "QueueDn": "80300 Sales Queue",
//   "CallsCount": 71, "AnsweredCount": 37, "RingTime": "PT49M34S", ...}, ...]
```

One row per queue: `CallsCount` (total), `AnsweredCount`, `RingTime`/
`AvgRingTime`, `TalkTime`/`AvgTalkTime`, `CallbacksCount`. Not-answered =
`CallsCount - AnsweredCount` (there's no separate field for it).

> **Why not derive these from `callLog()`?** A call log row is one segment/leg of
> a call, not a finished call. A queue segment's `Answered` field is `true` on
> every leg regardless of outcome (it means "handed off to the next segment"), and
> `Status` on queue legs is only ever `Unanswered`/`Waiting`. The real
> answered/not-answered signal lives in downstream segments of the same call chain.
> `detailedQueueStatistics` does this attribution server-side — use it.

> **Gotcha:** same empty-string issue as `callTimeFilterFrom`/`callTimeFilterTo`
> — this SDK defaults `waitInterval` to `'00:00:00'`.

`reports()->queuePerformanceOverview()` / `queuePerformanceOverviewForDate()`
covers the same ground but fans out **per agent** (`QueueReceivedCount`/
`QueueAnsweredCount` at the queue level, repeated per agent row, plus that agent's
own `ExtensionAnsweredCount`/`ExtensionDroppedCount`/`TalkTime`) — use it only if
the per-agent breakdown inside a queue is needed.

### Reference / lookup data

```php
$connector->queues()->list();   // queue config: Number, Name, PollingStrategy, Agents[]...
$connector->system()->status(); // health check: Version, CallsActive, ExtensionsRegistered...
```

`Queues::list()` returns configuration only — no live "calls waiting" or "agents
logged in now" fields. Useful for mapping a queue DN to its display name.

### Live calls (real-time)

```php
$response = $connector->activeCalls()->list();
$calls = $response->json('value');
// [{"Id": 33421, "Caller": "1000 Alice", "Callee": "230 Bob",
//   "Status": "Talking", "EstablishedAt": "...", "ServerNow": "..."}, ...]
```

`ActiveCalls` is genuinely real-time — one row per call currently in progress
(only `Status: "Talking"` observed; there is no known way to see calls still
ringing or waiting). Neither `Caller` nor `Callee` is reliably "the agent"
(depends on call direction), and there is no field attributing a call back to the
queue it came through. To check whether a specific agent DN is on a call related
to a given queue, extract the leading DN from `Caller` and `Callee` (both follow
the `"{DN} {Name}"` format) and match against that queue's `Agents` list from
`queues()->list()`.

### Recordings, transcription & AI

```php
// List recordings for a date range (optionally filter by phone number)
$response = $connector->recordings()->list(
    from: Carbon::yesterday('UTC'),
    to: Carbon::today('UTC'),
    callerNumber: '0612345678',
);
$recordings = $response->json('value');
// [{"Id": 12345, "StartTime": "...", "IsTranscribed": true,
//   "Transcription": "...", "Summary": "...", "SentimentScore": 4}, ...]

// Fetch one recording by ID (includes all transcription fields)
$recording = $connector->recordings()->find(12345)->json('value.0');

// Trigger on-demand transcription (POST — requires transcription enabled on the PBX)
$connector->recordings()->transcribe([12345, 12346]);

// Download the audio file
$audio = $connector->recordings()->download(12345)->body();

// Supported transcription languages
$languages = $connector->system()->transcribeLanguages()->json('value');

// AI provider settings
$ai = $connector->system()->aiSettings()->json();
```

`Transcription`, `Summary`, and `SentimentScore` are fields on the `Recording`
entity — there is no dedicated transcription endpoint. If `IsTranscribed` is
`false`, call `transcribe()` first and poll until `IsTranscribed` becomes `true`.

> **Gotcha:** `contains()` filters on phone numbers may be rejected on some PBX
> builds. If that happens, filter by date only and post-filter the result in PHP.

### Inbound / outbound call reports

```php
$response = $connector->reports()->inboundCallsForDate(Carbon::yesterday('UTC'));
$response = $connector->reports()->outboundCallsForDate(Carbon::yesterday('UTC'));
```

Both return one row per call with trunk, duration, and call-type breakdown.

## What this package does not do

- No **Call Control API** (`/callcontrol/...`) support — that's a separate REST
  surface (not OData, different base path) for live participant control and queue
  depth.
- No scheduling or file-delivery logic — this is the API client only.
