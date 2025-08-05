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

Route::get('/chatnew', function () {
    // For testing purposes, create or get a sample user and contact
    $user = \App\Models\User::first();
    $contact = \App\Models\Contact::first();
    
    // If no user exists, create a sample one
    if (!$user) {
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
    }
    
    // If no contact exists, create a sample one
    if (!$contact) {
        $contact = \App\Models\Contact::create([
            'name' => 'Support Agent',
            'email' => 'support@example.com',
            'phone' => '+1234567890'
        ]);
    }
    
    // Debug output
    \Log::info('User data:', $user->toArray());
    \Log::info('Contact data:', $contact->toArray());
    
    return view('chatnew', [
        'friend' => $contact,
        'currentUser' => $user
    ]);
});

// Chat Widget API Routes
Route::prefix('api/chat')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/messages/{chatId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/status/{chatId}', [ChatController::class, 'getChatStatus'])->name('chat.status');
});