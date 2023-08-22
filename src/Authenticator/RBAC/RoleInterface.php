<?php

namespace Lepton\Authenticator\RBAC;

interface RoleInterface
{
    public function hasPermission(PermissionInterface $permission); // Implement logic to check if the role has a permission
}
