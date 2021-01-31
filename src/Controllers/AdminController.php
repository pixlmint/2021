<?php

namespace Nacho\Controllers;

use DateTime;
use Nacho\Helpers\NavRenderer;
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

        $pages = ['January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $now = new \DateTime();

        return $this->render('admin/add.twig', [
            'parent' => $_REQUEST['parent'],
            'pages' => $pages,
            'currentMonth' => intval($now->format('n')) - 1,
        ]);
    }

    public function editCurrent($request)
    {
        // get name of current file
        $now = new \DateTime();
        $title = $now->format('Y-m-d') . '.md';
        $month = $now->format('F');
        $fileDir = $_SERVER['DOCUMENT_ROOT'] . "//${month}/${title}";
        // check if file exists, if not create it
        $content =
            "---\ntitle: " .
            rtrim($title, '.md') .
            "\ndate: " .
            $now->format('Y-m-d H:i') .
            "\n---";
        if (!is_file($fileDir)) {
            file_put_contents($fileDir, $content);
        }

        // redirect to edit page of current file
        return $this->redirect("/admin/edit?file=/${month}/" . rtrim($title, '.md'));
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
