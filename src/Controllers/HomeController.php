<?php

namespace Nacho\Controllers;

use Nacho\Helpers\NavRenderer;
use Nacho\Nacho;

class HomeController extends AbstractController
{
    private $navRenderer;

    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        $this->navRenderer = new NavRenderer($nacho);
    }

    public function index($request)
    {
        if ($request->requestUri === '/') {
            return $this->render('home.twig');
        }

        return $this->render('month.twig', [
            'month' => implode('', explode('/', $request->requestUri)),
        ]);
    }

    public function loadNav($request)
    {
        return $this->navRenderer->output();
    }
}
