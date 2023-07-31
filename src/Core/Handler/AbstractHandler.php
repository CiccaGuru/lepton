<?php

namespace Lepton\Core\Handler;
use Lepton\Http\Request;
use Lepton\Http\Response\HttpResponse;
use Lepton\Routing\Match;

abstract class AbstractHandler
{
    abstract public function handle($matcher) : HttpResponse;
    abstract public function resolveRequest() : Match\BaseMatch;


    protected Request $request;
    protected $middlewares = array();

    public function __construct($request){
      $this->request = $request;
    }

    public function addMiddlewares($middlewares) {
        $this->middlewares = $middlewares;
    }


    public function getResponse() : HttpResponse{
      $matcher = $this->resolveRequest();
      return $this->handle($matcher);

    }



  }

