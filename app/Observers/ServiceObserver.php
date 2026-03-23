<?php

namespace App\Observers;

use App\Events\ServiceChanged;
use App\Models\Service;

class ServiceObserver
{
    public function created(Service $service): void
    {
        broadcast(new ServiceChanged(
            'created',
            $service->fresh()->load(['owner', 'category', 'type'])
        ));
    }

    public function updated(Service $service): void
    {
        broadcast(new ServiceChanged(
            'updated',
            $service->fresh()->load(['owner', 'category', 'type'])
        ));
    }

    public function deleted(Service $service): void
    {
        broadcast(new ServiceChanged('deleted', null, $service->id));
    }
}