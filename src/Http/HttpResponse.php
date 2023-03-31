<?php

namespace Lepton\Http;

class HttpResponse
{
    public $statusCode;
    public $headers;
    public $body;

    public function __construct($statusCode, $headers = array(), $body = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function send()
    {
        // Send headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Send response code
        http_response_code($this->statusCode);

        // Send response body
        echo $this->body;
    }
}
