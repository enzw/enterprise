<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function checkRole(...$roles)
    {
        if (!in_array(auth()->user()->current_role, $roles)) {
            abort(403, 'Unauthorized access to this action');
        }
    }

    protected function getCurrentRole()
    {
        return auth()->user()->current_role;
    }

    protected function getAvailableRoles()
    {
        return auth()->user()->available_roles;
    }
}
