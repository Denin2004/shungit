<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainPage extends AbstractController
{
    /**
     * @Route("/admin/{reactRouting}", name="default", defaults={"reactRouting": null})
     */
    public function index()
    {
        return $this->render('base.html.twig');
    }
}
