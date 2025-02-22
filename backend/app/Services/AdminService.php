<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function login(string $email, string $password)
    {
        $admin = Admin::where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            return null;
        }

        return $admin->createToken('admin-token')->plainTextToken;
    }
}