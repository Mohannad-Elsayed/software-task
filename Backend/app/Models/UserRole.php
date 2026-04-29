<?php

namespace app\Models;

class UserRole
{
    private $role_id;
    private $role_name;

    public function __construct($role_id = null, $role_name = null)
    {
        $this->role_id = $role_id;
        $this->role_name = $role_name;
    }

    public function getRoleId() { return $this->role_id; }
    public function setRoleId($role_id) { $this->role_id = $role_id; return $this; }

    public function getRoleName() { return $this->role_name; }
    public function setRoleName($role_name) { $this->role_name = $role_name; return $this; }
}