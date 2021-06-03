<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
            dump($demand);
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
                'recipient' => $recipient,
                'currency' => $currency['isoCode'],
                'demandURL' => $demand['meta']['href'],
                'actions' => ''
            ];
        }
        return new JsonResponse([
            'success' => true,
            'demands' => $res
        ]);
    }

    public function createPochtaOrder(Request $request)
    {
        dump($request->getContent());
        $order = [
            [
                'address-from' => [
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
                ],
                'address-type-to' => 'DEFAULT',
                'area-to' => 'string',
                'branch-name' => 'string',
                'brand-name' => 'string',
                'building-to' => 'string',
                'comment' => 'string',
                'completeness-checking' => true,
                'compulsory-payment' => 0,
                'corpus-to' => 'string',
                'courier' => true,
                'customs-declaration' => [
                    'certificate-number' => 'string',
                    'currency' => 'string',
                    'customs-entries' => [
                        [
                            'amount' => 0,
                            'country-code' => 0,
                            'description' => 'string',
                            'tnved-code' => 'string',
                            'value' => 0,
                            'weight' => 0
                        ]
                    ],
                    'entries-type' => 'GIFT',
                    'invoice-number' => 'string',
                    'license-number' => 'string',
                    'with-certificate' => true,
                    'with-invoice' => true,
                    'with-license' => true
                ],
                'delivery-with-cod' => true,
                'dimension' => [
                    'height' => 0,
                    'length' => 0,
                    'width' => 0
                ],
                'dimension-type' => 'S',
                'easy-return' => true,
                'ecom-data' => [
                    'delivery-point-index' => 'string',
                    'services' => [
                       'WITHOUT_SERVICE'
                    ]
                ],
                'envelope-type' => 'C4',
                'fiscal-data' => [
                    'customer-email' => 'string',
                    'customer-inn' => 'string',
                    'customer-name' => 'string',
                    'customer-phone' => 0,
                    'payment-amount' => 0
                ],
                'fragile' => true,
                'given-name' => 'string',
                'goods' => [
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
                ],
                'hotel-to' => 'string',
                'house-to' => 'string',
                'index-to' => 0,
                'insr-value' => 0,
                'inventory' => true,
                'letter-to' => 'string',
                'location-to' => 'string',
                'mail-category' => 'SIMPLE',
                'mail-direct' => 0,
                'mail-type' => 'UNDEFINED',
                'manual-address-input' => true,
                'mass' => 0,
                'middle-name' => 'string',
                'no-return' => true,
                'notice-payment-method' => 'CASHLESS',
                'num-address-type-to' => 'string',
                'office-to' => 'string',
                'order-num' => 'string',
                'payment' => 0,
                'payment-method' => 'CASHLESS',
                'place-to' => 'string',
                'postoffice-code' => 'string',
                'raw-address' => 'string',
                'raw-tel-address' => 'string',
                'recipient-name' => 'string',
                'region-to' => 'string',
                'room-to' => 'string',
                'slash-to' => 'string',
                'sms-notice-recipient' => 0,
                'str-index-to' => 'string',
                'street-to' => 'string',
                'surname' => 'string',
                'tel-address' => 0,
                'time-slot-id' => 0,
                'transport-mode' => 'SUPEREXPRESS',
                'transport-type' => 'SURFACE',
                'vladenie-to' => 'string',
                'vsd' => true,
                'with-electronic-notice' => true,
                'with-order-of-notice' => true,
                'with-simple-notice' => true,
                'wo-mail-rank' => true
            ]
        ];
        dump($order);
        return new JsonResponse([
            'success' => true,
            //'demands' => $res
        ]);
    }
}
