<?php

namespace Nacho\Helpers;

class ImageHelper
{
    private array $defaultSizes = [100, 500, 1080];

    public function getDefaultSizes(): array
    {
        return $this->defaultSizes;
    }

    public function storeEntryImage(string $imagePath, ?string $month = null, ?string $day = null)
    {
        $now = new \DateTime();
        if (!$month) {
            $month = $now->format('F');
        }
        if (!$day) {
            $day = $now->format('d.m.Y');
        }
        $fileName = explode('/', $imagePath);
        $fileName = array_pop($fileName);
        if (!endswith($fileName, '.jpg')) {
            $fileName .= '.jpg';
        }
        $baseFileName = $now->getTimestamp();
        $imagesDir = $_SERVER['DOCUMENT_ROOT'] . '/images/';
        if (!is_dir("${imagesDir}${month}/${day}")) {
            mkdir("${imagesDir}${month}/${day}", 0777, true);
        }
        $uploadedFiles = [];
        file_put_contents("${imagesDir}${month}/${day}/${baseFileName}_${fileName}", file_get_contents($imagePath));
        foreach ($this->getDefaultSizes() as $size) {
            $this->compressImage("${imagesDir}${month}/${day}/${baseFileName}_${fileName}", $size);
            array_push($uploadedFiles, "/images/${month}/${day}/${size}/${baseFileName}_${fileName}");
        }

        return $uploadedFiles;
    }

    public function compressImage(string $imagePath, int $size, string $targetPath = '', string $fileName = '')
    {
        if (!$targetPath) {
            // Find out path of original image
            $splImgPath = explode('/', $imagePath);
            array_pop($splImgPath);
            $targetPath = implode('/', $splImgPath) . "/${size}";
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
