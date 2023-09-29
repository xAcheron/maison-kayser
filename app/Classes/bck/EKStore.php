<?php

namespace App\Classes;
use App\Classes\Address;

class EKStore
{
    private $name ="";
    private $phone ="";
    private $remarks ="";
    private $address;

    public function __construct($name, $phone, $address, $lat, $lon, $remarks="")
    {
       $this->name = $name;
       $this->phone = $phone;
       $this->remarks = (!empty($remarks)?$remarks:"");
       $this->address = new Address($address,$lat,$lon);
    }

    public function getJson()
    {
        return '{
            name: "'.$this->name.'",
            phone: "'.$this->phone.'",
            remarks: "'.$this->remarks.'",
            address: "'.$this->address->getAddress().'",
        }';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getRemarks()
    {
        return $this->remarks;
    }

    public function getAddress()
    {
        return $this->address->getAddress();
    }
    
    public function getAddressLat()
    {
        return $this->address->getLat();
    }
    
    public function getAddressLon()
    {
        return $this->address->getLon();
    }
}