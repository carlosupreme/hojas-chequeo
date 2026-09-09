<?php

namespace App\Services;

use finfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageService
{
    public function getAsBase64(?string $filePath): ?string
    {
        if (! $filePath || ! Storage::exists($filePath)) {
            return null;
        }

        $fileContent = Storage::get($filePath);
        $mimeType = Storage::mimeType($filePath);
        $base64 = base64_encode($fileContent);

        return "data:{$mimeType};base64,{$base64}";
    }

    public function storeBase64(string $folder, string $base64): string
    {
        // Remove data:<mime>;base64, prefix if present.
        // Use a broad capture so compound types like image/svg+xml are handled.
        if (preg_match('/^data:([^;]+);base64,/', $base64, $matches)) {
            $extension = match ($matches[1]) {
                'image/svg+xml' => 'svg',
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => explode('/', $matches[1])[1] ?? 'bin',
            };
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
