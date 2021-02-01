<?php

namespace Nacho\Helpers;

class ImageHelper
{
    private array $defaultSizes = [100, 500, 1080];

    public function getDefaultSizes(): array
    {
        return $this->defaultSizes;
    }

    public function compressImage(string $imagePath, int $size, string $targetPath = '', string $fileName = '')
    {
        if (!$targetPath) {
            // Find out path of original image
            $originalImgPath = '';
            $splImgPath = explode('/', $imagePath);
            $originalImgPath = implode('/', $splImgPath);
            $targetPath = "${originalImgPath}/${size}";
        }
        if (!$fileName) {
            $splImgPath = explode('/', $imagePath);
            $fileName = array_pop($splImgPath);
        }

        // Scale down image
        $imgObject = imagecreatefromstring(file_get_contents($imagePath));
        $scaled = imagescale($imgObject, $size);

        // Create new path if it does not exist yet
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        // Save scaled down version in new path
        imagejpeg($scaled, "${targetPath}/${fileName}");

        return "${targetPath}/${fileName}";
    }

    public static function base64_to_jpeg($base64_string)
    {
        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode(',', $base64_string);
         
        return base64_decode($data[1]);
    }
}
