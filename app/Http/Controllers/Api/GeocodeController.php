<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeocodeController extends Controller
{
    public function reverse(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required','numeric'],
            'lng' => ['required','numeric'],
        ]);

        $lat = $data['lat'];
        $lng = $data['lng'];

        $cacheKey = "reverse_{$lat}_{$lng}";

        $result = Cache::remember($cacheKey, 86400, function () use ($lat,$lng) {

            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'FixitApp/1.0 (contact@fixit.com)',
                    'Accept-Language' => 'en'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse',[
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng
                ]);

            if(!$response->ok()){
                return null;
            }

            return $response->json();
        });

        if(!$result){
            return response()->json([
                'success'=>false,
                'message'=>'Reverse geocode failed'
            ],502);
        }

        return response()->json([
            'success'=>true,
            'data'=>[
                'display_name'=>$result['display_name'] ?? '',
                'lat'=>$result['lat'] ?? $lat,
                'lng'=>$result['lon'] ?? $lng
            ]
        ]);
    }
}