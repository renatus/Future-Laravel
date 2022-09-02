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
    public static function save($request, $entryUuid, $sideLength = null)
    {
        if (!$request->hasFile('picture')) {
            return null;
        }
        $image = $request->file('picture');
        // If max side length (in pixels) wasn't provided, use default
        $sideLength = $sideLength ?? $_ENV['FUTURE_IMAGESIDE_DEF'];
        // Relative path (to store in DB) to file being saved
        $folderDbPath = 'images/' . date("Y") . '/' . date("m");
        $fileDbPath = $folderDbPath . '/' . $entryUuid . '-' . time() . '.' . $image->extension();
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
}
