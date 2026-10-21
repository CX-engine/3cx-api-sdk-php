<?php

namespace CXEngine\ThreeCx\Resources;

use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\System\SystemStatusRequest;

class SystemResource extends Resource
{
    public function status(): Response
    {
        return $this->connector->send(new SystemStatusRequest);
    }
}
