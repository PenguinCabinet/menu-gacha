<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserPageController extends Controller
{
    public function show(string $name): View
    {
        $user = User::where('name', $name)->firstOrFail();
        $menuGachas = $user->menuGachas()
            ->where('is_published', true)
            ->latest('created_at')
            ->get();

        return view('users.show', ['user' => $user, 'menuGachas' => $menuGachas]);
    }
}
