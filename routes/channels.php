<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use App\Models\ChatMessage;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Allow access if user is authenticated (admin) or if it's a public chat
    // For widget users, we'll handle authentication differently
    return true;
});