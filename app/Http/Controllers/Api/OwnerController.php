<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Owner;
use Illuminate\Http\Request;
use App\Http\Resources\OwnerResource;
use App\Http\Requests\OwnerStoreRequest;
use App\Http\Requests\OwnerUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class OwnerController extends BaseController
{
    /**
     * GET /api/owners
     *
     * Description:
     * Returns paginated list of owners with optional filters.
     *
     * Filters:
     * - search
     * - user_id
     * - created_date
     * - created_from
     * - created_to
     * - created_hour
     * - updated_date
     * - updated_from
     * - updated_to
     * - last_days
     * - this_month
     * - page
     * - per_page
     */

     public function index(Request $request)
     {
         $owners = Owner::with('user')
     
             // search by business_name
             ->when($request->filled('search'), fn ($q) =>
                 $q->where('business_name', 'like', "%{$request->search}%")
             )
     
             // ✅ filter by address (LIKE)
             ->when($request->filled('address'), fn ($q) =>
                 $q->where('address', 'like', "%{$request->address}%")
             )
     
             // ✅ filter by address exact match (optional)
             ->when($request->filled('address_exact'), fn ($q) =>
                 $q->where('address', $request->address_exact)
             )
     
             // filter by user
             ->when($request->filled('user_id'), fn ($q) =>
                 $q->where('user_id', $request->user_id)
             )
     
             // specific created date
             ->when($request->filled('created_date'), fn ($q) =>
                 $q->whereDate('created_at', $request->created_date)
             )
     
             // created date range
             ->when($request->filled('created_from') && $request->filled('created_to'), fn ($q) =>
                 $q->whereBetween('created_at', [
                     $request->created_from . ' 00:00:00',
                     $request->created_to   . ' 23:59:59',
                 ])
             )
     
             // created hour (0-23)
             ->when($request->filled('created_hour'), fn ($q) =>
                 $q->whereRaw('HOUR(created_at) = ?', [(int) $request->created_hour])
             )
     
             // specific updated date
             ->when($request->filled('updated_date'), fn ($q) =>
                 $q->whereDate('updated_at', $request->updated_date)
             )
     
             // updated date range
             ->when($request->filled('updated_from') && $request->filled('updated_to'), fn ($q) =>
                 $q->whereBetween('updated_at', [
                     $request->updated_from . ' 00:00:00',
                     $request->updated_to   . ' 23:59:59',
                 ])
             )
     
             // last N days
             ->when($request->filled('last_days'), fn ($q) =>
                 $q->where('created_at', '>=', now()->subDays((int) $request->last_days))
             )
     
             // this month
             ->when($request->this_month === 'true', fn ($q) =>
                 $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
             )
     
             ->latest()
             ->paginate($request->integer('per_page', 10));
     
         return $this->paginate(
             $owners,
             OwnerResource::class,
             'Owners retrieved successfully'
         );
     }
     
    
    /**
     * Store a newly created owner
     */
    public function store(OwnerStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
    
            $data = $request->validated();
    
            $uploaded = false;
    
            if ($request->hasFile('images')) {
                $data['images'] = $this->uploadImages($request);
                $uploaded = true;
            }
    
            if ($request->hasFile('logo')) {
                $data['logo'] = $this->uploadLogo($request);
                $uploaded = true;
            }
    
            // 🔥 If image/logo uploaded → status completed
            $data['status'] = $uploaded ? 'completed' : 'pending';
    
            $owner = Owner::create($data)->load('user');
    
            return $this->success(
                new OwnerResource($owner),
                'Owner created successfully',
                201
            );
        });
    }
    

    /**
     * Display the specified owner
     */
    public function show(Owner $owner)
    {
        $owner->load('user');

        return $this->success(
            new OwnerResource($owner),
            'Owner retrieved successfully'
        );
    }

    /**
     * Update the specified owner
     */
    public function update(OwnerUpdateRequest $request, Owner $owner)
    {
        return DB::transaction(function () use ($request, $owner) {
    
            $data = $request->validated();
    
            $uploaded = false;
    
            // Update Images
            if ($request->hasFile('images')) {
    
                $newImages = $this->uploadImages($request);
                $this->deleteImages($owner->images);
    
                $data['images'] = $newImages;
                $uploaded = true;
            }
    
            // Update Logo
            if ($request->hasFile('logo')) {
    
                $newLogo = $this->uploadLogo($request);
                $this->deleteLogo($owner->logo);
    
                $data['logo'] = $newLogo;
                $uploaded = true;
            }
    
            // 🔥 If uploaded → mark completed
            if ($uploaded) {
                $data['status'] = 'completed';
            }
    
            $owner->update($data);
            $owner->load('user');
    
            return $this->success(
                new OwnerResource($owner),
                'Owner updated successfully'
            );
        });
    }
    
    /**
     * Remove the specified owner
     */
    public function destroy(Owner $owner)
    {
        if ($owner->status === 'approved') {
            return $this->error(
                'Approved owner cannot be deleted.',
                null,
                403
            );
        }

        return DB::transaction(function () use ($owner) {

            $this->deleteImages($owner->images);
            $this->deleteLogo($owner->logo);

            $owner->delete();

            return $this->success(
                null,
                'Owner deleted successfully'
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | File Helpers
    |--------------------------------------------------------------------------
    */

    private function uploadImages($request)
    {
        return collect($request->file('images'))
            ->map(fn ($file) => $file->store('owners/images', 'public'))
            ->toArray();
    }

    private function uploadLogo($request)
    {
        return $request->file('logo')
            ->store('owners/logo', 'public');
    }

    private function deleteImages($images)
    {
        if (is_array($images)) {
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
    }

    private function deleteLogo($logo)
    {
        if ($logo) {
            Storage::disk('public')->delete($logo);
        }
    }
}
