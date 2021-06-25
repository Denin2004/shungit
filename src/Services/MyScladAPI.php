<?php
namespace App\Services;

class MyScladAPI
{
    protected $credentials;

    public function __construct($credentials)
    {
        $this->credentials = base64_encode($credentials);
    }

    public function query($query)
    {
        $ch = curl_init($query['url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $query['method']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
               'Authorization: Basic '.$this->credentials,
               'Content-Type: application/json;charset=UTF-8'
            ]
        );

/* for work */
        curl_setopt($ch, CURLOPT_PROXY, '192.168.82.249:3128');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        return curl_exec($ch);
    }
}
