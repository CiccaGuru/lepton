<?php

namespace Lepton\Middleware;

use Lepton\Http\Request;
use Lepton\Http\Response\{HttpResponse, RedirectResponse};
use Lepton\Middleware\AbstractMiddleware;
use Lepton\Core\Application;

abstract class ConditionalRedirectMiddleware extends AbstractMiddleware
{
    protected function handle(mixed ...$middlewareParams): HttpResponse|Request
    {
        $url = preg_replace("/^" . str_replace("/", "\/", Application::getAppConfig()->base_url) . "\//", "", $this->request->url);

        if(!in_array($url, $middlewareParams["allow"])){
            if($this->check_request($middlewareParams)){
                return new RedirectResponse($middlewareParams["redirect"], htmx: false, parse: false);
            }
        }
        return $this->request;
   }

    abstract function check_request($middlewareParams);
}
