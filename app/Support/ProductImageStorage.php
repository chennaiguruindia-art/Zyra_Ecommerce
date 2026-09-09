<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageStorage
{
    public static function store(mixed $source, string $directory = 'products', int $index = 0): ?string
    {
        if ($source instanceof UploadedFile) {
            $path = $source->store($directory, 'public');
            if (!$path) {
                return null;
            }

            static::publish($path);

            return $path;
        }

        if (!is_string($source) || $source === '') {
            return null;
        }

        if (str_starts_with($source, 'data:image')) {
            if (!preg_match('/^data:image\/(\w+);base64,/', $source, $matches)) {
                return null;
            }

            $ext = strtolower($matches[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }

            $binary = base64_decode(substr($source, strpos($source, ',') + 1));
            if ($binary === false) {
                return null;
            }

            $path = $directory . '/' . Str::uuid() . '_' . $index . '.' . $ext;
            if (!Storage::disk('public')->put($path, $binary)) {
                return null;
            }

            static::publish($path);

            return $path;
        }

        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            return $source;
        }

        $relative = ltrim($source, '/');
        static::publish($relative);

        return $relative;
    }

    /**
     * Copy a storage/app/public file into public/storage so Windows/Plesk
     * can serve it without a working storage symlink.
     */
    public static function publish(string $path): void
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, 'http')) {
            return;
        }

        $source = storage_path('app/public/' . $path);
        if (!is_file($source)) {
            return;
        }

        $destination = public_path('storage/' . $path);
        $sourceReal = realpath($source);
        $destReal = realpath($destination);

        if ($sourceReal && $destReal && $sourceReal === $destReal) {
            return;
        }

        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return;
        }

        copy($source, $destination);
    }
}
