<?php

namespace App\Controllers;

use App\Helpers\NavRenderer;
use Nacho\Nacho;
use Nacho\Controllers\AbstractController;
use Twig\TwigFunction;

class HomeController extends AbstractController
{
    private $navRenderer;

    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        $this->navRenderer = new NavRenderer($nacho);
        $this->getTwig()->addFunction(new TwigFunction('month_index', function($var) {
            return array_search($var, MONTHS);
        }));
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
        $day = $request->getRoute()->date;
        $date = new \DateTime($day);
        $route = "/${month}/" . $date->format('Y-m-d');
        $page = $this->nacho->getPage($route);
        $content = $this->nacho->renderPage($page);

        return $this->render('day.twig', [
            'day' => $date->format('d.m.Y'),
            'content' => $content,
        ]);
    }

    public function loadNav($request)
    {
        return $this->navRenderer->output();
    }
}
