<?php

namespace Lepton\Middleware;

use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Authenticator\UserAuthenticator;
use Lepton\Http\Request;
use Lepton\Routing\Match\{BaseMatch, MatchRoute};
use Lepton\Http\Response\{SuccessResponse, HttpResponse, RedirectResponse};
use Lepton\Middleware\BaseAccessControlMiddleware;
use ReflectionClass;

class RBACMiddleware extends BaseAccessControlMiddleware
{
    private string $rbac_class;
    private string $user_class;

    protected function handle(mixed ...$middlewareParams): HttpResponse|Request
    {
        $this->rbac_class = $middlewareParams["rbac_class"] ?? throw new \Exception("You have to define a RBAC class");

        $rbac_interfaces = class_implements($this->rbac_class);
        if(! in_array(\Lepton\Authenticator\RBAC\RBACInterface::class, $rbac_interfaces)) {
                throw new \Exception("RBAC class has to implement \Lepton\Authenticator\RBAC\RBACInterface");
        }

        $this->user_class = $middlewareParams["user_class"] ?? throw new \Exception("You have to define a User class");

        $user_interfaces = class_implements($this->user_class);
        if(! in_array(\Lepton\Authenticator\RBAC\UserInterface::class, $user_interfaces)) {
                throw new \Exception("User class has to implement \Lepton\Authenticator\RBAC\UserInterface");
        }

        return parent::handle(...$middlewareParams);
    }


    protected function checkPermissions(string $modifier, mixed ...$params): bool
    {

        if($modifier == LoginRequired::class) {

            $level = isset($params[0]) ? $params[0] : 1;
            $authenticator = new \Lepton\Authenticator\UserAuthenticator();
            $loggedIn = $authenticator->isLoggedIn();
            if(! $loggedIn) {
                return false;
            }
            $user = $authenticator->getLoggedUser();
            $num_privileges = $user->privileges->and(livello__gte: $level)->count();
            return ($num_privileges > 0);
        } elseif($modifier == PermissionRequired::class){
            $user = (new UserAuthenticator)->getLoggedUser();
            die(print_r($params));
        }
        return true;
    }
}
