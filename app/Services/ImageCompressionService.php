<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Recompress common image formats to reduce size.
     * - PNG: lossless recompress with level 9
     * - JPEG/JPG: lossy quality-based recompress (default Q=85, progressive)
     * - WEBP: lossy quality-based recompress (default Q=85) when supported
     *
     * Returns array with: path (temporary storage path), mime, original_bytes, compressed_bytes, filename
     */
    public function compress(UploadedFile $file, ?int $quality = null): array
    {
        $originalBytes = $file->getSize() ?? 0;
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $quality = $quality !== null ? max(1, min(100, $quality)) : 85;

        $tmpFilename = $this->generateTmpFilename($file->getClientOriginalName(), $extension);
        $tmpPath = 'tmp/'.Str::random(8).'/'.$tmpFilename;

        if (!Storage::disk('local')->exists(dirname($tmpPath))) {
            Storage::disk('local')->makeDirectory(dirname($tmpPath));
        }

        switch ($extension) {
            case 'png':
                $data = $this->recompressPng($file->getPathname());
                $mime = 'image/png';
                break;
            case 'jpg':
            case 'jpeg':
                $data = $this->recompressJpeg($file->getPathname(), $quality);
                $mime = 'image/jpeg';
                break;
            case 'webp':
                $data = $this->recompressWebp($file->getPathname(), $quality);
                $mime = 'image/webp';
                break;
            default:
                // Passthrough for other formats
                $data = file_get_contents($file->getPathname());
                break;
        }

        // Never return a file that is larger than original
        if ($originalBytes > 0 && strlen($data) > $originalBytes) {
            $data = file_get_contents($file->getPathname());
        }

        Storage::disk('local')->put($tmpPath, $data);

        return [
            'path' => Storage::disk('local')->path($tmpPath),
            'mime' => $mime,
            'original_bytes' => $originalBytes,
            'compressed_bytes' => strlen($data),
            'filename' => $tmpFilename,
        ];
    }

    protected function recompressPng(string $sourcePath): string
    {
        $image = imagecreatefrompng($sourcePath);
        if (!$image) {
            return file_get_contents($sourcePath);
        }

        imagesavealpha($image, true);
        // Use highest compression level 9 (lossless)
        ob_start();
        imagepng($image, null, 9, PNG_NO_FILTER);
        imagedestroy($image);
        return (string) ob_get_clean();
    }

    protected function recompressWebp(string $sourcePath, int $quality = 85): string
    {
        // Prefer GD imagewebp when available for lossy recompress
        if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
            $image = @imagecreatefromwebp($sourcePath);
            if ($image) {
                // Encode with quality
                ob_start();
                imagewebp($image, null, max(0, min(100, $quality)));
                imagedestroy($image);
                $data = (string) ob_get_clean();
                if ($data !== '') {
                    return $data;
                }
            }
        }

        // Fallback: passthrough
        return file_get_contents($sourcePath);
    }

    protected function recompressJpeg(string $sourcePath, int $quality = 85): string
    {
        $image = @imagecreatefromjpeg($sourcePath);
        if (!$image) {
            return file_get_contents($sourcePath);
        }

        // Progressive JPEG
        @imageinterlace($image, true);

        ob_start();
        imagejpeg($image, null, max(0, min(100, $quality)));
        imagedestroy($image);
        return (string) ob_get_clean();
    }

    protected function generateTmpFilename(string $originalName, string $extension): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $safe = Str::slug($base);
        if ($safe === '') {
            $safe = 'image';
        }
        return $safe.'-compressed.'.($extension ?: 'img');
    }
}



