<?php

namespace Lepton\Core\Handler;

use Lepton\Http\Response\{HttpResponse, SuccessResponse, NotFoundResponse, InternalErrorResponse};
use Lepton\Routing\Match\{BaseMatch, MatchRoute, Match404};
use Lepton\Routing\UrlResolver;
use Lepton\Core\Application;
use Lepton\Exceptions;
use Lepton\Controller\BaseController;

class BaseHandler extends AbstractHandler
{
    /**
     * Get the matcher that matches the current request
     *
     * @return BaseMatch
     *
     */

    protected function resolveRequest(): BaseMatch
    {
        $resolver = new UrlResolver($this->request->url);

        foreach (Application::$routes as $pattern => $callback) {

            // Add base_url to $pattern, if needed
            $pattern = sprintf("%s/%s", Application::getAppConfig()->base_url, $pattern);
            // If it matches, return the match
            $parameters = $resolver->match($pattern);
            if (is_array($parameters)) {
                // Check if callback is a controller method
                $controller = $callback[0];
                $method = $callback[1];
                if (! method_exists($controller, $method)) {
                    throw new Exceptions\ControllerNotFoundException("Invalid Controller and/or method in routes.php");
                }
                return new MatchRoute(controller: $controller, method: $method, parameters: $parameters);
            }
        }
        return new Match404();
    }


    /**
     * Handles the request forwarding it to the $handler
     * @param Match404|MatchRoute $matcher
     * @return NotFoundResponde|SuccessResponse
     */
    protected function handle(BaseMatch $matcher): HttpResponse
    {
        if($matcher instanceof Match404) {
            return new NotFoundResponse();
        }

        if($matcher instanceof MatchRoute) {

            foreach($this->middlewares as $middleware => $args){
              $middlewareInstance = new $middleware();
              $middlewareInstance->addMatcher($matcher);
              $middlewareInstance->setRequest($this->request);
              $middlewareResult = $middlewareInstance(...$args);
              if($middlewareResult instanceof HttpResponse){
                return $middlewareResult;
              }
            }

            $controller = new $matcher->controller();
            $method =  $matcher->method;
            return $controller->$method();
        }
        throw new \Exception("Wrong matcher!");
        return new InternalErrorResponse();
    }

}
