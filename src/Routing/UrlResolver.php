<?php

namespace Lepton\Routing;

use Lepton\Exceptions\ControllerNotFoundException;
use Lepton\Http\Request;
use Lepton\Http\{HttpResponse, RedirectResponse};
use Lepton\Authenticator\{UserAuthenticator, LoginRequired};
use Lepton\Core\Application;

class UrlResolver
{

    public function __construct(private $pattern){}

    public function patternPrepare($pattern)
    {
        $pattern = str_replace('?', '\?', $pattern);

        // if the type selector is not followed by a quantifier, than choose any length
        $pattern = preg_replace('/<([^{\d}]+?)(?!{[\d]+}):(.+?)>/', '<${1}+:${2}>', $pattern);

        // if the type selector is "int", match numbers
        $pattern = preg_replace('/<int([+{\d}]+?):(.+?)>/', '<\\d${1}:${2}>', $pattern);

        // if the type selector is "alpha" or "alnum"
        $pattern = preg_replace('/<(alpha|alnum)([+{\d}]+?):(.+?)>/', '<[[:${1}:]]${2}:${3}>', $pattern);

        // if there is no type selector, match anything but a /
        $pattern = preg_replace('/<([^:]+?)>/', '(?<${1}>[^/]+)', $pattern);

        // build regex
        $pattern = preg_replace('/<(.+?):(.+?)>/', '(?<${2}>${1})', $pattern);

        // replace slashes for regex
        $pattern = str_replace('/', '\/', $pattern);

        return $pattern;

    }


    protected function patternMatch($pattern, &$parameters)
    {
        $parameters = array();


        $pattern = $this->patternPrepare($pattern);

        // perform actual match on request uri
        $is_match = preg_match(sprintf("/^%s$/", $pattern), $_SERVER['REQUEST_URI'], $parameters);

        // keep only string keys
        $parameters = array_filter($parameters, function ($k) {
            return is_string($k);
        }, ARRAY_FILTER_USE_KEY);
        return $is_match;
    }

    public function match($pattern): bool|array
    {
        $parameters = array();

        return $this->patternMatch($pattern, $parameters) ? $parameters : false;



/*
        {
            if (is_callable($callback)) {

                $callback($parameters);
                exit();
            }

            if (is_array($callback)) {
                $controller = $callback[0];
                $method = $callback[1];
                if (! method_exists($controller, $method)) {
                     throw new \Lepton\Exceptions\ControllerNotFoundException("Invalid Controller and/or method in routes.php");
                }



                        $controllerObject = new $controller();
                        Application::$controller = $controllerObject->baseLink;
                        $httpResponse = $controllerObject->$method(...$parameters);


                    $httpResponse->send();


                } else {
                    throw new \Lepton\Exceptions\ControllerNotFoundException("Invalid Controller and/or method in routes.php");
                }
                return true;
            }

        return false;*/
    }
}
