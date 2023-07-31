<?php

namespace Lepton\Http\Response;

 class SuccessResponse extends HttpResponse
{

    public function __construct(
        public $body = '',
        public $headers = array()
        )
    {
        parent::__construct(statusCode: 200, headers: $headers, body: $body);
    }


}
