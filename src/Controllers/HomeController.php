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
        return $this->render('home.twig');
    }

    public function getMonth($request)
    {
        $month = $request->getRoute()->month;

        return $this->render('month.twig', [
            'month' => $month,
        ]);
    }

    public function getDay($request)
    {
        $month = $request->getRoute()->month;

        return $this->render('month.twig', [
            'month' => $month,
        ]);
    }

    public function loadNav($request)
    {
        return $this->navRenderer->output();
    }
}
