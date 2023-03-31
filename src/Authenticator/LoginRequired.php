<?php

namespace Lepton\Authenticator;

#[\Attribute]
class LoginRequired
{
    public function __construct(int $level = 1)
    {
    }
}
