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
            return $source->store($directory, 'public');
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
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            return $source;
        }

        return ltrim($source, '/');
    }
}
