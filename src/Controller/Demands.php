<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


use App\Services\MailAPI;
use App\Services\MyScladAPI;

class Demands extends AbstractController
{
    public function list(MyScladAPI $myScladAPI)
    {
        $demands = json_decode(
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10',
                'method' => 'GET'
            ]),
            true
        );
        dump($demands);

        return new JsonResponse([
            'success' => true
        ]);
    }
}
