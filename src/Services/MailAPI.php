<?php
namespace App\Services;

class MailAPI
{
    protected $token;
    protected $credentials;

    public function __construct($token, $credentials)
    {
        $this->token = $token;
        $this->credentials = base64_encode($credentials);
    }

    public function query($query)
    {
        $ch = curl_init($query['url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $query['method']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (isset($query['data'])) {
            $data_string = json_encode($query['data']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        }
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
               'Authorization: AccessToken '.$this->token,
               'X-User-Authorization: Basic '.$this->credentials,
               'Content-Type: application/json;charset=UTF-8'
            ]
        );
/* for work */
//        curl_setopt($ch, CURLOPT_PROXY, '192.168.82.249:3128');
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        return curl_exec($ch);
    }
}
