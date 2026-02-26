<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function reverse(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $lat = $data['lat'];
        $lng = $data['lng'];

        $url = "https://nominatim.openstreetmap.org/reverse";

        $res = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                // ✅ Nominatim requires a valid User-Agent (important)
                'User-Agent' => 'FixitAdmin/1.0 (contact: youremail@example.com)',
            ])
            ->get($url, [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'addressdetails' => 1,
            ]);

        if (! $res->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Reverse geocode failed',
                'errors' => ['nominatim' => [$res->body()]],
            ], 502);
        }

        $json = $res->json();

        return response()->json([
            'success' => true,
            'message' => 'Reverse geocode success',
            'data' => [
                'display_name' => $json['display_name'] ?? '',
                'lat' => $json['lat'] ?? $lat,
                'lng' => $json['lon'] ?? $lng,
                'raw' => $json, // optional (remove if you don’t want full data)
            ],
            'errors' => null,
        ]);
    }
}