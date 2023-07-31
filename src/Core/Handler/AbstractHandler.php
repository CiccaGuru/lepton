<?php

namespace Lepton\Core\Handler;
use Lepton\Http\Request;
use Lepton\Http\Response\HttpResponse;
use Lepton\Routing\Match\BaseMatch;

abstract class AbstractHandler
{
    abstract protected function handle(BaseMatch $matcher) : HttpResponse;
    abstract protected function resolveRequest() : BaseMatch;


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

