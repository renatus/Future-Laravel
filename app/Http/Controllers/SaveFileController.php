<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class SaveFileController extends Controller
{
    /**
     * Resize and save image, if present
     *
     * @param mixed $request            User request
     * @param string $entryUuid         Entry UUID
     * @return null|string              Relative path to saved file
     */
    public static function saveFile($request, $entryUuid)
    {
        if (!$request->hasFile('picture')) {
            return null;
        }
        $image = $request->file('picture');
        // Relative path (to store in DB) to file being saved
        $folderDbPath = 'images/' . date("Y") . '/' . date("m");
        $fileDbPath = $folderDbPath . '/' . $entryUuid . '.' . $image->extension();
        // Relative path to actually save file
        $folderFsPath = public_path('storage/' . $folderDbPath);
        $fileFsPath = public_path('storage/' . $fileDbPath);
        // Create subfolder, if not exists
        if (!File::isDirectory($folderFsPath)) {
            File::makeDirectory($folderFsPath, 0777, true, true);
        }
        $img = Image::make($image->path());
        // TODO: Get resized image size from .env variable
        // Resize image, preserving aspect ratio, and without upscaling
        $img->resize(200, 200, function ($const) {
            $const->aspectRatio();
            $const->upsize();
        })->save($fileFsPath);
        return $fileDbPath;
    }
}
