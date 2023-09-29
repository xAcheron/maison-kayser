<?php

namespace App\Classes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class MensajerosUrbanosApi
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
    private $orderStatus = null;

    public function __construct()
    {
       $this->token = $this->getAuthToken();
       $this->orderStatus = array( 1 => "Create", 2 => "on_hold", 3 => "assigned", 4 => "in_progress", 5 => "finished", 6 => "cancel" );
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
        
        $promise = $httpClient->requestAsync('POST', $url, [
            'headers' => [
                'access_token' => $this->token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $jsonBody
        ]);

        $promise->then(function ($response) {

            $status = $response->getStatusCode();
        
            $body  = (string) $response->getBody();
            
            $resBody = json_decode($body);

            if($status == 200)
                $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": '.$status.', "quote": '.$resBody->data->total_service.' }';
            else
                $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": 400, "quote": 0 }';

            $this->quoteResult = json_decode($newBodyStr);

        });

        return $promise;
        
    }

    public function getQuote()
    {
        return !empty($this->quoteResult) ? $this->quoteResult : null;
    }
    
    public function postOrder($client, $store, $order)
    {
        $httpClient = new Client();

        $endpoint = "/Create-services";
        $jsonBody ="";
        $url = $this->devserver . $endpoint;
//"store_id": "'.$store->getId("MensajerosUrbanos").'",

        $jsonBody .= '{
            "id_user": '.$this->UserID.',
            "type_service": 4,
            "roundtrip": 0,
            "declared_value": '.$order->getValue().',
            "city": 50,
            "start_date": "'.$order->getDate().'",
            "start_time": "'.$order->getTime().'",
            "observation": "",
            "user_payment_type": 3,
            "type_segmentation": 5,
            "type_task_cargo_id": 2,
            "os": "NEW API 2.0",
            "coordinates": [
              {
                "type": "1",
                "order_id": '.$order->getOrderNum().'99,
                "address": "'.$client->getAddress().'",
                "token": "'.$order->getOrderNum().'",
                "city": "ciudad mexico",
                "description": "'.$client->getRemarks().'",
                "client_data": {
                  "client_name": "'.$client->getAddress().'",
                  "client_phone": "'.$client->getAddress().'",
                  "client_email": "",
                  "products_value": '.$order->getValue().',
                  "domicile_value": "0",
                  "client_document": "",
                  "payment_type": 3
                },
                "products": [
                  {
                    "store_id": "KAYSREF",
                    "product_name": "Pedido Maison Kayser entrega a domicilo",
                    "url_img": null,
                    "value": '.$order->getValue().',
                    "quantity": 1,
                    "barcode": "",
                    "planogram": "'.$store->getRemarks().'"
                  }
                ]
              }
            ]
          }';

        $response = $httpClient->request('POST', $url, [
            'headers' => [
                'access_token' => $this->token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $jsonBody
        ]);

        $status = $response->getStatusCode();
        
        $body  = (string) $response->getBody();
        
        $resBody = json_decode($body);

        if($status == 200)
            $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": '.$resBody->data->status.', "service": "'.$resBody->data->uuid.'", "total": "'.$resBody->data->total.'", "dateTime": "'.$resBody->data->date.'", "distance": "'.$resBody->data->distance.'", "error": "'.$resBody->data->error.'" }';
        else
            $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": 400, "service": "NA", "total": 0, "error": "error" }';

        return json_decode($newBodyStr);

    }

    public function getOrderStatus($orderId)
    {
        $httpClient = new Client();

        $endpoint = "/task";

        $url = $this->devserver . $endpoint;

        $jsonBody .= '{
            "uuid": "'.$orderId.'"
        }';

        $response = $httpClient->request('POST', $url, [
            'headers' => [
                'access_token' => $this->token,
                'Content-Type'  => 'application/json'
            ],
            'body' => $jsonBody
        ]);

        $status = $response->getStatusCode();
        
        $body  = (string) $response->getBody();
        
        $resBody = json_decode($body);

        if($status == 200)
            $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": '.$resBody->data->status_id.' }';
        else
            $newBodyStr = '{ "provider": "MensajerosUrbanos", "status": 400 }';

        return json_decode($newBodyStr);

    }

    public function addStore()
    {
        return "testStore";   
    }
}