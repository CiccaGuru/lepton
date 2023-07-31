<?php

namespace Lepton\Middleware;

use \Lepton\Http\Request;
use \Lepton\Routing\Match\{BaseMatch, MatchRoute};
use \Lepton\Http\Response\{SuccessResponse, HttpResponse, RedirectResponse};


class ACFMiddleware extends BaseAccessControlMiddleware{

    protected function checkPermissions(string $modifier, mixed ...$params):bool{

        $authenticator = new \Lepton\Authenticator\UserAuthenticator();
        $loggedIn = $authenticator->isLoggedIn();
        return ($loggedIn && $authenticator->getLevel() >= $params["loginLevel"]);
    }
}