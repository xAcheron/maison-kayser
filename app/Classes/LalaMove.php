<?php

namespace App\Classes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Classes\LalaMoveApi;
use GuzzleHttp\Client;
use DateTime;

class LalaMove
{
    private $UserID = 0;
    private $CompanyID = 0;
    private $apikey ="ffd2a4db454b4706813cc589eef27ae9";
    private $secret ="MC0CAQACBQCtVpLjAgMBAAECBCiidEECAwDIawIDAN1pAgJ+mwIDAK9JAgMA";
    private $country = "MX_MEX";
    private $signature = "";
    private $devserver = "https://sandbox-rest.lalamove.com";
    private $prodserver = "https://sandbox-rest.lalamove.com";

    public function __construct()
    {
       
    }

    public function getQuote($client, $store, $orderValue=100, $parking=0)
    {
        
        $httpClient = new Client();

        $endpoint = "/v2/quotations";

        $url = $this->devserver . $endpoint;

        $body = array(
            "scheduleAt" => "", //gmdate('Y-m-d\TH:i:s\Z', time() + 60 * 30), // ISOString with the format YYYY-MM-ddTHH:mm:ss.000Z at UTC time
            "serviceType" => "MOTORCYCLE",                              // string to pick the available service type
            "specialRequests" => array("FOOD_SERVICE"),                               // array of strings available for the service type
            "requesterContact" => array(
              "name" => $store->getName(),
              "phone" => $store->getPhone()                                 // Phone number format must follow the format of your country
            ),  
            "stops" => array(
              array(
                "location" => array("lat" => $store->getAddressLat(), "lng" => $store->getAddressLon()),
                "addresses" => array(
                  "en_SG" => array(
                    "displayString" => $store->getAddress(),
                    "country" => "MX_MEX"                                   // Country code must follow the country you are at
                  )   
                )   
              ),  
              array(
                "location" => array("lat" => $client->getAddressLat(), "lng" => $client->getAddressLon()),
                "addresses" => array(
                  "en_SG" => array(
                    "displayString" => $client->getAddress(),
                    "country" => "MX_MEX"                                   // Country code must follow the country you are at
                  )   
                )   
              )   
            ),  
            "deliveries" => array(
              array(
                "toStop" => 1,
                "toContact" => array(
                  "name" => $client->getName(),
                  "phone" => $client->getPhone()                              // Phone number format must follow the format of your country
                ),  
                "remarks" => $client->getRemarks()
              )   
            )   
          );
        
        /*
        $jsonBody = "";
        $jsonBody .= '{
            "serviceType": "MOTORCYCLE",
            "specialRequests": [ "FOOD_SERVICE" ],
            "stops":[
                {
                    "location":
                    {
                        "lat": "'.$store->getAddressLat().'",
                        "lng": "'.$store->getAddressLon().'"
                    },
                    "addresses":
                    {
                        "en_MX":
                        {
                            "displayString": "'.$store->getAddress().'",
                            "country": "MX_MEX"
                        }
                    }
                },
                {
                    "location":
                    {
                        "lat": "'.$client->getAddressLat().'",
                        "lng": "'.$client->getAddressLon().'"
                    },
                    "addresses":
                    {
                        "en_MX":
                        {
                            "displayString": "'.$client->getAddress().'",
                            "country": "MX_MEX"
                        }
                    }
                }
            ],
            "requesterContact":
            {
                "name": "'.$store->getName().'",
                "phone": "'.$store->getPhone().'"
            },
            "deliveries":
            [
                {
                    "toStop": 1,
                    "toContact":
                    {
                        "name":"'.$client->getName().'",
                        "phone":"'.$client->getPhone().'"
                    },
                    "remarks":"'.$client->getRemarks().'"
                }
            ]
        }';
        */
        //$fecha = new DateTime();
        //$time = $fecha->getTimestamp();
        
        $time = time() * 1000;

        echo $time."<br>";

        $jsonBody = json_encode((object)$body);

        $_encryptBody = $time."\r\n"."POST"."\r\n".$endpoint."\r\n\r\n".$jsonBody;

        $signature = hash_hmac('sha256', $_encryptBody, $this->secret);
        
        echo $jsonBody."<br>";
        
        echo "hmac ".$this->apikey.":".$time.":".$signature."<br>";

        $content = [
            'headers' => [
                'Authorization' => "hmac ".$this->apikey.":".$time.":".$signature,
                'Content-Type'  => 'application/json',
                "Accept"=> "application/json",
                'X-LLM-Country' => "MX_MEX"
            ],
            'http_errors' => false
        ];

        $content['json'] = (object)$body;

        $response = $httpClient->request('POST', $url, $content);

        $status = $response->getStatusCode();
        
        $body  = (string) $response->getBody();
        
            
        
        if($status == 200)
            $newBodyStr = '{ "status": '.$status.', "quote": '.$resBody->totalFee.' }';
        else
            $newBodyStr = '{ "status": '.$status.', "quote": 0 }';
            
        return json_decode($newBodyStr);

    }

    public function createService(Request $request)
    {
        
    }

    public function trackService()
    {

    }

    public function addStore()
    {
        
    }
}