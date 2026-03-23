<?php

namespace App\Observers;

use App\Events\TypeChanged;
use App\Models\Type;

class TypeObserver
{
    public function created(Type $type): void
    {
        broadcast(new TypeChanged(
            'created',
            $type->fresh()->load('category')
        ));
    }

    public function updated(Type $type): void
    {
        broadcast(new TypeChanged(
            'updated',
            $type->fresh()->load('category')
        ));
    }

    public function deleted(Type $type): void
    {
        broadcast(new TypeChanged(
            'deleted',
            null,
            $type->id
        ));
    }
}