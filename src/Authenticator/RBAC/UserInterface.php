<?php

namespace Lepton\Authenticator\RBAC;

interface UserInterface {
    /**
     * Check if the user has a specific role.
     *
     * @param string $role The role to check.
     * @return bool Whether the user has the role.
     */
    public function hasRole(RoleInterface $role) : bool;

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission The permission to check.
     * @return bool Whether the user has the permission.
     */
    public function hasPermission(PermissionInterface $permission) : bool;


     /**
     * Retrieve the permissions associated with the user's roles.
     *
     * @return array An array of permissions associated with the user's roles.
     */
    public function getPermissions() : array;


    /**
     * Retrieve the roles associated with the user's roles.
     *
     * @return array An array of roles associated with the user's roles.
     */
    public function getRoles() : array;



    /**
     * Check if the user has access to a specific permission or role.
     *
     * @param PermissionInterface|RoleInterface $permissionOrRole The permission or role to check.
     * @return bool Whether the user has access to the permission or role.
     */
    public function hasAccess(PermissionInterface|RoleInterface $permissionOrRole) : bool;


}