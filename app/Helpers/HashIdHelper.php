<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;

class HashIdHelper
{
    public static function encode($id)
    {
        return Hashids::encode($id);
    }

    public static function decode($hash)
    {
        $decoded = Hashids::decode($hash);

        return !empty($decoded) ? $decoded[0] : null;
    }

    public static function decodeMany(array $hashes): array
    {
        return collect($hashes)
            ->map(fn ($hash) => self::decode($hash))
            ->filter()
            ->values()
            ->toArray();
    }
}