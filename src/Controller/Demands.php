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
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/6e6f0433-c8b6-11e8-9109-f8fc00219b08'),
                //'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10',
                'method' => 'GET'
            ]),
            true
        );
        $res = [];
        foreach ($demands['rows'] as $demand) {
            $order = json_decode(
                $myScladAPI->query([
                    'url' => $demand['customerOrder']['meta']['href'],
                    'method' => 'GET'
                ]),
                true
            );
            $agent = json_decode(
                $myScladAPI->query([
                    'url' => $demand['agent']['meta']['href'],
                    'method' => 'GET'
                ]),
                true
            );
            $address = '';
            $index = '';
            $country = '';
            $recipient = '';
            foreach ($order['attributes'] as $attribute) {
                switch ($attribute['id']) {
                    case '03cc852b-5b8b-11e9-9ff4-34e80012699d': // Город
                        $address.= $attribute['value'].' ';
                        break;
                    case '03cc8ba7-5b8b-11e9-9ff4-34e80012699f': // Индекс
                        $index = $attribute['value'];
                        break;
                    case '03cc8957-5b8b-11e9-9ff4-34e80012699e': // Адрес
                        $address.= $attribute['value'].' ';
                        break;
                    case '402041a0-14f2-11ea-0a80-0546002039dc': // Страна
                        $country = $attribute['value']['name'];
                        break;
                    case '3eb9d744-9b5d-11ea-0a80-00f6000856e7': // Штат
                        $address.= $attribute['value'].' ';
                        break;
                    case 'cefaabd3-5b8a-11e9-9ff4-31500012efda':
                        $country = $attribute['value'];
                        break;
                    case '03cc8d7f-5b8b-11e9-9ff4-34e8001269a0':
                        $recipient = $attribute['value'];
                        break;
                }
            }
            $res[] = [
                'num' => $demand['name'],
                'sum' => $demand['sum']/100,
                'agent' => $agent['name'],
                'address' => $country.' '.$index.' '.$address,
                'recipient' => $recipient
            ];

        }
        return new JsonResponse([
            'success' => true,
            'demands' => $res
        ]);
    }
}
