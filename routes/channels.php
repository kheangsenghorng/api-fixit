<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ServiceBooking;


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




Broadcast::channel('service-booking.{bookingId}', function ($user, $bookingId) {
    $booking = ServiceBooking::find($bookingId);

    if (! $booking) {
        return false;
    }

    // customer who created booking
    if ((int) $booking->user_id === (int) $user->id) {
        return true;
    }

    // provider linked to this booking
    return $booking->bookingProviders()
        ->whereHas('provider', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->exists();
});

Broadcast::channel('owner.{ownerId}', function ($user, $ownerId) {
    $owner = \App\Models\Owner::where('user_id', $user->id)->first();

    return $owner && (int) $owner->id === (int) $ownerId;
});
