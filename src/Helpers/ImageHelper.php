<?php

namespace Nacho\Helpers;

class ImageHelper
{
    private array $defaultSizes = [100, 500, 1080];

    public function getDefaultSizes(): array
    {
        return $this->defaultSizes;
    }

    public function compressImage(string $imagePath, int $size)
    {
        // Find out path of original image
        $originalImgPath = '';
        $splImgPath = explode('/', $imagePath);
        $fileName = array_pop($splImgPath);
        $originalImgPath = implode('/', $splImgPath);

        // Scale down image
        $imgObject = imagecreatefromstring(file_get_contents($imagePath));
        $scaled = imagescale($imgObject, $size);

        // Create new path if it does not exist yet
        if (!is_dir("${originalImgPath}/${size}")) {
            mkdir("${originalImgPath}/${size}", 0777, true);
        }

        // Save scaled down version in new path
        imagejpeg($scaled, "${originalImgPath}/${size}/${fileName}");

        return "${originalImgPath}/${size}/${fileName}";
    }
}
