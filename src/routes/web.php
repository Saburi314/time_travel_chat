<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DebateController;
use App\Http\Controllers\Api\DebateApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ホーム画面
Route::get('/', [HomeController::class, 'index'])->name('home');
// 議論画面
Route::get('/debate', [DebateController::class, 'index'])->name('debate');