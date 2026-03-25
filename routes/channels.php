<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private chat channel - user can only join their own channel
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    // Verify user ID matches
    return (int) $user->id === (int) $userId;
});
