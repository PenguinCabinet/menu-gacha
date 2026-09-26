<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MenuGachaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $menuGachas = $request->user()->menuGachas()->latest('created_at')->get();

        return view('dashboard', ['menuGachas' => $menuGachas]);
    })->name('dashboard');

    Route::post('/menu', [MenuGachaController::class, 'store'])->name('menu-gachas.store');
    Route::get('/menu/{id}', [MenuGachaController::class, 'show'])
        ->whereNumber('id')
        ->name('menu-gachas.show');
    Route::patch('/menu/{id}', [MenuGachaController::class, 'update'])
        ->whereNumber('id')
        ->name('menu-gachas.update');
    Route::post('/menu/{id}/items', [MenuGachaController::class, 'storeItem'])
        ->whereNumber('id')
        ->name('menu-gachas.items.store');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
