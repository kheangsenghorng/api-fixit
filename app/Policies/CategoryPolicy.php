<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * View all categories
     */
    public function viewAny(User $user)
    {
        return true; // any logged-in user
    }

    /**
     * View single category
     */
    public function view(User $user, Category $category)
    {
        return true;
    }

    /**
     * Create category
     */
    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    /**
     * Update category
     */
    public function update(User $user, Category $category)
    {
        return $user->role === 'admin';
    }

    /**
     * Delete category
     */
    public function delete(User $user, Category $category)
    {
        return $user->role === 'admin';
    }

    /**
     * Restore soft deleted category
     */
    public function restore(User $user, Category $category)
    {
        return $user->role === 'admin';
    }

    /**
     * Force delete category
     */
    public function forceDelete(User $user, Category $category)
    {
        return $user->role === 'admin';
    }
}