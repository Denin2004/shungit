<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Mobile_Detect;

use App\Services\MailAPI;
use App\Services\MyScladAPI;

class Application extends AbstractController
{
    /**
     * @Route("{reactRouting}", name="default", defaults={"reactRouting": null})
     */
    public function index()
    {
        $detect = new Mobile_Detect;
        if ($detect->isMobile()) {
            return $this->render('base_mobile.html.twig');
        }
        return $this->render('base_web.html.twig');
    }

    public function error403(Request $request)
    {
        return new Response('saaaaa');
    }

    public function config()
    {
        return new JsonResponse([
            'success' => true,
            'urls' => $this->renderView('urls.json.twig')
        ]);
    }

    /**
     * @Route("{reactRouting}", name="default", defaults={"reactRouting": null})
     */

    public function test(MyScladAPI $myScladAPI, MailAPI $mailAPI)
    {
        $res = $mailAPI->query([
            'url' => 'https://otpravka-api.pochta.ru/1.0/clean/address',
            'method' => 'POST',
            'data' => [
                [
                    'id' => 'adr 1',
                    'original-address' => 'L1M 2M6, Canada, Whitby, 62 Kenilworth Cres'
                ],
                [
                    'id' => 'adr 2',
                    'original-address' => 'ул. Мясницкая, д. 26, г. Москва, 1'
                ]
            ]
        ]);
        dump(json_decode($res, true));
        return new JsonResponse([
            'success' => true
        ]);


        $ch = curl_init('https://otpravka-api.pochta.ru/1.0/clean/address');
        //$ch = curl_init('https://online.moysklad.ru/api/remap/1.2/security/token');
        //$ch = curl_init('https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/6e6f0433-c8b6-11e8-9109-f8fc00219b08'));
        //$ch = curl_init('https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10');
        //$ch = curl_init('https://online.moysklad.ru/api/remap/1.2/entity/demand/0003ac30-8253-11eb-0a80-00110009d254');
        //$ch = curl_init('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/6e6f0433-c8b6-11e8-9109-f8fc00219b08');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data_string = json_encode([
            [
                'id' => 'adr 1',
                'original-address' => 'L1M 2M6, Canada, Whitby, 62 Kenilworth Cres'
            ],
            [
                'id' => 'adr 2',
                'original-address' => 'ул. Мясницкая, д. 26, г. Москва, 1'
            ]
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        //dump(base64_encode('bot@shungit1:Ботшунгит'));
        // почта

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
               'Authorization: AccessToken BJa2a7nXn99B4jym0ktUlRUta6yholcx',
               'X-User-Authorization: Basic '.base64_encode('dir@karelshungit.com:Mineral123456'),
               'Content-Type: application/json;charset=UTF-8',
               'Content-Length: ' . strlen($data_string)
            ]
        );


        /* мой складs
         * curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
               'Authorization: Basic Ym90QHNodW5naXQxOtCR0L7RgtGI0YPQvdCz0LjRgg=='
            ]
        );*/
        $res = curl_exec($ch);
        dump($res);
        if (!$res) {
            return new JsonResponse([
                'success' => false,
                'error' =>  curl_error($ch)
            ]);
        }
        dump(json_decode($res, true));
        return new JsonResponse([
            'success' => true
        ]);
    }
}
