<?php
namespace Lepton\Base;

use Lepton\Routing\Route;

class Application{
  protected static $_config;
  protected static $_db;
  protected static $_routes;

  public function __construct(){
  }

  public static function loadConfig($config){
    static::$_config = $config->app;
    static::$_routes = $config->routes;
  }

  public static function loadDb($db){
    static::$_db = $db;
  }

  public static function unloadDb(){
    unset(static::$_db);
  }

  public static function getDb(){
    return static::$_db;
  }

  public static function loadErrorHandler(){

    $whoops = new \Whoops\Run;
    $handler = new \Whoops\Handler\PrettyPageHandler;

    $handler->setPageTitle("What the Kelvin! There was an unexpected error!");
    //die(print_r($handler->getResourcePaths()));
    $handler->addResourcePath(__DIR__."/css");
    $handler->addCustomCss("whoops.css");
    $whoops->pushHandler($handler);
    $whoops->register();
  }

 public static function matchRoutes(){
    foreach(static::$_routes as $pattern => $callback){
      $pattern = sprintf("/%s/%s", static::$_config->base_url, $pattern);
      Route::match($pattern, $callback);
    }
  }
}
?>