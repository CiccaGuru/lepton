<?php

namespace Lepton\Core\Handler;
use Lepton\Http\Response\{SuccessResponse, NotFoundResponse};
use Lepton\Routing\Match;
use Lepton\Routing\Match\{MatchRoute, Match404};
use Lepton\Routing\UrlResolver;
use Lepton\Core\Application;
use Lepton\Controller\BaseController;

class BaseHandler extends AbstractHandler
{

  public function resolveRequest() : Match\BaseMatch{
    $resolver = new UrlResolver($this->request->url);

    foreach (Application::$routes as $pattern => $callback) {

      // Check if callback is a controller method
      $controller = $callback[0];
      $method = $callback[1];
      if (! method_exists($controller, $method)) {
        throw new \Lepton\Exceptions\ControllerNotFoundException("Invalid Controller and/or method in routes.php");
      }

      // Add base_url to $pattern, if needed
      $pattern = sprintf("%s/%s", Application::getAppConfig()->base_url, $pattern);
      // If it matches, return the match
      $parameters = $resolver->match($pattern);
      if (is_array($parameters)) {
        return new MatchRoute(controller: $controller, method: $method, parameters: $parameters);
      }
    }
    return new Match404();
  }

  public function handle($matcher) : NotFoundResponse|SuccessResponse{
    if($matcher instanceof Match404){
      return new NotFoundResponse();
    }
      $controller = new $matcher->controller;
      $method =  $matcher->method;

      return $controller->$method();
    }

}
