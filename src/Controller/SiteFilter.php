<?php

namespace Lepton\Controller;

use Lepton\Core\Application;

class SiteFilter
{
    public function url($string)
    {
        return Application::getDir()."/".$string;
    }


    public function sublink($url, $tocheck)
    {
        $url = str_replace(Application::getDir(), "", $url);
        $tocheck = str_replace(Application::getDir(), "", $tocheck);

        $url = str_replace(Application::$controller, "", $url);
        $tocheck = str_replace(Application::$controller, "", $tocheck);

        $url = trim(str_replace("//", "", $url), "/");
        $tocheck = trim(str_replace("//", "", $tocheck), "/");

        return str_starts_with($tocheck, $url) && $url != "";
    }


    public function query_count($query){
        return $query->count();
    }

    public function to_json($elements)
    {
        return json_encode($elements);
    }

    public function htmlquotes($element)
    {
        return htmlspecialchars($element);
    }

}
