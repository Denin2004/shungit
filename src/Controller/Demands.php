<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Services\MailAPI;
use App\Services\MyScladAPI;
use App\Services\SiteConfig;

class Demands extends AbstractController
{
    public function list(MyScladAPI $myScladAPI, $offset)
    {
/*        $s = json_decode(
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata?limit=100',
                'method' => 'GET'
            ]),
            true
        );
        dump($s);*/

/*        $demands = json_decode( //ожидае отгрузки
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&offset='.$offset.'&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/6e6f0433-c8b6-11e8-9109-f8fc00219b08'),
                'method' => 'GET'
            ]),
            true
        );*/

        $demands = json_decode(
            $myScladAPI->query([
                'url' => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=10&offset='.$offset.'&filter=state='.urlencode('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/states/4a029aee-a6bf-11eb-0a80-083a000112cd'),
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
                'street' => $street
            ];
        }
        return new JsonResponse([
            'success' => true,
            'demands' => $res,
            'nextOffset' => $offset+10
        ]);
    }

    public function createPochtaOrder(Request $request, MyScladAPI $myScladAPI, MailAPI $mailAPI, SiteConfig $config)
    {
        $order = [
            [
                'address-from' => $config->get('post-office'),
                'address-type-to' => 'DEFAULT',
                'customs-declaration' => [
                    'currency' => 'USD',
                    'customs-entries' => [],
                    'entries-type' => 'GIFT',
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

        $data = json_decode($request->getContent(), true);

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
        dump($demand);

/*        $res = json_decode(   изменение отгрузки
            $myScladAPI->query([
                'url' => $data['demandURL'],
                'method' => 'PUT',
                'data' => [
                    'name' => $demand['name'].'-test'
                ]
            ]),
            true
        );
        dump($res);
        return new JsonResponse([
            'success' => true,
            'mailResult' => []
        ]);*/



        foreach ($demand['attributes'] as $attribute) {
            switch ($attribute['id']) {
                case '7c75642d-c0d8-11e8-9ff4-34e80029be85': // вес
                    $order[0]['weight'] = $attribute['value']*1000;
                    $order[0]['mass'] = $attribute['value']*1000;
                    break;
            }
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
                    $order[0]['index-to'] = $data['index']; // преобразовать в строку
                    //$order[0]['raw-address'] = $data['address'];
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
        //$order[0]['tel-address-from'] = isset($organization['phone']) ? str_replace(['+', '-', ' ', ')', '('], '', $organization['phone']): 0;

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
                            'error' => 'demand.errors.no_product_description',
                            'args' => [
                                'product' => $productFolder['name']
                            ]
                        ]);
                    }
                    if (!isset($productFolder['code'])) {
                        return new JsonResponse([
                            'success' => false,
                            'error' => 'demand.errors.no_product_code',
                            'args' => [
                                'product' => $productFolder['name']
                            ]
                        ]);
                    }
                    $order[0]['customs-declaration']['customs-entries'][] = [
                        'amount' => $pos['quantity'],
                        'country-code' => 643,
                        'description' => $productFolder['description'],
                        'tnved-code' => $productFolder['code'],
                        'trademark' => 'NO TM',
                        'value' => intval(round($pos['price']*0.2, 0)),
                        'weight' => $assortment['weight']*$pos['quantity']*1000
                    ];
                    $goods[] = $assortment['productFolder']['meta']['href'];
                }
            } else {
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['value'] =
                    ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value']+$pos['price']*0.2/100*$pos['quantity']) /
                    ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']+$pos['quantity']);
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount'] += $pos['quantity'];
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['weight'] += $assortment['weight']*$pos['quantity']*1000;
            }
        }
/*        $res = json_decode($mailAPI->query([
            'url' => 'https://otpravka-api.pochta.ru/1.0/user/backlog',
            'method' => 'PUT',
            'data' => $order
        ]), true);*/
        dump($order[0]);
        return new JsonResponse([
            'success' => true,
            'mailResult' => $res
        ]);
    }

    public function createPochtaOrderOld(Request $request, MyScladAPI $myScladAPI, MailAPI $mailAPI, SiteConfig $config)
    {
        $order = [
            [
                'address-from' => $config->get('post_office')/*[
                    'address-type' => 'DEFAULT',
                    'area' => 'string',
                    'building' => 'string',
                    'corpus' => 'string',
                    'hotel' => 'string',
                    'house' => 'string',
                    'index' => 'string',
                    'letter' => 'string',
                    'location' => 'string',
                    'num-address-type' => 'string',
                    'office' => 'string',
                    'place' => 'string',
                    'region' => 'string',
                    'room' => 'string',
                    'slash' => 'string',
                    'street' => 'string',
                    'vladenie' => 'string'
                ]*/,
                'address-type-to' => 'DEFAULT',
                //'area-to' => 'string',
                //'branch-name' => 'string',
                //'brand-name' => 'string',
                //'building-to' => 'string',
                //'comment' => 'string',
                //'completeness-checking' => true,
                //'compulsory-payment' => 0,
                //'corpus-to' => 'string',
                //'courier' => false,
                'customs-declaration' => [
                    //'certificate-number' => 'string',
                    'currency' => 'USD',
                    //'customs-code' => 'string',
                    'customs-entries' => [
                        /*[
                            'amount' => 0,
                            'country-code' => 0,
                            'description' => 'string',
                            'tnved-code' => 'string',
                            'trademark' => 'string',
                            'value' => 0,
                            'weight' => 0
                        ]*/
                    ],
                    'entries-type' => 'GIFT',
                    //'invoice-number' => 'string',
                    //'license-number' => 'string',
                    //'with-certificate' => false,
                    //'with-invoice' => false,
                    //'with-license' => false
                ],
                //'delivery-with-cod' => false,
                /*'dimension' => [
                    'height' => 0,
                    'length' => 0,
                    'width' => 0
                ],*/
                //'dimension-type' => 'S',
                //'easy-return' => false,
                /*'ecom-data' => [
                    'delivery-point-index' => 'string',
                    'services' => [
                       'WITHOUT_SERVICE'
                    ]
                ],*/
                //'envelope-type' => 'C', ????? https://otpravka.pochta.ru/specification#/enums-base-envelope-type
                /*'fiscal-data' => [
                    //'customer-email' => 'string',
                    //'customer-inn' => 'string',
                    //'customer-name' => 'string',
                    //'customer-phone' => 0,
                    //'payment-amount' => 0
                ],*/
                //'fragile' => true,
                'given-name' => 'string',
/*                'goods' => [
                    'items' => [
                        [
                           'code' => 'string',
                           'country-code' => 0,
                           'customs-declaration-number' => 'string',
                           'description' => 'string',
                           'excise' => 0,
                           'goods-type' => 'GOODS',
                           'insr-value' => 0,
                           'item-number' => 'string',
                           'lineattr' => 0,
                           'payattr' => 0,
                           'quantity' => 0,
                           'supplier-inn' => 'string',
                           'supplier-name' => 'string',
                           'supplier-phone' => 'string',
                           'value' => 0,
                           'vat-rate' => 0,
                           'weight' => 0
                        ]
                    ]
                ],*/
                //'hotel-to' => 'string',
                //'house-to' => 'string',
                //'index-to' => 0,
                //'insr-value' => 0,
                //'inventory' => true,
                //'letter-to' => 'string',
                //'location-to' => 'string',
                'mail-category' => 'ORDINARY',
                //'mail-direct' => 0,
                //'mail-type' => 'UNDEFINED',
                'mass' => 0,
                //'middle-name' => 'string',
                //'no-return' => false,
                //'notice-payment-method' => 'CASHLESS',
                //'num-address-type-to' => 'string',
                //'office-to' => 'string',
                //'order-num' => 'string',
                'payment' => 0,
                //'payment-method' => 'CASHLESS',
                //'place-to' => 'string',
                //'postoffice-code' => 'string',
                //'pre-post-preparation' => false,
                //'prepaid-amount' => 0,
                //'raw-address' => 'string',
                'recipient-name' => 'string',
               // 'region-to' => 'string',
               // 'room-to' => 'string',
                //'sender-name' => 'string',
                //'slash-to' => 'string',
                //'sms-notice-recipient' => 0,
                //'str-index-to' => 'string',
                //'street-to' => 'string',
                //'surname' => 'string',
                //'tel-address' => 0,
                //'tel-address-from' => 0,
                //'time-slot-id' => 0,
                //'transport-mode' => 'SUPEREXPRESS',
                //'transport-type' => 'AVIA',
                //'vladenie-to' => 'string',
                //'vsd' => true,
                'weight' => 0,
                //'with-electronic-notice' => true,
                //'with-order-of-notice' => true,
                //'with-simple-notice' => true,
                //'wo-mail-rank' => true
            ]
        ];

        $data = json_decode($request->getContent(), true);

        $order[0]['given-name'] = $data['recipient'];
        //$order[0]['place-to'] = $data['city'];
        //$order[0]['raw-address'] = $data['address'];
        $order[0]['recipient-name'] = $data['recipient'];
        //$order[0]['str-index-to'] = $data['index'];
        $order[0]['order-num'] = $data['order-num'];

        $demand = json_decode(
            $myScladAPI->query([
                'url' => $data['demandURL'],
                'method' => 'GET'
            ]),
            true
        );
        foreach ($demand['attributes'] as $attribute) {
            switch ($attribute['id']) {
                case '3c7d311e-ca7a-11e8-9ff4-3150002d4d91': // упаковка
                    $pack = json_decode(
                        $myScladAPI->query([
                            'url' => $attribute['value']['meta']['href'],
                            'method' => 'GET'
                        ]),
                        true
                    );
                    if (!isset($pack['code'])) {
                        return new JsonResponse([
                            'success' => false,
                            'error' => 'demand.errors.no_pack_code',
                            'args' => [
                                'pack' => $pack['name']
                            ]
                        ]);
                    }
                    $order[0]['mail-type'] = 'POSTAL_PARCEL';//$pack['code'];
                    break;
                case '7c75642d-c0d8-11e8-9ff4-34e80029be85': // вес
                    $order[0]['weight'] = $attribute['value']*1000;
                    $order[0]['mass'] = $attribute['value']*1000;
                    break;
            }
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
/*            if ($address['id'] == 'addressFrom') {
                if ($address['validation-code'] != 'VALIDATED') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'demand.errors.invalid_address_from',
                        'args' => [
                            'address' => $organization['name'].' '.$organization['actualAddress']
                        ]
                    ]);
                }
                foreach ($address as $key => $value) {
                    if (!in_array($key, ['id', 'original-address', 'validation-code', 'quality-code']) && !str_contains($key, '-guid')) {
                        $order[0]['address-from'][$key] = $value;
                    }
                }
            }*/
            if ($address['id'] == 'addressTo') {
                if ($address['validation-code'] != 'VALIDATED') {
                    $order[0]['place-to'] = $country['description']; // город взять из order
                    $order[0]['index-to'] = $data['index']; // преобразовать в строку
                    //$order[0]['raw-address'] = $data['address'];
                    $order[0]['street-to'] = 'Billerica 131 Bridle Road';//$data['address']; заказ адрес
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
        //$order[0]['tel-address-from'] = isset($organization['phone']) ? str_replace(['+', '-', ' ', ')', '('], '', $organization['phone']): 0;

        $agent = json_decode(
            $myScladAPI->query([
                'url' => $data['agentURL'],
                'method' => 'GET'
            ]),
            true
        );
        if (isset($agent['phone'])) {
            //$order[0]['fiscal-data']['customer-phone'] = str_replace(['+', '-', ' ', ')', '('], '', $agent['phone']);
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
                            'error' => 'demand.errors.no_product_description',
                            'args' => [
                                'product' => $productFolder['name']
                            ]
                        ]);
                    }
                    if (!isset($productFolder['code'])) {
                        return new JsonResponse([
                            'success' => false,
                            'error' => 'demand.errors.no_product_code',
                            'args' => [
                                'product' => $productFolder['name']
                            ]
                        ]);
                    }
                    $order[0]['customs-declaration']['customs-entries'][] = [
                        'amount' => $pos['quantity'],
                        'country-code' => 643,
                        'description' => $productFolder['description'],
                        'tnved-code' => $productFolder['code'],
                        'trademark' => 'NO TM',
                        'value' => intval(round($pos['price']*0.2, 0)),
                        'weight' => $assortment['weight']*$pos['quantity']*1000
                    ];
                    $goods[] = $assortment['productFolder']['meta']['href'];
                    //$order[0]['fiscal-data']['payment-amount'] += $pos['price']*0.2/100*$pos['quantity'];
                    //$order[0]['prepaid-amount'] += $pos['price']*0.2/100*$pos['quantity'];
                    //$order[0]['insr-value'] += intval(round($pos['price']*0.2*$pos['quantity']));
                }
            } else {
                //$order[0]['fiscal-data']['payment-amount'] -= $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value'];
                //$order[0]['prepaid-amount'] -= $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value'];

                $order[0]['customs-declaration']['customs-entries'][$productIndex]['value'] =
                    ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value']+$pos['price']*0.2/100*$pos['quantity']) /
                    ($order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']+$pos['quantity']);
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount'] += $pos['quantity'];
                $order[0]['customs-declaration']['customs-entries'][$productIndex]['weight'] += $assortment['weight']*$pos['quantity']*1000;
                //$order[0]['fiscal-data']['payment-amount'] += $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value'];
                //$order[0]['prepaid-amount'] += $order[0]['customs-declaration']['customs-entries'][$productIndex]['amount']*$order[0]['customs-declaration']['customs-entries'][$productIndex]['value'];
            }
        }
        //dump($order);
        //unset($order[0]['customs-declaration']);
        /*        $res = json_decode($mailAPI->query([
            'url' => 'https://otpravka-api.pochta.ru/1.0/user/backlog',
            'method' => 'PUT',
            'data' => $order
        ]), true);*/
        dump($order[0]);
        return new JsonResponse([
            'success' => true,
            'mailResult' => []//$res
        ]);
    }
}
