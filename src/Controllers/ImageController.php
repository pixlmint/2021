<?php

namespace Nacho\Controllers;

use Nacho\Helpers\ImageHelper;

class ImageController extends AbstractController
{
    public function uploadImage($request)
    {
        $now = new \DateTime();
        $baseFileName = $now->getTimestamp();
        $imagesDir = $_SERVER['DOCUMENT_ROOT'] . '/images/';
        $month = $_REQUEST['month'];
        $day = $_REQUEST['day'];

        if (!is_dir("${imagesDir}${month}/${day}")) {
            mkdir("${imagesDir}${month}/${day}", 0777, true);
        }

        $imgHelper = new ImageHelper();
        foreach ($_FILES as $file) {
            file_put_contents("${imagesDir}${month}/${day}/${baseFileName}" . $file['name'], file_get_contents($file['tmp_name']));
            foreach ($imgHelper->getDefaultSizes() as $size) {
                $imgHelper->compressImage("${imagesDir}${month}/${day}/${baseFileName}" . $file['name'], $size);
            }
        }

        return $this->json(['message' => 'uploaded files']);
    }

    public function loadImages($request)
    {
        $imagesDir = $_SERVER['DOCUMENT_ROOT'] . '/images/';
        $month = $_REQUEST['month'];
        $day = $_REQUEST['day'];

        if (!is_dir("${imagesDir}${month}/${day}")) {
            return $this->json(['message' => 'There are no images'], 404);
        }

        $images = [];
        foreach (scandir("${imagesDir}${month}/${day}") as $img) {
            if (is_dir("${imagesDir}${month}/${day}/${img}")) {
                continue;
            }
            array_push($images, "/images/${month}/${day}/${img}");
        }

        return $this->json(['message' => 'I should be loading your images now', 'images' => $images]);
    }
}
