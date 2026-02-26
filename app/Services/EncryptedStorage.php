<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class EncryptedStorage
{
    public static function putEncrypted(string $disk, string $path, string $rawBytes): void
    {
        // Encrypt raw bytes (binary-safe by base64 encoding)
        $payload = base64_encode(string: $rawBytes);
        $encrypted = Crypt::encryptString($payload);

        Storage::disk($disk)->put($path, $encrypted);
    }

    public static function getDecrypted(string $disk, string $path): string
    {
        $encrypted = Storage::disk($disk)->get($path);
        $payload = Crypt::decryptString($encrypted);

        return base64_decode($payload);
    }

    public static function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }
}