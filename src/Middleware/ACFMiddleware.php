<?php

namespace Lepton\Middleware;

use Lepton\Authenticator\AccessControlAttributes\LoginRequired;
use Lepton\Http\Request;
use Lepton\Routing\Match\{BaseMatch, MatchRoute};
use Lepton\Http\Response\{SuccessResponse, HttpResponse, RedirectResponse};
use Lepton\Middleware\BaseAccessControlMiddleware;

class ACFMiddleware extends BaseAccessControlMiddleware
{
    public string $level_field;

    protected function handle(mixed ...$middlewareParams): HttpResponse|Request
    {
        $this->level_field = $middlewareParams["level_field"] ?? "level";

        return parent::handle(...$middlewareParams);
    }


    protected function checkPermissions(string $modifier, mixed ...$params): bool
    {

        if($modifier == LoginRequired::class) {
            $level = $params[0] ?? 1;

            $authenticator = new \Lepton\Authenticator\UserAuthenticator();
            $loggedIn = $authenticator->isLoggedIn();
            if(! $loggedIn) {
                return false;
            }
            $user = $authenticator->getLoggedUser();
            $splitted = explode("__", $this->level_field);
            $user_level = $user;
            foreach($splitted as $part) {
                $user_level = $user_level->$part;
            }
            return ($user_level >= $level);
        }
        return true;
    }
}
