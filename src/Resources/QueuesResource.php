<?php

namespace CXEngine\ThreeCx\Resources;

use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\Queues\ListQueuesRequest;

class QueuesResource extends Resource
{
    public function list(bool $expandAgents = true): Response
    {
        return $this->connector->send(new ListQueuesRequest($expandAgents));
    }
}
