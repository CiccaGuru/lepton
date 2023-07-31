<?php

namespace Lepton\Middleware;

use \Lepton\Http\{Request, HttpResponse};

// Abstract Middleware to be used in Routing

class BaseAccessControlMiddleware{

  protected function handle(Request $request): HttpResponse{
    return new HttpResponse(200);
  }

  public function __invoke(Request $request) {
        // Implement your logic for handling the request here
        // For example, logging the incoming request data
        // or checking authentication credentials

        return $this->handle($request); // You can return the modified request object or a new request object
    }




       /* $middlewareClasses = Application::getAppConfig()->middleware;
                    $middlewareChain = null;
                    foreach (array_reverse($middlewareClasses) as $middlewareClass => $middlewareOptions) {
                        $middlewareChain = new $middlewareClass($middlewareChain, ...$middlewareOptions);
                    }

                    die(print_r($middlewareChain));
                    $response = $middlewareChain($request);


                    $reflection = new \ReflectionMethod($controller, $method);
                    $attributes = $reflection->getAttributes();



                    $loginRequired = false;
                    $loginLevel = 0;
                    foreach ($attributes as $attribute) {
                        if ($attribute->getName() == LoginRequired::class) {
                            $loginRequired = true;
                            $arguments = $attribute->getArguments();
                            if(count($arguments) > 0) {
                                $loginLevel = array_pop($arguments);
                            } else {
                                $loginLevel = 0;
                            }
                        }
                    }

                    $authenticator = new \Lepton\Authenticator\UserAuthenticator();
                    $loggedIn = $authenticator->isLoggedIn();
                    if ($loginRequired && !$loggedIn) {
                        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                        $httpResponse = new RedirectResponse("login");
                    } elseif($loginRequired && $loggedIn && $authenticator->getLevel() < $loginLevel) {
                        $httpResponse = new RedirectResponse("");
                    } else {*/
}