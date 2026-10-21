<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Resources;

use Carbon\CarbonInterface;
use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\CallHistory\ListCallHistoryViewRequest;

class CallHistoryResource extends Resource
{
    public function list(CarbonInterface $from, CarbonInterface $to, int $top = 200): Response
    {
        return $this->connector->send(new ListCallHistoryViewRequest($from, $to, $top));
    }
}
