<?php

namespace App\Services;

use finfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageService
{
    public function getAsBase64(string $filePath): string
    {
        if (! Storage::exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $fileContent = Storage::get($filePath);
        $mimeType = Storage::mimeType($filePath);
        $base64 = base64_encode($fileContent);

        return "data:{$mimeType};base64,{$base64}";
    }

    public function storeBase64(string $folder, string $base64): string
    {
        // Remove data:image/png;base64, prefix if exists
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $extension = $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            // Try to detect extension from decoded data
            $extension = $this->detectExtensionFromBase64($base64);
        }

        // Decode base64
        $imageData = base64_decode($base64);

        if ($imageData === false) {
            throw new InvalidArgumentException('Invalid base64 string');
        }

        // Generate unique filename
        $filename = Str::uuid().'.'.$extension;
        $filePath = $folder.'/'.$filename;

        // Store file
        Storage::put($filePath, $imageData);

        return $filePath;
    }

    private function detectExtensionFromBase64(string $base64): string
    {
        $imageData = base64_decode($base64);

        if ($imageData === false) {
            throw new InvalidArgumentException('Invalid base64 string');
        }

        // Detect mime type from binary data
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->buffer($imageData);

        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => throw new InvalidArgumentException("Unsupported image type: {$mimeType}")
        };
    }
}
