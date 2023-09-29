<?php

namespace App\Classes;

class Address
{
    private $address ="";
    private $lat = 0;
    private $lon = 0;

    public function __construct($address, $lat, $lon)
    {
       $this->address = $address;
       $this->lat = $lat;
       $this->lon = $lon;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getLat()
    {
        return (string) $this->lat;
    }

    public function getLon()
    {
        return (string) $this->lon;
    }
}