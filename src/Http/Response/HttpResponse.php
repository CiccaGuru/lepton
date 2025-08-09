<?php

namespace Lepton\Http\Response;

class HttpResponse
{

    public function __construct(
        public $statusCode,
        public $body = '',
        public $headers = array()
        )
    {
    }


    public function sendHeaders(){

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }

    public function sendBody(){
        echo $this->body;
    }

    public function send()
    {

        // Send headers
        $this->sendHeaders();

        // Send response code
        http_response_code($this->statusCode);

        // Send response bod
        $this->sendBody();
    }

    public function __toString()
    {
        return $this->body;
    }
}
