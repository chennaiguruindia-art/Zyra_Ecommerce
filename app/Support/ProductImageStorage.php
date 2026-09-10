<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Throwable;

class ProductImageStorage
{
    public static function store(mixed $source, string $directory = 'products', int $index = 0, ?string $originalName = null): ?string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory === '') {
            $directory = 'products';
        }

        if ($source instanceof UploadedFile) {
            if (!$source->isValid()) {
                return null;
            }

            $realPath = $source->getRealPath();
            if (!$realPath || !is_file($realPath) || is_dir($realPath)) {
                return null;
            }

            $filename = static::uniqueOriginalName(
                $source->getClientOriginalName() ?: $originalName,
                $directory,
                $index,
                $source->getClientOriginalExtension()
            );
            $relative = $directory . '/' . $filename;
            $binary = @file_get_contents($realPath);
            if ($binary === false || $binary === '' || !static::writePublicFile($relative, $binary)) {
                return null;
            }

            return $relative;
        }

        if (!is_string($source) || trim($source) === '') {
            return null;
        }

        $source = trim($source);
        if ($source === '/' || $source === '\\' || $source === '.' || $source === '..') {
            return null;
        }

        if (str_starts_with($source, 'blob:')) {
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
            if ($binary === false || $binary === '') {
                return null;
            }

            $filename = static::uniqueOriginalName($originalName, $directory, $index, $ext);
            $relative = $directory . '/' . $filename;
            if (!static::writePublicFile($relative, $binary)) {
                return null;
            }

            return $relative;
        }

        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            return $source;
        }

        $relative = ltrim(str_replace('\\', '/', $source), '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        if ($relative === '' || $relative === 'public' || is_dir(public_path($relative))) {
            return null;
        }

        return $relative;
    }

    public static function url(?string $path, string $placeholder = 'images/placeholder.jpg'): string
    {
        if (!$path || trim($path) === '' || trim($path) === '/') {
            return asset($placeholder);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = str_replace('\\', '/', ltrim($path, '/'));

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        foreach (['storage/', 'uploads/', 'media/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return url('/media/' . ltrim($path, '/'));
    }

    /**
     * Write into public/uploads (always web-accessible) and storage/app/public.
     */
    public static function writePublicFile(string $relative, string $binary): bool
    {
        $relative = str_replace('\\', '/', ltrim($relative, '/'));
        if ($relative === '' || $relative === '/' || $relative === 'public') {
            return false;
        }

        $written = false;

        foreach ([
            public_path('uploads/' . $relative),
            public_path('media/' . $relative),
            storage_path('app/public/' . $relative),
            public_path('storage/' . $relative),
        ] as $destination) {
            if (static::putFile($destination, $binary)) {
                $written = true;
            }
        }

        return $written;
    }

    public static function publish(string $path): void
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if ($path === '' || $path === '/' || $path === 'public' || str_contains($path, '..') || str_starts_with($path, 'http')) {
            return;
        }

        $source = storage_path('app/public/' . $path);
        if (!is_file($source) || is_dir($source)) {
            return;
        }

        $binary = @file_get_contents($source);
        if ($binary === false || $binary === '') {
            return;
        }

        static::writePublicFile($path, $binary);
    }

    public static function resolveOnDisk(string $path): ?string
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if ($path === '' || $path === '/' || $path === 'public' || str_contains($path, '..')) {
            return null;
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $candidates = [
            public_path('media/' . $path),
            public_path('uploads/' . $path),
            storage_path('app/public/' . $path),
            public_path('storage/' . $path),
            public_path($path),
        ];

        if (str_starts_with($path, 'uploads/')) {
            $without = substr($path, strlen('uploads/'));
            $candidates[] = public_path('uploads/' . $without);
            $candidates[] = storage_path('app/public/' . $without);
        }

        foreach ($candidates as $file) {
            $real = @realpath($file);
            if ($real && is_file($real) && !is_dir($real)) {
                return $real;
            }
        }

        return null;
    }

    private static function uniqueOriginalName(?string $original, string $directory, int $index, ?string $fallbackExt = 'jpg'): string
    {
        $original = $original ?: ('image_' . ($index + 1) . '.' . ($fallbackExt ?: 'jpg'));
        $original = basename(str_replace('\\', '/', $original));

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION) ?: ($fallbackExt ?: 'jpg'));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base) ?: 'image';
        $filename = $base . '.' . $ext;

        $n = 0;
        $candidate = $filename;
        while (static::fileExists($directory . '/' . $candidate)) {
            $n++;
            $candidate = $base . '_' . $n . '.' . $ext;
        }

        return $candidate;
    }

    private static function fileExists(string $relative): bool
    {
        if ($relative === '' || $relative === '/') {
            return false;
        }

        return is_file(public_path('uploads/' . $relative))
            || is_file(storage_path('app/public/' . $relative))
            || is_file(public_path('storage/' . $relative));
    }

    private static function putFile(string $destination, string $binary): bool
    {
        try {
            if (is_dir($destination)) {
                return false;
            }

            $directory = dirname($destination);
            if (!is_dir($directory) && !static::ensureDirectory($directory)) {
                return false;
            }

            return @file_put_contents($destination, $binary) !== false;
        } catch (Throwable) {
            return false;
        }
    }

    private static function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        try {
            return @mkdir($directory, 0755, true) || is_dir($directory);
        } catch (Throwable) {
            return is_dir($directory);
        }
    }
}

