<?php

namespace Lepton\Http\Response;

class EmptyResponse extends SuccessResponse
{

    public function __construct(
        public $body = '',
        public $headers = array()
        )
    {
    }

}
