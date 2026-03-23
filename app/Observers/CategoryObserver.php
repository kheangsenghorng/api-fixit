<?php

namespace App\Observers;

use App\Events\CategoryChanged;
use App\Models\Category;

class CategoryObserver
{
    public function created(Category $category): void
    {
        broadcast(CategoryChanged::fromModel('created', $category))->toOthers();
    }

    public function updated(Category $category): void
    {
        broadcast(CategoryChanged::fromModel('updated', $category))->toOthers();
    }

    public function deleted(Category $category): void
    {
        broadcast(CategoryChanged::fromModel('deleted', $category))->toOthers();
    }

    public function restored(Category $category): void
    {
        broadcast(CategoryChanged::fromModel('restored', $category))->toOthers();
    }

    public function forceDeleted(Category $category): void
    {
        broadcast(CategoryChanged::fromModel('force_deleted', $category))->toOthers();
    }
}