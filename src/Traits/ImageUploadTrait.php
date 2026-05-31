<?php

namespace MuhammedSalama\Base\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

trait ImageUploadTrait
{
    /**
     * @param Request $request
     * @param string  $inputName
     * @param string  $path
     * @return string|void
     */
    public function uploadImage(Request $request, $inputName, $path)
    {
        if ($request->hasFile($inputName)) {
            $image     = $request->file($inputName);
            $extension = $image->getClientOriginalExtension();
            $imageName = 'media_' . uniqid() . '.' . $extension;

            $image->move(public_path($path), $imageName);

            return $path . '/' . $imageName;
        }
    }

    /**
     * @param Request $request
     * @param string  $inputName
     * @param string  $path
     * @return array|void
     */
    public function uploadMultiImage(Request $request, $inputName, $path)
    {
        $imagePaths = [];

        if ($request->hasFile($inputName)) {
            $images = $request->{$inputName};

            foreach ($images as $image) {
                $ext       = $image->getClientOriginalExtension();
                $imageName = 'media_' . uniqid() . '.' . $ext;

                $image->move(public_path($path), $imageName);

                $imagePaths[] = $path . '/' . $imageName;
            }

            return $imagePaths;
        }
    }

    /**
     * @param Request $request
     * @param string  $inputName
     * @param string  $path
     * @param string|null $oldPath
     * @return string|void
     */
    public function updateImage(Request $request, $inputName, $path, $oldPath = null)
    {
        if ($request->hasFile($inputName)) {
            /** Check File If Exists, then delete old one */
            if ($oldPath && File::exists(public_path($oldPath))) {
                File::delete(public_path($oldPath));
            }

            $image     = $request->file($inputName);
            $extension = $image->getClientOriginalExtension();
            $imageName = 'media_' . uniqid() . '.' . $extension;

            $image->move(public_path($path), $imageName);

            return $path . '/' . $imageName;
        }
    }

    /**
     * Handle delete file.
     *
     * @param string $path
     * @return void
     */
    public function deleteImage(string $path)
    {
        /** Check File If Exists, then delete */
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
