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


// AIとのやり取り関連（セッションやCsrfを利用している為、webルートに記載）
Route::post('/ai-response', [DebateApiController::class, 'getAiResponse'])->middleware('web');
Route::get('/get-chat-history', [DebateApiController::class, 'getChatHistory'])->middleware('web');
Route::post('/delete-chat', [DebateApiController::class, 'deleteChatHistory'])->middleware('web');
