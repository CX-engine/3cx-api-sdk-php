<?php

namespace CXEngine\ThreeCx\Resources;

use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\ActiveCalls\ListActiveCallsRequest;

class ActiveCallsResource extends Resource
{
    public function list(): Response
    {
        return $this->connector->send(new ListActiveCallsRequest);
    }
}
