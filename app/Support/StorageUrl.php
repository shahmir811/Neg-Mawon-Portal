<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    /**
     * Resolve a displayable URL for a file on the "public" disk, using a short-lived
     * signed URL when the disk supports it (S3, where the bucket blocks public access)
     * and falling back to a plain URL on disks that don't (local).
     */
    public static function for(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->providesTemporaryUrls()
            ? $disk->temporaryUrl($path, now()->addMinutes(30))
            : $disk->url($path);
    }
}
