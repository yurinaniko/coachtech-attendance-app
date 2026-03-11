<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function list()
    {
        $users = User::orderBy('id')->get();

        return view('admin.staff.list', compact('users'));
    }
}
