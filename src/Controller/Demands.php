<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Services\MailAPI;
use App\Services\MyScladAPI;
use App\Services\SiteConfig;
use App\Entity\Batches;

class Demands extends AbstractController
{
    private $discount = 1;

    public function list(MyScladAPI $myScladAPI, $offset)
    {
        $demands = json_decode( //ожидае отгрузки
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&order=created,desc&offset='.$offset.
                  '&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/715fb121-c8c8-11e8-9107-50480022b339').';'.
                   'store='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/store/50392439-584c-11e8-9ff4-3150000af7ef').';'.
                   urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/c773c502-6039-11e9-912f-f3d400064b14').'.meta.href='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/customentity/3e0f4472-a6da-11e8-9ff4-3150001277bb/57c5e928-a6f4-11e8-9ff4-315000146031'),
                'method' => 'GET'
            ]),
            true
        );

/*        $demands = json_decode(
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&offset='.$offset.'&filter=name=05831',
                'method' => 'GET'
            ]),
            true
        );*/

/*        $demands = json_decode(
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&offset='.$offset.'&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/4a029aee-a6bf-11eb-0a80-083a000112cd'),
                'method' => 'GET'
            ]),
            true
        );*/
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
            $currency = json_decode(
                $myScladAPI->query([
                    'url' => $order['rate']['currency']['meta']['href'],
                    'method' => 'GET'
                ]),
                true
            );
            $address = '';
            $index = '';
            $country = '';
            $countryURL = '';
            $recipient = '';
            $city = '';
            $street = '';
            $pochtaURL = '';
            foreach ($order['attributes'] as $attribute) {
                switch ($attribute['id']) {
                    case '03cc852b-5b8b-11e9-9ff4-34e80012699d': // Город
                        $address.= $attribute['value'].' ';
                        $city = $attribute['value'];
                        break;
                    case '03cc8ba7-5b8b-11e9-9ff4-34e80012699f': // Индекс
                        $index = $attribute['value'];
                        break;
                    case '03cc8957-5b8b-11e9-9ff4-34e80012699e': // Адрес
                        $address.= $attribute['value'].' ';
                        $street = $attribute['value'];
                        break;
                    case '402041a0-14f2-11ea-0a80-0546002039dc': // Страна
                        $country = $attribute['value']['name'];
                        $countryURL = $attribute['value']['meta']['href'];
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
            foreach ($demand['attributes'] as $attribute) {
                switch ($attribute['id']) {
                    case '274274ac-d8f0-11eb-0a80-00e50010d1bb':
                        $pochtaURL = $attribute['value'];
                        break;
                }
            }
            $res[] = [
                'num' => $demand['name'],
                'sum' => $demand['sum']/100,
                'agent' => $agent['name'],
                'address' => $country.' '.$index.' '.$address,
                'recipient' => $recipient,
                'currency' => $currency['isoCode'],
                'demandURL' => $demand['meta']['href'],
                'actions' => '',
                'agentURL' => $demand['agent']['meta']['href'],
                'countryURL' => $countryURL,
                'city' => $city,
                'index' => $index,
                'order-num' => $order['name'],
                'street' => $street,
                'pochtaURL' => $pochtaURL
            ];
        }
        return new JsonResponse([
            'success' => true,
            'demands' => $res,
            'nextOffset' => $offset+10
        ]);
    }

    public function createPochtaOrder(Request $request, MyScladAPI $myScladAPI, MailAPI $mailAPI, SiteConfig $config, Batches $batchesDB)
    {
        $data = json_decode($request->getContent(), true);
        $order = [
            [
                //'address-from' => $config->get('post-office'),
                'address-type-to' => 'DEFAULT',
                'customs-declaration' => [
                    'currency' => $data['currency'],
                    'customs-entries' => [],
                    'entries-type' => 'SALE_OF_GOODS',
                ],
                'given-name' => 'string',
                'mail-category' => 'ORDINARY',
                'mass' => 0,
                'payment' => 0,
                'recipient-name' => 'string',
                'weight' => 0,
                'mail-type' => 'POSTAL_PARCEL',
                'transport-type' => 'AVIA'
            ]
        ];

        $order[0]['given-name'] = $data['recipient'];
        $order[0]['recipient-name'] = $data['recipient'];
        $order[0]['order-num'] = $data['order-num'];

        $demand = json_decode(
            $myScladAPI->query([
                'url' => $data['demandURL'],
                'method' => 'GET'
            ]),
            true
        );

        $dateDemand = '';
        foreach ($demand['attributes'] as $attribute) {
            switch ($attribute['id']) {
                case '7c75642d-c0d8-11e8-9ff4-34e80029be85': // вес
                    $order[0]['weight'] = $attribute['value']*1000;
                    $order[0]['mass'] = $attribute['value']*1000;
                    break;
                case 'b46f0979-d8d7-11eb-0a80-0962000d1b54':
                    $dateDemand = explode(' ', $attribute['value'])[0];
                    break;
            }
        }
        if ($dateDemand == '') {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    [
                        'code' => $demand['name'],
                        'description' => 'demand.errors.no_date',
                        'details' => ''
                    ]
                ]
            ]);
        }
        if ($order[0]['mass'] < 1500) {
            $order[0]['mail-type'] = 'SMALL_PACKET';
            $order[0]['mail-category'] =  'ORDERED';
        }

        $organization = json_decode(
            $myScladAPI->query([
                'url' => $demand['organization']['meta']['href'],
                'method' => 'GET'
            ]),
            true
        );
        $country = json_decode(
            $myScladAPI->query([
                'url' => $data['countryURL'],
                'method' => 'GET'
            ]),
            true
        );
        $order[0]['mail-direct'] = $country['code'];

        $addresses = json_decode($mailAPI->query([
            'url' => 'https://otpravka-api.pochta.ru/1.0/clean/address',
            'method' => 'POST',
            'data' => [
                [
                    'id' => 'addressTo',
                    'original-address' => $data['address']
                ]
            ]
        ]), true);
        foreach ($addresses as $address) {
            if ($address['id'] == 'addressTo') {
                if ($address['validation-code'] != 'VALIDATED') {
                    $order[0]['str-index-to'] = $data['index']; // преобразовать в строку
                    $order[0]['street-to'] = $data['street'];
                    $order[0]['place-to'] = $data['city'];
                } else {
                    foreach ($address as $key => $value) {
                        if (!in_array($key, ['id', 'original-address', 'validation-code', 'quality-code']) && !str_contains($key, '-guid')) {
                            $order[0][$key.'-to'] = $value;
                        }
                    }
                }
            }
        }
        $order[0]['postoffice-code'] = $config->get('postoffice-code');

        $order[0]['sender-name'] = $organization['name'];

        $agent = json_decode(
            $myScladAPI->query([
                'url' => $data['agentURL'],
                'method' => 'GET'
            ]),
            true
        );
        if (isset($agent['phone'])) {
            $order[0]['tel-address'] = str_replace(['+', '-', ' ', ')', '('], '', $agent['phone']);
        }

        $positions = json_decode(
            $myScladAPI->query([
                'url' => $demand['positions']['meta']['href'],
                'method' => 'GET'
            ]),
            true
        );

        $goods = [];
        foreach ($positions['rows'] as $position) {
            $pos = json_decode(
                $myScladAPI->query([
                    'url' => $position['meta']['href'],
                    'method' => 'GET'
                ]),
                true
            );
            $assortment = json_decode(
                $myScladAPI->query([
                    'url' => $pos['assortment']['meta']['href'],
                    'method' => 'GET'
                ]),
                true
            );// description брать из product folder
            $productIndex = array_search($assortment['productFolder']['meta']['href'], $goods);
            if ($productIndex === false) {
                $productFolder = json_decode(
                    $myScladAPI->query([
                        'url' => $assortment['productFolder']['meta']['href'],
                        'method' => 'GET'
                    ]),
                    true
                ); // tnvedcode - code //desription - description
                if ($productFolder['id'] != '08451167-b85d-11e9-912f-f3d40009f89c') { // не услуги
                    if (!isset($productFolder['description'])) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [
                                [
                                    'code' => $demand['name'],
                                    'description' => 'demand.errors.no_product_description',
                                    'details' => $productFolder['name']
                                ]
                            ]
                        ]);
                    }
                    if (!isset($productFolder['code'])) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [
                                [
                                    'code' => $demand['name'],
                                    'description' => 'demand.errors.no_product_code',
                                    'details' => $productFolder['name']
                                ]
                            ]
                        ]);
                    }
                    $order[0]['customs-declaration']['customs-entries'][] = [
                        'amount' => $pos['quantity'],
                        'country-code' => 643,
                        'description' => $productFolder['description'],
                        'tnved-code' => $productFolder['code'],
                        'trademark' => 'NO TM',
                        'value' => intval(round($pos['price']*$this->discount, 0)*$pos['quantity']),
                        'weight' => $assortment['weight']*$pos['quantity']*1000
                    ];
                    $goods[] = $assortment['productFolder']['meta']['href'];
                }
            } else {
/*                $order[0]['customs-declaration']['customs-entries'][$productIndex]['value'] = intval(
                    round(
                        ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value']+$pos['price']*$this->discount*$pos['quantity']) /
                        ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']+$pos['quantity'])
                    )
                );*/
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['value'] += intval(round($pos['price']*$this->discount, 0)*$pos['quantity']);
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount'] += $pos['quantity'];
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['weight'] += $assortment['weight']*$pos['quantity']*1000;
            }
        }
        $res = json_decode($mailAPI->query([
            'url' => 'https://otpravka-api.pochta.ru/1.0/user/backlog',
            'method' => 'PUT',
            'data' => $order
        ]), true);
        if (isset($res['result-ids'])) {
            $pochtaDemand = json_decode($mailAPI->query([
                'url' => 'https://otpravka-api.pochta.ru/1.0/backlog/'.$res['result-ids'][0],
                'method' => 'GET'
            ]), true);
            $myScladAPI->query([
                'url' => $data['demandURL'],
                'method' => 'PUT',
                'data' => [
                    'name' => $pochtaDemand['barcode'],
                    'attributes' => [
                        [
                            'meta' => [
                                'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/274274ac-d8f0-11eb-0a80-00e50010d1bb',
                                'type' => 'attributemetadata',
                                'mediaType' => 'application/json'
                            ],
                            'id' => '274274ac-d8f0-11eb-0a80-00e50010d1bb',
                            'type' => 'link',
                            'value' => 'https://otpravka.pochta.ru/api/document/downloadForms/'.$res['result-ids'][0]
                        ]
                    ],
                    'overhead' => [
                        'sum' =>  $pochtaDemand['mass-rate-with-vat'],
                        'distribution' => 'price'
                    ]
                ]
            ]);
            $batches = $batchesDB->find([
                'dt' => $dateDemand,
                'mail_type' => $order[0]['mail-type']
            ]);
            if (count($batches) == 0) {
                $part = json_decode($mailAPI->query([
                    'url' => 'https://otpravka-api.pochta.ru/1.0/user/shipment?sending-date='.$dateDemand,
                    'method' => 'POST',
                    'data' => $res['result-ids']
                ]), true);
                if (isset($part['errors'])) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $part['errors']
                    ]);
                }
                $batchesDB->create([
                    'dt' => $dateDemand,
                    'batch' => $part['batches'][0]['batch-name'],
                    'mail_type' => $order[0]['mail-type']
                ]);
            } else {
                $part = json_decode($mailAPI->query([
                    'url' => 'https://otpravka-api.pochta.ru/1.0/batch/'.$batches[0]['batch'].'/shipment',
                    'method' => 'POST',
                    'data' => $res['result-ids']
                ]), true);
                if (isset($part['errors'])) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $part['errors']
                    ]);
                }
                if (isset($part['code'])and($part['code'] == 1001)) {
                    $batchesDB->delete([
                      'batch' => $batches[0]['batch']
                    ]);
                    $part = json_decode($mailAPI->query([
                        'url' => 'https://otpravka-api.pochta.ru/1.0/user/shipment?sending-date='.$dateDemand,
                        'method' => 'POST',
                        'data' => $res['result-ids']
                    ]), true);
                    if (isset($part['errors'])) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => $part['errors']
                        ]);
                    }
                    $batchesDB->create([
                        'dt' => $dateDemand,
                        'batch' => $part['batches'][0]['batch-name'],
                        'mail_type' => $order[0]['mail-type']
                    ]);
                }
            }
            if (isset($part['errors'])) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $part['errors']
                ]);
            }
        } else {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    [
                        'code' => 'Internal error',
                        'description' => json_encode($res, JSON_UNESCAPED_UNICODE),
                        'details' => json_encode($order[0], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
                    ]
                ]
            ]);
        }
        return new JsonResponse([
            'success' => true,
            'mailResult' => $res
        ]);
    }
}
