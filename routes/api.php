<?php

use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('feedback',[FeedbackController::class,'store']);
Route::get('questions/{slug}',[FeedbackController::class,'questions']);
Route::get('business',[FeedbackController::class,'business']);

// Telegram webhook — bot guruhga qo'shilganda chat_id ni avtomatik oladi.
Route::post('telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle']);