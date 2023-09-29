<?php

namespace App\Classes;
use App\Classes\Address;

class EKStore
{
    private $name ="";
    private $phone ="";
    private $remarks ="";
    private $address;
    private $id;

    public function __construct($name, $phone, $address, $lat, $lon, $remarks="", $id)
    {
       $this->name = $name;
       $this->phone = $phone;
       $this->remarks = (!empty($remarks)?$remarks:"");
       $this->address = new Address($address,$lat,$lon);
       $this->id = array( "MensajerosUrbanos" => $id, "Micros" => $id );
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

    public function getId($type="Micros")
    {
        return !empty($this->ids[$type])?$this->ids[$type]:"Invalid";
    }
}