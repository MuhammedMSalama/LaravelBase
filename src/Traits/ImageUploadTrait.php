<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    public function uploadImage(Request $request, string $inputName, string $path): ?string
    {
        if (! $request->hasFile($inputName)) {
            return null;
        }

        $image = $request->file($inputName);
        if (! $image instanceof UploadedFile) {
            return null;
        }

        $imageName = 'media_'.Str::random(32).'.'.$image->extension();
        $image->move(public_path($path), $imageName);

        return $path.'/'.$imageName;
    }

    /**
     * @return array<int, string>
     */
    public function uploadMultiImage(Request $request, string $inputName, string $path): array
    {
        $paths = [];

        if (! $request->hasFile($inputName)) {
            return $paths;
        }

        foreach ((array) $request->file($inputName) as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $imageName = 'media_'.Str::random(32).'.'.$image->extension();
            $image->move(public_path($path), $imageName);

            $paths[] = $path.'/'.$imageName;
        }

        return $paths;
    }

    public function updateImage(Request $request, string $inputName, string $path, ?string $oldPath = null): ?string
    {
        if (! $request->hasFile($inputName)) {
            return null;
        }

        if ($oldPath !== null && File::exists(public_path($oldPath))) {
            File::delete(public_path($oldPath));
        }

        $image = $request->file($inputName);
        if (! $image instanceof UploadedFile) {
            return null;
        }

        $imageName = 'media_'.Str::random(32).'.'.$image->extension();
        $image->move(public_path($path), $imageName);

        return $path.'/'.$imageName;
    }

    public function deleteImage(string $path): void
    {
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
