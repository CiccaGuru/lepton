<?php

namespace Lepton\Authenticator\AccessControlAttributes;

#[\Attribute]
class LoginRequired extends AbstractAccessControlAttribute
{
    public function __construct(int $level = 1)
    {
    }
}
