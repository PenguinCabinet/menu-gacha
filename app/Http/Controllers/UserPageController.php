<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserPageController extends Controller
{
    public function show(string $name): View
    {
        $user = User::where('name', $name)->firstOrFail();

        return view('users.show', ['user' => $user]);
    }
}
