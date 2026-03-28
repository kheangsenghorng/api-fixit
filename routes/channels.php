<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.notifications', function ($user) {
    \Log::info('=== BROADCAST AUTH ===', [
        'user_id'   => $user?->id,
        'user_role' => $user?->role,
        'user'      => $user,
    ]);

    if ($user === null) {
        \Log::warning('Broadcast auth FAILED: user is null');
        return false;
    }

    if ($user->role !== 'admin') {
        \Log::warning('Broadcast auth FAILED: role is ' . $user->role);
        return false;
    }

    return true;
}, ['guards' => ['api']]);

