<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'business_name'  => $this->business_name,
            'address'        => $this->address,

            'lat'     => $this->lat !== null ? (float) $this->lat : null,
            'lng'     => $this->lng !== null ? (float) $this->lng : null,
            'map_url' => $this->map_url,

            'images' => is_array($this->images)
                ? collect($this->images)->map(fn ($img) => [
                    'url'  => asset('storage/' . $img),
                    'path' => $img,
                ])->toArray()
                : [],

            'logo' => $this->logo
                ? asset('storage/' . $this->logo)
                : null,

            'user' => new UserResource($this->whenLoaded('user')),

            'providers' => $this->relationLoaded('providers')
                ? $this->providers->map(fn ($provider) => [
                    'id'            => $provider->providerId,
                    'user_id'       => $provider->user_id,
                    'owner_id'      => $provider->owner_id,
                    'category_id'   => $provider->category_id,
                    'provider_type' => $provider->provider_type,
                    'status'        => $provider->status,

                    'user' => $provider->relationLoaded('user') && $provider->user
                        ? [
                            'id'        => $provider->user->id,
                            'name'      => $provider->user->name,
                            'email'     => $provider->user->email,
                            'phone'     => $provider->user->phone,
                            'role'      => $provider->user->role,
                            'avatar'    => $provider->user->avatar,
                            'is_active' => $provider->user->is_active,
                        ]
                        : null,

                    'category' => $provider->relationLoaded('category') && $provider->category
                        ? [
                            'id'       => $provider->category->id,
                            'name'     => $provider->category->name,
                            'icon'     => $provider->category->icon,
                            'icon_url' => $provider->category->icon
                                ? asset('storage/' . $provider->category->icon)
                                : null,
                        ]
                        : null,

                    'created_at' => $provider->created_at?->toDateTimeString(),
                    'updated_at' => $provider->updated_at?->toDateTimeString(),
                ])->values()
                : [],

            'status' => $this->final_status,
        ];
    }
}