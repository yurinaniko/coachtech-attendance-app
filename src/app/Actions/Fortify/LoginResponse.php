<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('attendance.index');
    }
}