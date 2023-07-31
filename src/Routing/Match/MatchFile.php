<?php

namespace Lepton\Routing\Match;


class MatchFile extends BaseMatch
{
  public function __construct(public $filePath, public $contentType)
  {

  }

}
