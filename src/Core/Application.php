<?php

namespace Lepton\Core;

use Lepton\Core\Handler\AbstractHandler;
use Lepton\Http\Request;
use Lepton\Http\Response\{FileResponse, HttpResponse};
use Whoops;

class Application
{
    protected static $_config;
    protected static $_db;
    protected static $_dbconfig;
    protected static $_auth;
    protected static $_email;

    public static $request;
    public static $routes;

    public static string $controller;
    public static $documentRoot;

    /**
     * Entry point for application logic
     * Here it is where the whole magic happens
     */
    public static function run()
    {
        if(! static::$_config) {
            throw new \Exception("No config loaded!");
        }

        session_start();

        if(static::$_dbconfig->use_db) {
            static::$_db = new Database(
                static::$_dbconfig->host,
                static::$_dbconfig->user,
                static::$_dbconfig->password,
                static::$_dbconfig->dbname
            );
        }

        static::$request = new Request();
        $handler = static::getHandler();
        $response = $handler->getResponse();
        $response->send();

        if(static::$_dbconfig->use_db) {
            static::$_db->close();
        }

        exit();

    }

    /**
     * Returns the appropriate handler for the pending request
     *
     * @return AbstractHandler $handler
    */
    public static function getHandler(): AbstractHandler
    {
        $regex = '/\.(?:'.implode('|', static::$_config->static_files_extensions).')$/';
        if (preg_match($regex, static::$request->url)) {
            return new Handler\StaticHandler(static::$request);
        }

        $handler = new Handler\BaseHandler(static::$request);
        $handler->addMiddlewares(static::$_config->middlewares);
        return $handler;
    }


    /**
     * Loads the configuration for the application
     * @param \stdClass $config
     */

    public static function loadConfig(\stdClass $config)
    {
        if(property_exists($config, "app")) {
            static::$_config = $config->app;
        } else {
            throw new \Exception("No app configuration!");
        }

        if(property_exists($config, "routes")) {
            static::$routes = $config->routes;
        } else {
            throw new \Exception("No routes configuration!");
        }


        if(property_exists($config, "database")) {
            static::$_dbconfig = $config->database;
        } else {
            throw new \Exception("No database configuration!");
        }


        if(property_exists($config, "auth")) {
            static::$_auth = $config->auth;
        } else {
            throw new \Exception("No authentication configuration!");
        }

        if(property_exists($config, "email")) {
            static::$_email = $config->email;
        } else {
            throw new \Exception("No email configuration!");
        }

        static::$documentRoot = $_SERVER["DOCUMENT_ROOT"];
    }



    /**
     * Loads the error handler (Whoops)
     * For Whoops documentation
     * @see https://filp.github.io/whoops/
     */
    public static function loadErrorHandler()
    {
        $whoops = new \Whoops\Run();

        $handler = new \Whoops\Handler\PrettyPageHandler();
        $handler->setPageTitle("Whooops! There was an unexpected error!");
        $handler->addResourcePath(__DIR__."/css");
        $handler->addCustomCss("whoops.css");

        $whoops->pushHandler($handler);
        $whoops->register();
    }

    /*==================================== GETTERS ======================================= */

    public static function getDb()
    {
        return static::$_db;
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

    public static function getAppConfig()
    {
        return static::$_config;
    }
}
