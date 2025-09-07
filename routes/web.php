<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AnalyticsController;

Route::post('/verify', [ChatController::class, 'verifyDomain'])->middleware('cors')->name('verify.domain');

// Chat Widget API Routes
Route::prefix('api/chat')->middleware('cors')->group(function () {
    Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
    Route::post('/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/messages/{chatId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/status/{chatId}', [ChatController::class, 'getChatStatus'])->name('chat.status');
    Route::post('/admin/message', [ChatController::class, 'sendAdminMessage'])->name('chat.admin.message');
});

// Analytics API Routes
Route::prefix('api/analytics')->middleware('cors')->group(function () {
    // General tracking endpoint
    Route::post('/track', [AnalyticsController::class, 'track'])->name('analytics.track');
    
    // Specific event tracking endpoints
    Route::post('/track/loaded', [AnalyticsController::class, 'trackLoaded'])->name('analytics.track.loaded');
    Route::post('/track/opened', [AnalyticsController::class, 'trackOpened'])->name('analytics.track.opened');
    Route::post('/track/action-clicked', [AnalyticsController::class, 'trackActionClicked'])->name('analytics.track.action_clicked');
    Route::post('/track/chat-clicked', [AnalyticsController::class, 'trackChatClicked'])->name('analytics.track.chat_clicked');
    Route::post('/track/chat-started', [AnalyticsController::class, 'trackChatStarted'])->name('analytics.track.chat_started');
    Route::post('/track/conversion', [AnalyticsController::class, 'trackConversion'])->name('analytics.track.conversion');
    
    // Analytics retrieval endpoints
    Route::get('/widget/{widgetId}', [AnalyticsController::class, 'getWidgetAnalytics'])->name('analytics.widget');
    Route::get('/widget/{widgetId}/summary', [AnalyticsController::class, 'getWidgetAnalyticsSummary'])->name('analytics.widget.summary');
});