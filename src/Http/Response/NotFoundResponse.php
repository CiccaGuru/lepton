<?php

namespace Lepton\Http\Response;

 class NotFoundResponse extends HttpResponse
{

    public function __construct(
        public $body = '',
        public $headers = array()
        )
    {
        parent::__construct(statusCode: 404, headers: $headers, body: require '404.php');
    }


}
