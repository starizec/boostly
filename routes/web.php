<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::post('/verify', [ChatController::class, 'verifyDomain'])->middleware('cors')->name('verify.domain');

// Chat Widget API Routes
Route::prefix('api/chat')->middleware('cors')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/messages/{chatId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/status/{chatId}', [ChatController::class, 'getChatStatus'])->name('chat.status');
});