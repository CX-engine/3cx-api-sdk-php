<?php

declare(strict_types=1);

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;

/**
 * `GET /xapi/v1/ReportOutboundCalls/Pbx.GetOutboundCalls(...)` — outbound call
 * report for a period. One row per call with trunk, duration, and call-type info.
 *
 * `trunkDns` and `callsType` are optional filters; pass empty string / 0 for
 * "no filter". Parameters follow the same undocumented-integer convention as
 * the other Report* endpoints.
 */
class GetOutboundCallsRequest extends ReportRequest
{
    public function __construct(
        protected readonly CarbonInterface $periodFrom,
        protected readonly CarbonInterface $periodTo,
        protected readonly string $trunkDns = '',
        protected readonly int $callsType = 0,
    ) {}

    public function resolveEndpoint(): string
    {
        $params = implode(',', [
            'periodFrom=' . $this->formatDateTime($this->periodFrom),
            'periodTo=' . $this->formatDateTime($this->periodTo),
            'trunkDns=' . $this->odataString($this->trunkDns),
            'callsType=' . $this->callsType,
        ]);

        return "/xapi/v1/ReportOutboundCalls/Pbx.GetOutboundCalls({$params})";
    }
}
