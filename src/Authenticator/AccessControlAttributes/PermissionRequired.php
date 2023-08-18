<?php

namespace Lepton\Authenticator;

#[\Attribute]
class PermissionRequired
{
    public function __construct(int $permission, ...$args)
    {
    }
}
