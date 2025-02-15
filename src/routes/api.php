<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DebateApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/ai-response', [DebateApiController::class, 'getAiResponse']);
Route::get('/get-chat-history', [DebateApiController::class, 'getChatHistory']);
Route::post('/delete-chat', [DebateApiController::class, 'deleteChatHistory']);
