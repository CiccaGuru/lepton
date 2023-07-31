<?php

namespace Lepton\Http\Response;

 class InternalErrorResponse extends HttpResponse
{

    public function __construct(
        public $body = '',
        public $headers = array()
        )
    {
        parent::__construct(statusCode: 500, headers: $headers);
    }


}
