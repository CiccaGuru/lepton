<?php

namespace Lepton\Controller;

use Lepton\Authenticator\UserAuthenticator;
use Lepton\Core\Application;
use Liquid\{Liquid, Template};
use Lepton\Boson\QuerySet;
use Lepton\Http\Response\{SuccessResponse, RedirectResponse};

abstract class BaseController
{
    protected array $default_parameters;
    protected array $custom_filters;
    public string $baseLink;

    public function url($string){
        return Application::getDir()."/".$string;
    }

    public function render(string $view, array $parameters = array(), $headers = array())
    {
        Liquid::set('INCLUDE_SUFFIX', 'html');
        Liquid::set('INCLUDE_PREFIX', '');
        $path = "app/Views";
        $template  = new Template($path);
        $template->registerFilter(new SiteFilter());

        if(isset($this->custom_filters)){
            foreach($this->custom_filters as $filter){
                $template->registerFilter(new $filter());
            }
        }
        $template->parseFile($view);
        $parameters = array_map(function ($x) {
            if ($x instanceof QuerySet) {
                return $x->do();
            } else {
                return $x;
            }
        }, $parameters);


        $authenticator = new UserAuthenticator();


        $parameters["page"] = $_SERVER["REQUEST_URI"];
        $parameters["logged_user"] = $authenticator->getLoggedUser();
        $parameters["base_link"] = $this->baseLink;

        if (isset($this->default_parameters)) {
            $parameters = array_merge($parameters, $this->default_parameters);
        }
        return new SuccessResponse($template->render($parameters), headers: $headers);
    }


    public function redirect($url, $htmx = false, $parse = true)
    {
        return new RedirectResponse($url, $htmx, $parse);
    }
}
