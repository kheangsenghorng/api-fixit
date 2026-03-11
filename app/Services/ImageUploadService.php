<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadService
{
    protected static function manager()
    {
        return new ImageManager(new Driver());
    }

    /**
     * Upload single image
     */
    public static function upload($file, $folder, $width = 1200)
    {
        $manager = self::manager();

        $image = $manager->read($file)->scale(width: $width);

        $filename = uniqid() . '.webp';

        $path = $folder . '/' . $filename;

        Storage::disk('public')->put(
            $path,
            $image->toWebp(90)
        );

        return $path;
    }

    /**
     * Upload multiple images
     */
    public static function uploadMultiple($files, $folder, $width = 1200)
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = self::upload($file, $folder, $width);
        }

        return $paths;
    }

    /**
     * Delete images
     */
    public static function delete($paths)
    {
        if (!$paths) return;

        foreach ((array) $paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}