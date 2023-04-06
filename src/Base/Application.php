<?php

namespace Lepton\Base;

use Lepton\Http\HttpResponse;
use Lepton\Routing\Route;

class Application
{
    protected static $_config;
    protected static $_db;
    protected static $_routes;
    protected static $_dbconfig;
    protected static $_auth;
    protected static $_email;

    public static string $controller;

    public function __construct()
    {
    }

    public static function loadConfig($config)
    {
        static::$_config = $config->app;
        static::$_routes = $config->routes;
        static::$_dbconfig = $config->database;
        static::$_auth = $config->database->authentication;
        static::$_email = $config->email;
        unset(static::$_dbconfig->authentication);
    }

    public static function loadDb($db)
    {
        static::$_db = $db;
    }

    public static function unloadDb()
    {
        static::$_db->close();
    }

    public static function getDb()
    {
        return static::$_db;
    }

    public static function loadErrorHandler()
    {
        $whoops = new \Whoops\Run();
        $handler = new \Whoops\Handler\PrettyPageHandler();

        $handler->setPageTitle("What the Kelvin! There was an unexpected error!");
        //die(print_r($handler->getResourcePaths()));
        $handler->addResourcePath(__DIR__."/css");
        $handler->addCustomCss("whoops.css");
        $whoops->pushHandler($handler);
        $whoops->register();
    }

 public static function matchRoutes()
 {
     $found = false;

     if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|pdf)$/', $_SERVER["REQUEST_URI"])) {
         self::serveStaticFile($_SERVER["REQUEST_URI"]);
     }

     foreach (static::$_routes as $pattern => $callback) {
         $pattern = sprintf("/%s/%s", static::$_config->base_url, $pattern);
         if ($found = Route::match($pattern, $callback)) {
             break;
         }
     }
     if (!$found) {
        include("404.php");
        exit;

     }
 }

 public static function serveStaticFile($url)
 {
     // Get the requested file path
     $url = preg_replace("/^\/voltaire\//", "/", $url);
     $filePath = dirname($_SERVER["SCRIPT_FILENAME"]). '/resources' . $url;
     // Check if the file exists
     if (file_exists($filePath)) {
         // Get the file extension
         $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
         // Set the content type based on the file extension
         switch ($fileExtension) {
             case 'png':
                 $contentType = 'image/png';
                 break;
             case 'jpg':
             case 'jpeg':
                 $contentType = 'image/jpeg';
                 break;
             case 'gif':
                 $contentType = 'image/gif';
                 break;
             case 'css':
                 $contentType = 'text/css';
                 break;
             case 'js':
                 $contentType = 'text/javascript';
                 break;
             case 'pdf':
                 $contentType = 'application/pdf';
                 break;
             default:
                 $contentType = '';
                 break;
         }

         // If the content type is not empty, set the header and output the file
         if ($contentType !== '') {
             header("Content-Type: $contentType");
             readfile($filePath);
             exit;
         }
     }
 }

    public static function getDir()
    {
        return static::$_config->base_url;
    }

    public static function getAuthConfig()
    {
        return static::$_auth;
    }


    public static function getDbConfig()
    {
        return static::$_dbconfig;
    }

    public static function getEmailConfig()
    {
        return static::$_email;
    }
}
