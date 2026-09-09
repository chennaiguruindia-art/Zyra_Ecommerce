<?php

namespace App\Http\Controllers;

use App\Support\ProductImageStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $file = ProductImageStorage::resolveOnDisk($path);

        if (!$file) {
            abort(404);
        }

        $baseRoots = [
            realpath(public_path('media')),
            realpath(public_path('uploads')),
            realpath(storage_path('app/public')),
            realpath(public_path('storage')),
            realpath(public_path()),
        ];

        $fileCheck = PHP_OS_FAMILY === 'Windows' ? strtolower($file) : $file;
        $allowed = false;
        foreach ($baseRoots as $base) {
            if (!$base) {
                continue;
            }
            $baseCheck = PHP_OS_FAMILY === 'Windows' ? strtolower($base) : $base;
            if (str_starts_with($fileCheck, rtrim($baseCheck, '\\/') . DIRECTORY_SEPARATOR) || $fileCheck === $baseCheck) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(404);
        }

        return response()->file($file);
    }
}
