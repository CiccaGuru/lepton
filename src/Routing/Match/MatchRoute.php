<?php

namespace Lepton\Routing\Match;


class MatchRoute extends BaseMatch
{
  public function __construct(public $controller, public $method, public $parameters){}

}
