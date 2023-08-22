<?php

namespace Lepton\Authenticator\RBAC;

/**
 * RBACInterface defines the interface for Role-Based Access Control (RBAC) functionality.
 */
interface RBACInterface
{

    /**
     * Retrieve a list of users who have been assigned a specific role.
     *
     * @param string $role The role for which to retrieve users.
     * @return array An array of users who have been assigned the role.
     */
    public function getUsersWithRole(RoleInterface $role);

    /**
     * Retrieve a list of users who have been granted a specific permission.
     *
     * @param string $permission The permission for which to retrieve users.
     * @return array An array of users who have been granted the permission.
     */
    public function getUsersWithPermission(PermissionInterface $permission);


}
