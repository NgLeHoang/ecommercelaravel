<?php

namespace App\Traits;

use Intervention\Image\Laravel\Facades\Image;

trait ImageUploadTrait
{
    /**
     * Save an image to a specified folder with resizing.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $imageName
     * @param string $folderName
     * @param int $width
     * @param int $height
     * @return void
     */
    public function saveImageToFolder($image, $imageName, $folderName, $width, $height)
    {
        $destinationPath = public_path('uploads/' . $folderName);
        $img = Image::read($image->path());

        // Resize and cover image
        $img->cover($width, $height, 'top');
        $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }
}
