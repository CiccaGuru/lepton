<?php

namespace Lepton\Middleware;

use \Lepton\Http\{Request, HttpResponse};

// Abstract Middleware to be used in Routing

abstract class AbstractMiddleware{

  public function __construct(private $next){  }

  abstract protected function handle(Request $request);

  public function __invoke(Request $request) {

        return $this->handle($request);
    }
}