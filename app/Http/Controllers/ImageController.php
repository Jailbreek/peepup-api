<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\FlareClient\Http\Exceptions\NotFound;

class ImageController extends Controller
{

    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|string',
            'image_type' => 'required|string'
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $base64ImageData = $request->input('data');

        $base64Data = explode(',', $base64ImageData, 2)[1];


        $binaryImageData = base64_decode($base64Data);


        $ext = $request->input('image_type');
        $filename = $this->generateRandomFilename('peepup', $ext);


        try {
            Storage::disk('public')->put($filename, $binaryImageData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save image to storage'], 500);
        }

        return response()->json([
            'data'=>
            ['message' => 'Image uploaded successfully',
            'filename' => $filename,
            'url' => asset("storage/{$filename}")],
        ]);
    }

    private function detectFileExtension(string $mediaType): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $extension = "";

        if ($finfo) {
            $mimeType = finfo_buffer($finfo, $mediaType);

            // Map common MIME types to file extensions
            $mimeToExtension = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
                'image/jpg' => 'jpg'

            ];

            $extension = $mimeToExtension[$mimeType] ?? null;

            finfo_close($finfo);
        }

        return $extension;
    }

    private function generateRandomFilename(string $prefix, string $extension): string
    {
        // Combine prefix, a unique identifier, and the original extension
        $uniqueIdentifier = uniqid();
        $filename = "{$prefix}_{$uniqueIdentifier}.{$extension}";

        return $filename;
    }
}

