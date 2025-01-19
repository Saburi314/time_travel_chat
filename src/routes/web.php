<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebateController;

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
Route::get('/', [DebateController::class, 'home']);

// 議論画面
Route::get('/debate', [DebateController::class, 'debate']);