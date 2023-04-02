<?php

namespace Lepton\Controller;

use Lepton\Authenticator\UserAuthenticator;
use Lepton\Base\Application;
use Liquid\{Liquid, Template};
use Lepton\Boson\QuerySet;
use Lepton\Http\{HttpResponse, RedirectResponse};

abstract class BaseController
{
    protected array $default_parameters;
    public string $baseLink;
    public function render(string $view, array $parameters = array(), $headers = array())
    {
        Liquid::set('INCLUDE_SUFFIX', 'html');
        Liquid::set('INCLUDE_PREFIX', '');
        $path = "app/Views";
        $template  = new Template($path);
        $template->registerFilter(new SiteFilter());
        $template->parseFile($view);
        $parameters = array_map(function ($x) {
            if ($x instanceof QuerySet) {
                return $x->do();
            } else {
                return $x;
            }
        }, $parameters);
        $parameters["page"] = $_SERVER["REQUEST_URI"];
        $authenticator = new UserAuthenticator();

        $parameters["logged_user"] = $authenticator->getLoggedUser();

        if (isset($this->default_parameters)) {
            $parameters = array_merge($parameters, $this->default_parameters);
        }
        return new HttpResponse(200, $headers, $template->render($parameters));
    }


    public function redirect($url, $htmx = false, $parse = true)
    {
        return new RedirectResponse($url, $htmx, $parse);
    }
}
