<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuGachaController;
use App\Http\Controllers\MenuGachaFlagController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\UserPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/og/site.svg', [OgImageController::class, 'site'])->name('og.site');
Route::get('/og/menu/{id}.svg', [OgImageController::class, 'menu'])->whereNumber('id')->name('og.menu');
Route::get('/og/user/{name}.svg', [OgImageController::class, 'user'])->name('og.user');

Route::get('/menu/{id}', [MenuGachaController::class, 'show'])
    ->whereNumber('id')
    ->name('menu-gachas.show');

Route::get('/u/{name}', [UserPageController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $menuGachas = $request->user()->menuGachas()->latest('created_at')->get();

        return view('dashboard', ['menuGachas' => $menuGachas]);
    })->name('dashboard');

    Route::post('/menu', [MenuGachaController::class, 'store'])->name('menu-gachas.store');
    Route::patch('/menu/{id}', [MenuGachaController::class, 'update'])
        ->whereNumber('id')
        ->name('menu-gachas.update');
    Route::post('/menu/{id}/items', [MenuGachaController::class, 'storeItem'])
        ->whereNumber('id')
        ->name('menu-gachas.items.store');
    Route::delete('/menu/{id}/items/{itemId}', [MenuGachaController::class, 'destroyItem'])
        ->whereNumber('id')
        ->whereNumber('itemId')
        ->name('menu-gachas.items.destroy');
    Route::post('/menu/{id}/flags', [MenuGachaFlagController::class, 'store'])
        ->whereNumber('id')
        ->name('menu-gachas.flags.store');
    Route::patch('/menu/{id}/flags/{flagId}', [MenuGachaFlagController::class, 'update'])
        ->whereNumber('id')
        ->whereNumber('flagId')
        ->name('menu-gachas.flags.update');
    Route::delete('/menu/{id}/flags/{flagId}', [MenuGachaFlagController::class, 'destroy'])
        ->whereNumber('id')
        ->whereNumber('flagId')
        ->name('menu-gachas.flags.destroy');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
