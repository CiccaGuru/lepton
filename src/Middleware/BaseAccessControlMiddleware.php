<?php

namespace Lepton\Middleware;

use Lepton\Authenticator\AccessControlAttributes\{LoginRequired, AbstractAccessControlAttribute};
use Lepton\Core\Application;
use Lepton\Http\Request;
use Lepton\Routing\Match\MatchRoute;
use Lepton\Http\Response\{HttpResponse, RedirectResponse};


class BaseAccessControlMiddleware extends AbstractMiddleware
{

    protected function handle(mixed ...$middlewareParams): HttpResponse|Request
    {
        if($this->match instanceof MatchRoute) {
            $reflection = new \ReflectionMethod($this->match->controller, $this->match->method);
            $attributes = $reflection->getAttributes();

            foreach ($attributes as $attribute) {
                if(is_subclass_of($attribute->getName(), AbstractAccessControlAttribute::class)) {
                    return
                        $this->checkPermissions($attribute->getName(),  ...($attribute->getArguments()))?
                        $this->request :
                        new RedirectResponse(
                            Application::getAuthConfig()->login_url,
                            redirect_after: $this->request->url
                        );
                }

            }
            return $this->request;
        }
    }


    protected function checkPermissions(string $modifier, mixed ...$params):bool{
        if($modifier == LoginRequired::class){
            $authenticator = new \Lepton\Authenticator\UserAuthenticator();
            return $authenticator->isLoggedIn();
        }
        return true;
    }

}
