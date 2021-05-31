<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// вход в логин
Route::post('/generateCodeLogin', [App\Http\Controllers\SmsController::class, 'generateCodeLogin']);
Route::post('/checkCodeLogin', [App\Http\Controllers\LoginmyController::class, 'checkCodeLogin']);
// выбор роли
Route::post('/checkRole', [App\Http\Controllers\LoginmyController::class, 'checkRole']);
// установка языка
Route::get('/setLng', [App\Http\Controllers\LocalizationController::class, 'index']);
Route::get('/', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::auth();
Route::get('/home', function () {
    return redirect(env('LOGIN_URL'));
});


