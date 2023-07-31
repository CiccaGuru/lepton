<?php

namespace Lepton\Middleware;

use Lepton\Http\Request;
use Lepton\Http\Response\HttpResponse;
use Lepton\Routing\Match\BaseMatch;

// Abstract Middleware to be used in Routing

abstract class AbstractMiddleware{

  public BaseMatch $match;
  public Request $request;

  public function __construct(){  }

  abstract protected function handle(mixed ...$args): HttpResponse|Request;

  public function addMatcher(BaseMatch $match){
    $this->match = $match;
  }

  public function setRequest(Request $request){
    $this->request = $request;
  }

  public function __invoke(mixed ...$args) {
        return $this->handle(...$args);
    }
}