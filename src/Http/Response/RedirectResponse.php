<?php

namespace Lepton\Http\Response;
use Lepton\Core\Application;

class RedirectResponse extends HttpResponse
{

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


}
