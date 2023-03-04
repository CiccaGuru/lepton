<?php
namespace Lepton\Routing;

use Lepton\Exceptions\ControllerNotFoundException;
use \Lepton\Http\Request;

class Route{


  // Route get type
  public static function get($route, $callback)
  {
      if (strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') !== 0) {
          return;
      }

      self::match($route, $callback);
  }

  //Route post type
  public static function post($route, $callback)
  {
      if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') !== 0) {
          return;
      }

      self::match($route, $callback);
  }

  public static function matchrgx($regex, $callback)
  {
     print_r($_SERVER['REQUEST_URI']);
      $params = strtok($_SERVER['REQUEST_URI'], '/');
      $params = (stripos($params, "/") !== 0) ? "/" . $params : $params;
      $regex = str_replace('/', '\/', $regex);
      $is_match = preg_match('/^' . ($regex) . '$/', $params, $matches, PREG_OFFSET_CAPTURE);

      if ($is_match) {
          array_shift($matches);
          $params = array_map(function ($param) {
              return $param[0];
          }, $matches);
          $callback(new Request($params));
          exit();
      }
  }

  protected static function patternMatch($pattern, &$parameters){

    $parameters = array();

    // if the type selector is not followed by a quantifier, than choose any length
    $pattern = preg_replace('/<([^{\d}]+)(?!{[\d]+}):(.+)>/', '<${1}+:${2}>', $pattern);

    // if the type selector is "int", match numbers
    $pattern = preg_replace('/<int([+{\d}]+):(.+)>/', '<\\d${1}:${2}>', $pattern);

    // if the type selector is "alpha" or "alnum"
    $pattern = preg_replace('/<(alpha|alnum)([+{\d}]+):(.+)>/', '<[[:${1}:]]${2}:${3}>', $pattern);

    // if there is no type selector, match anything but a /
    $pattern = preg_replace('/<([^:]+)>/', '(?<${1}>[^/]+)', $pattern);

    // build regex
    $pattern = preg_replace('/<(.+):(.+)>/', '(?<${2}>${1})', $pattern);

    // replace slashes for regex
    $pattern = str_replace('/', '\/', $pattern);

    // perform actual match on request uri
    $is_match = preg_match(sprintf("/^%s$/", $pattern), $_SERVER['REQUEST_URI'], $parameters);

    // keep only string keys
    $parameters = array_filter($parameters,function ($k) { return is_string($k); }, ARRAY_FILTER_USE_KEY);
    return $is_match;

  }

  public static function match($pattern, $callback)
  {
      $parameters = array();

      if(self::patternMatch($pattern, $parameters)){

        if(is_callable($callback)){
          $callback($parameters);
          exit();
        }

        if(is_array($callback)){
          $controller = "\App\Controllers\\".$callback[0];
          $method = $callback[1];
          if(!is_callable(array($controller, $method))){
            throw new \Lepton\Exceptions\ControllerNotFoundException("Invalid Controller and/or method in routes.php");
          }

          $controller::$method(...$parameters);
        }
      }

  }
}

?>