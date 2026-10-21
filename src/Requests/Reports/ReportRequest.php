<?php

namespace CXEngine\ThreeCx\Requests\Reports;

use Carbon\CarbonInterface;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Base class for the `Report*` family of XAPI OData bound functions.
 *
 * These use "path-style" function parameters (3CX's OData bound-function syntax:
 * `Entity/Pbx.Method(param=value,...)`), not `?$x=y` query strings. Date-time
 * values go in unquoted; string values need single quotes.
 */
abstract class ReportRequest extends Request
{
    protected Method $method = Method::GET;

    protected function formatDateTime(CarbonInterface $dateTime): string
    {
        return $dateTime->clone()->utc()->format('Y-m-d\TH:i:s\Z');
    }

    protected function odataString(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }
}
