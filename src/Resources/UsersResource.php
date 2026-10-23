<?php

namespace CXEngine\ThreeCx\Resources;

use Saloon\Http\Response;
use CXEngine\ThreeCx\Requests\Users\ListUsersRequest;

class UsersResource extends Resource
{
    /**
     * @param  string[]|null  $select  OData `$select` fields; null keeps the request's default set.
     */
    public function list(?array $select = null): Response
    {
        return $this->connector->send($select === null ? new ListUsersRequest : new ListUsersRequest($select));
    }
}
