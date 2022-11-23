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

Route::get('.well-known/webfinger', [\App\Http\Controllers\WellKnown\WebfingerController::class, 'index']);

Route::get('users/{handle}', [\App\Http\Controllers\UserController::class, 'show']);

Route::get('users/{username}/{publicId}', [\App\Http\Controllers\UserPostController::class, 'show']);


