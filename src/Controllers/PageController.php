<?php


namespace Nacho\Controllers;

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
            if (endswith($page, '.gitignore')) {
                continue;
            }
            $subPage = $this->nacho->getPage($page);
            if (is_bool($subPage)) {
                header('HTTP/1.1 404');
                return $this->json(['message' => 'Unable to find This page']);
            }
            $subPage['content'] = base64_encode($this->nacho->renderPage($subPage));
            array_push($pages, $subPage);
        }

        return $this->json($pages);
    }
}
