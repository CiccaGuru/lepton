<?php

namespace Lepton\Controller;

use Lepton\Base\Application;

class SiteFilter
{
    public function url($string)
    {
        return "/".Application::getDir()."/".$string;
    }


    public function sublink($url, $tocheck)
    {
        $url = str_replace(Application::getDir(), "", $url);
        $tocheck = str_replace(Application::getDir(), "", $tocheck);

        $url = str_replace(Application::$controller, "", $url);
        $tocheck = str_replace(Application::$controller, "", $tocheck);

        $url = trim(str_replace("//", "", $url), "/");
        $tocheck = trim(str_replace("//", "", $tocheck), "/");

        $url_exp = explode("/", $url);
        $tocheck_exp = explode("/", $tocheck);
        return intval($url_exp[0] == $tocheck_exp[0]);
    }


    public function to_json($elements)
    {
        return json_encode($elements);
    }
}
