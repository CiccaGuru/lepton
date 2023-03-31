<?php

namespace Lepton\Http;
use Lepton\Base\Application;

class RedirectResponse extends HttpResponse
{
    public $statusCode;
    public $headers;
    public $body;

    public function __construct($url, $htmx = false, $parse = true )
    {
        if ($this->isLocalUrl($url) && $parse) {
            $parsedUrl = "/".Application::getDir()."/".$url;
        } else {
            $parsedUrl = $url;
        }
        if ($htmx) {
            $headers = ["HX-Redirect" => $parsedUrl];
        } else {
            $headers = [ "Location" => $parsedUrl ];
        }
        parent::__construct(302, $headers, $body = '');
    }

    public function isLocalUrl($url)
    {
        $parsed_url = parse_url($url);
        return empty($parsed_url['host']) || $parsed_url['host'] === 'localhost';
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
