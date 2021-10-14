<?php

namespace App\Services;

interface RoleService
{
    public function create(
        $name,
        $display_name,
        $description,
        $permissions
    );

    public function read($parameters = null);

    public function readRoles($withDefaultRole);

    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $inputtedPermissions
    );

    public function getAllPermissions();
}
