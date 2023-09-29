<?php

namespace App\Classes;

use GuzzleHttp\Client;
use App\Classes\ClientePickGo;
use App\Classes\EKStore;
use App\Classes\MensajerosUrbanosApi;
use App\Classes\LalaMoveApi;


class Delivery
{
    private $client;
    private $store;
    private $total;
    private $cost;
    private $provider;
    private $reference;

    public function __construct($total=300)
    {
        $this->client = null;
        $this->store = null;
        $this->total = $total;
        $this->cost = 50;
        $this->provider = null;
        $this->realCost = 50;
        $this->reference= "";
    }

    public function setClient($name, $phone, $address, $lat, $lng, $remarks)
    {
        $this->client = new ClientePickGo($name, $phone, $address, $lat, $lng, $remarks);
    }

    public function setStore($name, $phone, $address, $lat, $lng, $remarks)
    {
        $this->store = new EKStore($name, $phone, $address, $lat, $lng, $remarks);
    }
    
    public function getQuote()
    {
        
        $menurb = new MensajerosUrbanosApi();
        $promiseMensajeros = $menurb->createQuote($this->client, $this->store, $this->total, 0);
        $lala = new LalaMoveApi();
        $promiseLala = $lala->createQuote($this->client, $this->store, $this->total, 0);
        $promises = [
            'mensajeros' => $promiseMensajeros,
            'lala'   => $promiseLala
        ];

        $results = [];
                
        foreach ($promises as $key => $promise) {
            $results[$key] = $promise->wait(true);
        }

        $cot1 = $lala->getQuote();
        $cot2 = $menurb->getQuote();

        if($cot1->quote > 0 || $cot2->quote > 0)
        {
            if($cot1->quote > $cot2->quote)
            {
                $this->cost = $cot1->quote;
                $this->provider = $cot2->provider;
                $this->realCost = $cot2->quote;
            }
            else
            {
                $this->cost = $cot2->quote;
                $this->provider = $cot1->provider;
                $this->realCost = $cot1->quote;
            }

        }
        else
        {
            $this->cost = 0;
            $this->provider = "na";
            $this->realCost = 0;
        }

        return json_decode('{ "cost": '.$this->cost.', "provider": "'.$this->provider.'", "realCost": '.$this->realCost.', "reference": "'.$this->reference.'" }');

    }

}