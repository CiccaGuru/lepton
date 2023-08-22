<?php

namespace Lepton\Http\Response;

 class JSONResponse extends SuccessResponse
{

    public function __construct(
        public $array,
        )
    {
        parent::__construct(headers: ["Content-Type" => "application/json"]);
    }

    public function sendBody(){
        echo json_encode($this->array);
    }


}
