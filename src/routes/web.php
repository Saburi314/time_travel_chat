<?php

use Illuminate\Support\Facades\Route;
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
Route::get('/', function () {
    return view('home');
});

// 議論画面
Route::get('/debate', function () {
    return view('debate');
});

// APIルート
Route::post('/ai-response', [DebateApiController::class, 'getAiResponse'])->middleware('web');
Route::get('/get-chat-history', [DebateApiController::class, 'getChatHistory'])->middleware('web');
Route::post('/reset-chat', [DebateApiController::class, 'resetChatHistory'])->middleware('web');
