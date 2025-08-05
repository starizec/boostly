<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/chatwidget', function () {
    return view('layouts.app');
});

Route::get('/chat-widget', [ChatController::class, 'index'])->name('chat.widget');

Route::get('/verify', [ChatController::class, 'verifyDomain'])->name('verify.domain');

// Test widget page
Route::get('/test-widget', function () {
    return view('test-widget');
});

Route::get('/chatnewcontroller', [ChatController::class, 'showChat'])->name('chat.show');

// Chat Widget API Routes
Route::prefix('api/chat')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/messages/{chatId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/status/{chatId}', [ChatController::class, 'getChatStatus'])->name('chat.status');
});