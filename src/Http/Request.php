<?php
namespace Lepton\Http;

class Request
{
    public $method;
    public $post;
    public $get;
    public $cookie;
    public $url;
    public $contentType;
    public function __construct()
    {
        $this->method = trim($_SERVER['REQUEST_METHOD']);
        $this->contentType = !empty($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        $this->cookie = $_COOKIE;
        $this->post = $_POST;
        $this->get = $_GET;
        $this->url = $_SERVER['REQUEST_URI'];
    }


}