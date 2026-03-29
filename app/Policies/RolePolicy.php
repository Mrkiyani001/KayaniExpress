<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function checkrole(User $user){
        if (!$user->hasRole(['Super Admin','Admin'])) {
            abort(403, 'You are not allowed to perform this action');
        }
        return true;
    }
}
