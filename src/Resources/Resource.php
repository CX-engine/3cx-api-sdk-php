<?php

declare(strict_types=1);


namespace CXEngine\ThreeCx\Resources;

use CXEngine\ThreeCx\ThreeCxConnector;

class Resource
{
    public function __construct(
        protected ThreeCxConnector $connector
    ) {
        
    }
}
