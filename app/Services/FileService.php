<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class FileService
{
    /**
     * Resize and save image, if present in request
     *
     * @param mixed $request            User request
     * @param string $entryUuid         Entry UUID
     * @param int $sideLength           Max side length (in pixels) to resize image
     * @return null|string              Relative path to saved file
     */
    public static function requestImgSave($request, $entryUuid, $sideLength = null)
    {
        if (!$request->hasFile('picture')) {
            return null;
        }
        $image = $request->file('picture');
        return FileService::imgSave($image, $entryUuid, $sideLength);
    }

    /**
     * Resize and save image
     *
     * @param file $image               User request
     * @param string $entryUuid         Entry UUID
     * @param int $sideLength           Max side length (in pixels) to resize image
     * @return null|string              Relative path to saved file
     */
    public static function imgSave($image, $entryUuid, $sideLength = null)
    {
        // If max side length (in pixels) wasn't provided, use default
        $sideLength = $sideLength ?? $_ENV['FUTURE_IMAGESIDE_DEF'];
        // Relative path (to store in DB) to file being saved
        $folderDbPath = 'images/' . date("Y") . '/' . date("m");
        $fileDbPath = $folderDbPath . '/' . $entryUuid . '-' . microtime(true) . '.' . $image->extension();
        // Relative path to actually save file
        $folderFsPath = public_path('storage/' . $folderDbPath);
        $fileFsPath = public_path('storage/' . $fileDbPath);
        // Create subfolder, if not exists
        if (!File::isDirectory($folderFsPath)) {
            File::makeDirectory($folderFsPath, 0777, true, true);
        }
        $img = Image::make($image->path());
        // Resize image, preserving aspect ratio, and without upscaling
        $img->resize($sideLength, $sideLength, function ($const) {
            $const->aspectRatio();
            $const->upsize();
        })->save($fileFsPath);
        return $fileDbPath;
    }

    /**
     * Get absolute path to image file
     *
     * @param string $fileDbPath        DB-stored relative file path, like:
     *                                  'images/2022/09/972ff9ce-e076-4c32-8d2f-1955437eefa6-1662323103.5752.jpg'
     * @return string                   Absolute path to saved file
     */
    public static function getImgFsPath($fileDbPath)
    {
        return public_path('storage/' . $fileDbPath);
    }
}
