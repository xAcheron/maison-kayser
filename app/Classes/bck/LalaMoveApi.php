<?php

namespace App\Classes;
use GuzzleHttp\Client;

class LalaMoveApi
{
  public $host = '';
  public $key = '';
  public $secret = '';
  public $country = '';
  public $quoteResult = null;  
  public $method = "GET";
  public $body = array();
  public $path = '';
  public $header = array();
  public $ch = null;

  /**
   * Create the signature for the
   * @param $time, time to create the signature (should use current time, same as the Authorization timestamp)
   *
   * @return a signed signature using the secret
   */
  public function getSignature($time)
  {
    $_encryptBody = "{$time}\r\n{$this->method}\r\n{$this->path}\r\n\r\n";
    if ($this->method != "GET") {
      $_encryptBody .= json_encode((object)$this->body);
    }
    return hash_hmac("sha256", $_encryptBody, $this->secret);
  }

  /**
   * Build and return the header require for calling lalamove API
   * @return {Object} an associative aray of lalamove header
   */
  public function buildHeader()
  {
    //date_default_timezone_set("UTC");
    $time = time() * 1000;
    
    return [
      'X-Request-ID' => uniqid(),
      'Content-type' => "application/json; charset=utf-8",
      'Authorization' => "hmac ".$this->key.":".$time.":".$this->getSignature($time),
      'X-LLM-Country' => $this->country
    ];
  }

  /**
   * Send out the request via guzzleHttp
   * @return return the result after requesting through guzzleHttp
   */
  public function send()
  {
    $client = new Client();

    $content = [
      'headers' => $this->buildHeader(),
      'http_errors' => false
    ];

    if ($this->method != "GET") {
      $content['json'] = (object)$this->body;
    }
    
    $response  = $client->request($this->method, $this->host.$this->path, $content);
    return $response;
    
  }

  public function asyncSend()
  {
    $client = new Client();

    $content = [
      'headers' => $this->buildHeader(),
      'http_errors' => false
    ];

    if ($this->method != "GET") {
      $content['json'] = (object)$this->body;
    }

    $promise = $client->requestAsync($this->method, $this->host.$this->path, $content);
    
    $promise->then(function ($response) {

        $status = $response->getStatusCode();
        
        $body  = (string) $response->getBody();
        
        $resBody = json_decode($body);

        if($status == 200)
            $newBodyStr = '{ "provider": "LalaMove", "status": '.$status.', "quote": '.$resBody->totalFee.' }';
        else
            $newBodyStr = '{ "provider": "LalaMove", "status": 400, "quote": 0 }';

        $this->quoteResult = json_decode($newBodyStr);

    });

    return $promise;

  }


  /**
   * Constructor for Lalamove API
   *
   * @param $host - domain with http / https
   * @param $apikey - apikey lalamove provide
   * @param $apisecret - apisecret lalamove provide
   * @param $country - two letter country code such as HK, TH, SG, MX
   *
   */
  public function __construct()
  {
    $this->host = "https://sandbox-rest.lalamove.com";
    $this->key = "ffd2a4db454b4706813cc589eef27ae9";
    $this->secret = "MC0CAQACBQCtVpLjAgMBAAECBCiidEECAwDIawIDAN1pAgJ+mwIDAK9JAgMA";
    $this->country = "MX_MEX";
  }

  /**
   * Make a http Request to get a quotation from lalamove API via guzzlehttp/guzzle
   *
   * @param $body{Object}, the body of the json
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function createQuote($client, $store, $orderValue=100, $parking=0)
  {

    //"scheduleAt" => gmdate('Y-m-d\TH:i:s\Z', time() + 60 * 30), // ISOString with the format YYYY-MM-ddTHH:mm:ss.000Z at UTC time

    $body = array(
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
              "en_MX" => array(
                "displayString" => $store->getAddress(),
                "country" => "MX_MEX"                                   // Country code must follow the country you are at
              )   
            )   
          ),  
          array(
            "location" => array("lat" => $client->getAddressLat(), "lng" => $client->getAddressLon()),
            "addresses" => array(
              "en_MX" => array(
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
    
    $this->method = "POST";
    $this->path = "/v2/quotations";
    $this->body = $body;
    $this->host = $this->host;
    $this->key = $this->key;
    $this->secret = $this->secret;
    $this->country = $this->country;

    $promise = $this->asyncSend($this->quoteResult);

    return $promise;

  }

  public function getQuote()
  {
    
    if(empty($this->quoteResult))
    {
        return null;
    }
    else
    {
        return $this->quoteResult;
    }

  }


  /**
   * Make a http request to place an order at lalamove API via guzzlehttp/guzzle
   *
   * @param $body{Object}, the body of the json
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function postOrder($body)
  {
    $request = new Request();
    $request->method = "POST";
    $request->path = "/v2/orders";
    $request->body = $body;
    $request->host = $this->host;
    $request->key = $this->key;
    $request->secret = $this->secret;
    $request->country = $this->country;
    return $request->send();
  }

  /**
   * Make a http request to get the status of order
   *
   * @param $orderId(String), the customerOrderId of lalamove
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function getOrderStatus($orderId)
  {
    $request = new Request();
    $request->method = "GET";
    $request->path = "/v2/orders/".$orderId;
    $request->host = $this->host;
    $request->key = $this->key;
    $request->secret = $this->secret;
    $request->country = $this->country;
    return $request->send();
  }
  
  /**
   * Make a http request to get the driver Info
   *
   * @param $orderId(String), the customerOrderId of lalamove
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function getDriverInfo($orderId, $driverId)
  {
    $request = new Request();
    $request->method = "GET";
    $request->path = "/v2/orders/".$orderId."/drivers/".$driverId;
    $request->host = $this->host;
    $request->key = $this->key;
    $request->secret = $this->secret;
    $request->country = $this->country;
    return $request->send();
  }

  /**
   * Make a http request to get the driver Location
   *
   * @param $orderId(String), the customerOrderId of lalamove
   * @param $driverId(String), the id of the driver at lalamove
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function getDriverLocation($orderId, $driverId)
  {
    $request = new Request();
    $request->method = "GET";
    $request->path = "/v2/orders/".$orderId."/drivers/".$driverId."/location";
    $request->host = $this->host;
    $request->key = $this->key;
    $request->secret = $this->secret;
    $request->country = $this->country;
    return $request->send();
  }

  /**
   * Cancel the http request to get the driver location
   *
   * @param $orderId(String), the customerOrderId of lalamove
   * @return the http response from guzzlehttp/guzzle, an exception will not be thrown
   *   2xx - http request is successful
   *   4xx - unsuccessful request, see body for error message and documentation for matching
   *   5xx - server error, please contact lalamove
   */
  public function cancelOrder($orderId)
  {
    $request = new Request();
    $request->method = "PUT";
    $request->path = "/v2/orders/".$orderId."/cancel";
    $request->host = $this->host;
    $request->key = $this->key;
    $request->secret = $this->secret;
    $request->country = $this->country;
    return $request->send();
  }
}