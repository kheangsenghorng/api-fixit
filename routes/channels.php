<?php


use Illuminate\Support\Facades\Broadcast;

// Only admin can listen to this channel
Broadcast::channel('admin.notifications', function ($user) {
    return $user->role === 'admin'; // adjust to your role check
    // or: return $user->hasRole('admin');
    // or: return $user->is_admin === true;
});