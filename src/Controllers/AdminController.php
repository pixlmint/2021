<?php

namespace App\Controllers;

use DateTime;
use App\Helpers\ImageHelper;
use App\Helpers\NavRenderer;
use Nacho\Controllers\AbstractController;
use Nacho\Nacho;

class AdminController extends AbstractController
{
    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        if (!$this->isGranted('Editor')) {
            header('Http/1.1 302');
            header('Location: /login?required_page=' . $_SERVER['REDIRECT_URL']);
            die();
        }
    }

    function index($request)
    {
        return $this->render('admin/admin.twig');
    }

    public function delete($request)
    {
        function returnHome()
        {
            header('Location: /admin');
            header('HTTP/1.1 302');
        }
        if (key_exists('file', $_REQUEST)) {
            $file = $_REQUEST['file'];
        } elseif (key_exists('dir', $_REQUEST)) {
            $file = $_REQUEST['dir'];
        } else {
            returnHome();
        }
        if (
            substr($file, 0, strlen($_SERVER['DOCUMENT_ROOT'])) !==
            $_SERVER['DOCUMENT_ROOT']
        ) {
            returnHome();
        }

        function rmdirRecursive($dir)
        {
            foreach (scandir($dir) as $sub) {
                if ($sub !== '.' && $sub !== '..') {
                    $newDir = $dir . '/' . $sub;
                    if (is_file($newDir)) {
                        echo "deleting ${dir}<br>";
                        unlink($newDir);
                    } elseif (is_dir($newDir)) {
                        rmdirRecursive($newDir);
                    }
                }
            }
            rmdir($dir);
        }

        if (is_file($file)) {
            echo "deleting ${file}<br>";
            unlink($file);
        } elseif (is_dir($file)) {
            rmdirRecursive($file);
        }
        returnHome();
    }

    public function editHome($request)
    {
        $imagesDir = $request->documentRoot . '/images/home';
        $fileNames = ['original', 'cover', 'banner'];
        $images = [];
        $cropData = [];
        foreach (MONTHS as $key => $month) {
            $tmpImages = [];
            if (!is_dir("${imagesDir}/${key}_${month}")) {
                mkdir("${imagesDir}/${key}_${month}", 0777, true);
                file_put_contents("${imagesDir}/${key}_${month}/.gitignore", "*.jpg\n*.json");
                file_put_contents("${imagesDir}/${key}_${month}/crop-data.json", "{}");
            }
            foreach ($fileNames as $name) {
                if (is_file("${imagesDir}/${key}_${month}/${name}.jpg")) {
                    $tmpImages[$name] = "/images/home/${key}_${month}/${name}.jpg";
                }
            }
            $images[$month] = $tmpImages;
            $cropData[$month] = file_get_contents("${imagesDir}/${key}_${month}/crop-data.json");
        }

        // if this is a post request, store new files
        if (strtoupper($request->requestMethod) === 'POST') {
            $imageHelper = new ImageHelper();
            $month = $_REQUEST['month'];
            $key = array_search($month, MONTHS);
            $cover = $_REQUEST['cover'];
            $banner = $_REQUEST['banner'];
            $cropCover = json_decode($_REQUEST['cropCover'], true);
            $cropBanner = json_decode($_REQUEST['cropBanner'], true);
            $cropData = ['cover' => $cropCover, 'banner' => $cropBanner];
            file_put_contents("${imagesDir}/${key}_${month}/original.jpg", file_get_contents("${imagesDir}/${key}_${month}/tmp.original.jpg"));
            file_put_contents("${imagesDir}/${key}_${month}/crop-data.json", json_encode($cropData));
            $coverName = md5(random_bytes(10));
            $bannerName = md5(random_bytes(10));
            file_put_contents("/tmp/${coverName}.jpeg", ImageHelper::base64_to_jpeg($cover));
            file_put_contents("/tmp/${bannerName}.jpeg", ImageHelper::base64_to_jpeg($banner));
            $imageHelper->compressImage("/tmp/${coverName}.jpeg", 200, "${imagesDir}/${key}_${month}", 'cover.jpg');
            $imageHelper->compressImage("/tmp/${bannerName}.jpeg", 1080, "${imagesDir}/${key}_${month}", 'banner.jpg');
        }


        return $this->render('admin/edit-home.twig', [
            'months' => MONTHS,
            'files' => $images,
            'cropData' => $cropData,
        ]);
    }

    public function uploadOriginal($request)
    {
        if (strtoupper($request->requestMethod) !== 'POST') {
            return $this->json(['only post allowed'], 405);
        }
        $imagesDir = $request->documentRoot . '/images/home';
        $month = $_REQUEST['month'];
        $original = $_FILES['original'];
        $key = array_search($month, MONTHS);

        $imageHelper = new ImageHelper();
        $imageHelper->compressImage($original['tmp_name'], 1000, "${imagesDir}/${key}_${month}", 'tmp.original.jpg');

        return $this->json(['file' => "/images/home/${key}_${month}/tmp.original.jpg"]);
    }

    public function add($request)
    {
        if (strtolower($request->requestMethod) === 'post') {
            $timeFileName = new DateTime($_REQUEST['journalEntry']);
            $fileName = $timeFileName->format('Y-m-d');
            $month = $timeFileName->format('F');
            $date = new DateTime();
            $content =
                "---\ntitle: " .
                $fileName .
                "\ndate: " .
                $date->format('Y-m-d H:i') .
                "\n---";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/content/" . $month . '/' . $timeFileName->format('d.m.Y') . '.md', $content);
            header('Location: /admin/edit?file=/' . $month . '/' . $timeFileName->format('d.m.Y'));
            header('HTTP/1.1 302');
        }

        $now = new \DateTime();

        return $this->render('admin/add.twig', [
            'parent' => $_REQUEST['parent'],
            'pages' => MONTHS,
            'currentMonth' => intval($now->format('n')) - 1,
        ]);
    }

    public function editCurrent($request)
    {
        $file = $this->getCurrentFile();

        return $this->redirect("/admin/edit?file=" . rtrim($file, '.md'));
    }

    public function publishStatus($request)
    {
        if (strtoupper($request->requestMethod) !== 'POST') {
            return $this->json(['post requests only'], 405);
        }
        $contentDir = $_SERVER['DOCUMENT_ROOT'] . '/content';
        $fileName = $this->getCurrentFile();

        $content = file_get_contents("${contentDir}/${fileName}");

        $content .= $_REQUEST['status'] . "\n\n";
        file_put_contents("${contentDir}/${fileName}", $content);

        return $this->json(['message' => 'Successfully added status']);
    }

    public function testExif($request)
    {
        if (strtoupper($request->requestMethod) === 'POST') {
            print_r($_FILES);
            $file = $_FILES['file'];
            print_r(exif_read_data($file['tmp_name']));
        }

        return $this->render('admin/test-exif.twig');
    }

    public function appendImage($request)
    {
        if (strtoupper($request->requestMethod) !== 'POST') {
            return $this->json(['post requests only'], 405);
        }

        $image = $_FILES['image'];
        $imageHelper = new ImageHelper();
        $generated = $imageHelper->storeEntryImage($image['tmp_name']);

        $contentDir = $_SERVER['DOCUMENT_ROOT'] . '/content';
        $file = $this->getCurrentFile();
        $content = file_get_contents("${contentDir}${file}");
        $content .= "![image](" . $generated[1] . ")\n\n";
        file_put_contents("${contentDir}${file}", $content);

        return $this->json(['message' => 'Successfully appended Image']);
    }

    private function getCurrentFile()
    {
        // get name of current file
        $now = new \DateTime();
        $title = $now->format('Y-m-d') . '.md';
        $month = $now->format('F');
        $fileDir = $_SERVER['DOCUMENT_ROOT'] . "/content/${month}/${title}";
        // check if file exists, if not create it
        $content =
            "---\ntitle: " .
            rtrim($title, '.md') .
            "\ndate: " .
            $now->format('Y-m-d H:i') .
            "\n---\n";
        if (!is_file($fileDir)) {
            file_put_contents($fileDir, $content);
        }

        return "/${month}/${title}";
    }

    function edit($request)
    {
        $page = $this->nacho->getPage($_REQUEST['file']);

        if ($request->requestMethod === 'POST') {
            header('content-type: application/json');
            if (!$page || !is_file($page['file'])) {
                return $this->json(['message' => 'Unable to find this file']);
            }

            file_put_contents($page['file'], $_REQUEST['content']);
            return json_encode(['message' => 'successfully saved content']);
        }

        $month = explode('/', $page['id'])[1];

        return $this->render('admin/edit.twig', [
            'page' => $page,
            'referer' => $_SERVER['HTTP_REFERER'],
            'month' => $month,
        ]);
    }

    public function showInfo($request)
    {
        return phpinfo();
    }

    protected function render(string $template, array $args = [])
    {
        $args['nav'] = $this->getFilesRecursive();

        return parent::render($template, $args);
    }

    private function getFilesRecursive(): array
    {
        $navHelper = new NavRenderer($this->nacho);
        $pages = $this->nacho->getPages();
        $page = $this->nacho->getPage('/');

        return [$navHelper->findChildPages('/', $page, $pages)];
    }
}
