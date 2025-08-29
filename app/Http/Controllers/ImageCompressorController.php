<?php

namespace App\Http\Controllers;

use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageCompressorController extends Controller
{
    public function __construct(private ImageCompressionService $compressionService)
    {
    }

    public function showForm()
    {
        return view('compressor');
    }

    public function compressWeb(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'max:10240',
                // Support common formats: png, webp, jpeg/jpg, heic, heif, avif, dng, tiff
                'mimes:png,webp,jpeg,jpg,heic,heif,avif,dng,tif,tiff'
            ],
            'quality' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $this->compressionService->compress($request->file('image'), $request->integer('quality'));
        $filename = $result['filename'];
        $mime = $result['mime'];
        $path = $result['path'];

        return response()->streamDownload(function () use ($path) {
            $stream = fopen($path, 'rb');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
            // Clean up after streaming
            @unlink($path);
        }, $filename, [
            'Content-Type' => $mime,
        ]);
    }

    public function compressApi(Request $request)
    {
        $request->validate([
            'image' => [
                'required',
                'file',
                'max:10240',
                'mimes:png,webp,jpeg,jpg,heic,heif,avif,dng,tif,tiff'
            ],
            'quality' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $result = $this->compressionService->compress($request->file('image'), $request->integer('quality'));

        // Return as file download if client accepts octet-stream, else JSON with base64
        if (str_contains((string) $request->header('Accept'), 'application/octet-stream')) {
            $filename = $result['filename'];
            $mime = $result['mime'];
            $path = $result['path'];
            return response()->streamDownload(function () use ($path) {
                $stream = fopen($path, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
                @unlink($path);
            }, $filename, [
                'Content-Type' => $mime,
            ]);
        }

        $data = file_get_contents($result['path']);
        @unlink($result['path']);

        return response()->json([
            'filename' => $result['filename'],
            'mime' => $result['mime'],
            'original_bytes' => $result['original_bytes'],
            'compressed_bytes' => $result['compressed_bytes'],
            'data_base64' => base64_encode($data),
        ]);
    }
}


