<?php


namespace App\Controllers;

use Nacho\Controllers\AbstractController;

/**
 * Class PageController
 */
class PageController extends AbstractController
{
    public function loadEntry()
    {
        $url = $_REQUEST['p'];
        $directory = $_SERVER['DOCUMENT_ROOT'] . '/content' . $url;
        $tmp = scandir($directory);
        $pagesDirs = [];
        foreach ($tmp as $subPage) {
            if (endswith($subPage, '.gitignore')) {
                continue;
            }
            if (!is_file($directory . '/' . $subPage)) {
                continue;
            }
            if (endswith($subPage, 'index.md')) {
                array_push($pagesDirs, $url);
            } else {
                array_push($pagesDirs, $url . '/' . rtrim($subPage, '.md'));
            }
        }
        $pages = [];
        foreach ($pagesDirs as $page) {
            $subPage = $this->nacho->getPage($page);
            if (is_bool($subPage)) {
                header('HTTP/1.1 404');
                return $this->json(['message' => 'Unable to find This page']);
            }
            $subPage['content'] = base64_encode($this->nacho->renderPage($subPage));
            array_push($pages, $subPage);
        }

        usort($pages, [$this, 'sortByDate']);

        return $this->json($pages);
    }

    public function sortByDate($a, $b) 
    {
        if (is_int(array_search($a['title'], MONTHS))) {
            return -1;
        }
        if (is_int(array_search($b['title'], MONTHS))) {
            return 1;
        }
        $t1 = strtotime($a['title']);
        $t2 = strtotime($b['title']);

        return $t2 - $t1;
    }
}
