<?php

namespace Lepton\Authenticator\AccessControlAttributes;

#[\Attribute]
class PermissionRequired
{
    public function __construct(int $permission, ...$args)
    {
    }
}
