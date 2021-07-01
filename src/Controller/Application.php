<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;

use Mobile_Detect;

use App\Services\MailAPI;
use App\Services\MyScladAPI;

class Application extends AbstractController
{
    public function index()
    {
        $detect = new Mobile_Detect;
        if ($detect->isMobile()) {
            return $this->render('base_mobile.html.twig');
        }
        return $this->render('base_web.html.twig');
    }

    public function config()
    {
        return new JsonResponse([
            'success' => true,
            'urls' => $this->renderView('urls.json.twig')
        ]);
    }
}
