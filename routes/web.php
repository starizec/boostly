<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/chatwidget', function () {
    return view('layouts.app');
});

Route::get('/chat-widget', [ChatController::class, 'index'])->name('chat.widget');

Route::get('/verify', [ChatController::class, 'verifyDomain'])->name('verify.domain');