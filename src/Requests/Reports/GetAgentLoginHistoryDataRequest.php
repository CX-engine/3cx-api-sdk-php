<?php

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /ReportAgentLoginHistory/Pbx.GetAgentLoginHistoryData(...)` — per agent,
 * per queue, per login session: `loggedInDt`/`LoggedOutDt` timestamps and
 * `LoggedInInterval`/`TalkingInterval` ISO-8601 durations. This is the source for
 * "who was logged into which queue, and when" — confirmed live against
 * lacostedbe.on3cx.fr with real session data, no gotchas found (unlike
 * {@see GetCallLogDataRequest}).
 */
class GetAgentLoginHistoryDataRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $startDt,
        protected readonly CarbonInterface $endDt,
        protected readonly string $clientTimeZone = 'UTC',
        protected readonly string $queueDnStr = '',
        protected readonly string $agentDnStr = '',
    ) {
    }

    public function resolveEndpoint(): string
    {
        $params = implode(',', [
            'clientTimeZone='.$this->odataString($this->clientTimeZone),
            'startDt='.$this->formatDateTime($this->startDt),
            'endDt='.$this->formatDateTime($this->endDt),
            'queueDnStr='.$this->odataString($this->queueDnStr),
            'agentDnStr='.$this->odataString($this->agentDnStr),
        ]);

        return "/xapi/v1/ReportAgentLoginHistory/Pbx.GetAgentLoginHistoryData({$params})";
    }
}
