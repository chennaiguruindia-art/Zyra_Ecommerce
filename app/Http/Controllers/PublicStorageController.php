<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $base = realpath(storage_path('app/public'));
        $file = realpath(storage_path('app/public/' . $path));

        $baseCheck = $base;
        $fileCheck = $file;
        if (PHP_OS_FAMILY === 'Windows') {
            $baseCheck = $base ? strtolower($base) : false;
            $fileCheck = $file ? strtolower($file) : false;
        }

        if (!$baseCheck || !$fileCheck || !str_starts_with($fileCheck, $baseCheck) || !is_file($file)) {
            abort(404);
        }

        return response()->file($file);
    }
}
