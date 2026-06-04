<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->current_role;

        $data = [
            'user' => $user,
            'currentRole' => $role,
            'availableRoles' => $user->available_roles,
        ];

        return view('dashboard.index', $data);
    }

    public function switchRole(Request $request)
    {
        $role = $request->input('role');
        $user = auth()->user();

        if ($user->switchRole($role)) {
            return redirect()->route('dashboard')->with('success', "Switched to {$role} role");
        }

        return redirect()->back()->with('error', 'Unable to switch role');
    }
}
