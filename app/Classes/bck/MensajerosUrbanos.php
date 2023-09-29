<?php

namespace App\Classes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class MensajerosUrbanos
{
    private $UserID = 163616;
    private $CompanyID = 146496;
    private $client_id ="14kh8t36gkk4j3vz6_murbanos";
    private $client_secret ="f5bd805736563b33b491b59815e89d8518b7b082";
    private $grant_type = "client_credentials";
    private $token = "";
    private $devserver = "https://dev.api.mensajerosurbanos.com";
    private $prodserver = "https://dev.api.mensajerosurbanos.com";
    private $quoteResult = null;

    public function __construct()
    {
       $this->token = $this->getAuthToken();
    }

    private function getAuthToken()
    {
        $endpoint = "/oauth/token";

        $url = $this->devserver . $endpoint;

        $client = new Client();

        $response = $client->request('POST', $url, [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => $this->grant_type
            ]
        ]);
        
        $token = json_decode($response->getBody()->getContents());
        
        return $token->access_token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function createQuote($client, $store, $orderValue=100, $parking=0)
    {
        echo "Mensajeros Urbanos<br>";
        $httpClient = new Client();

        $endpoint = "/calculate";

        $url = $this->devserver . $endpoint;

        $jsonBody = "";

        $jsonBody .= '{
            "id_user": '.$this->UserID.',
            "type_service": 4,
            "roundtrip": 0,
            "declared_value": '.$orderValue.',
            "city": 50,
            "parking_surcharge": '.$parking.',
            "coordinates": [
                {
                "address": "'.$store->getAddress().'",
                "city": "ciudad mexico"                  
                },
                {
                "address": "'.$client->getAddress().'",
                "city": "ciudad mexico"
                }
            ]
        }';
        
        echo "SEND ASYNC Mensajeros Urbanos<br>";

        $promise = $httpClient->requestAsync('POST', $url, [
            'headers' => [
                'access_token' => $this->token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $jsonBody
        ]);

        echo "START ASYNC Mensajeros Urbanos<br>";
        echo "<br>";

        $promise->then(function ($response) {

            $status = $response->getStatusCode();
        
            $body  = (string) $response->getBody();
            
            $resBody = json_decode($body);

            if($status == 200)
                $newBodyStr = '{ "status": '.$status.', "quote": '.$resBody->data->total_service.' }';
            else
                $newBodyStr = '{ "status": 400, "quote": 0 }';

            $this->quoteResult = json_decode($newBodyStr);

            echo "END MENSAJEROS QUOTE:".time();
    
        });

        return $promise;
        
    }

    public function getQuote()
    {
        return !empty($this->quoteResult) ? $this->quoteResult : null;

    }
    public function createService()
    {
        return "testService";
    }

    public function trackService()
    {
        return "<br>test";
    }

    public function addStore()
    {
        return "testStore";   
    }
}