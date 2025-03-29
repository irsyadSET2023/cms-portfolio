<?php

namespace App\Services\ImageUpload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Illuminate\Support\Str;
class UploadImageService
{
    public function __construct() {}

    public function uploadImage(UploadedFile $image,string $fileSystem='s3', string $path, string $filename,bool $isPublic=false)
    {
        try {
        $originalPath = $image->getPathname();

        // Step 1: Convert and compress using GD Library (native in PHP)
        $resizedPath = storage_path('app/temp/'.uniqid().'.jpg');
        $this->compressImageGD($originalPath, $resizedPath, 60); // Initial compression (JPEG quality: 60%)

        // Step 2: Optimize the resized image with Spatie
        ImageOptimizer::optimize($resizedPath);

        // Step 3: Ensure file size is under 100KB
        while (filesize($resizedPath) > 100000) { // 100KB limit
            $this->compressImageGD($resizedPath, $resizedPath, 40); // Lower quality (JPEG: 40%)
            ImageOptimizer::optimize($resizedPath);
        }

        $generatedUlid = Str::ulid();

        // Step 4: Store the final optimized image
        $finalFilename = $path . '/'.$filename.'_'.$generatedUlid.'.jpg';
        $options = $isPublic ? ['visibility' => 'public'] : [];
        Storage::disk($fileSystem)->put($finalFilename, file_get_contents($resizedPath), $options);

        // Cleanup temp file
        unlink($resizedPath);

        return [
            'success' => true,
            'message' => 'Image successfully uploaded and compressed!',
            'original_size' => round($image->getSize() / 1024, 2).' KB',
            'compressed_size' => round(Storage::disk($fileSystem)->size($finalFilename) / 1024, 2).' KB',
            'path' =>
            $fileSystem == 's3' ?
            env('AWS_URL') . '/' . $finalFilename :
            Storage::url($finalFilename),
        ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Image upload failed',
                'error' => $th->getMessage(),
            ];
        }
     
    }

    private function compressImageGD($sourcePath, $destinationPath, $quality)
    {
        $imageInfo = getimagesize($sourcePath);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                imagepalettetotruecolor($image); // Convert PNG to true color to allow JPEG conversion
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false; // Unsupported format
        }

        // Resize image to max width 800px while keeping aspect ratio
        $maxWidth = 800;
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width > $maxWidth) {
            $newHeight = intval(($maxWidth / $width) * $height);
            $resizedImage = imagecreatetruecolor($maxWidth, $newHeight);
            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resizedImage;
        }

        // ✅ Ensure the directory exists before saving
        $directory = dirname($destinationPath);
        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Save as compressed JPEG
        imagejpeg($image, $destinationPath, $quality);
        imagedestroy($image);
    }
}
