<?php

namespace App\Classes;
use DateTime;

class Order
{
    private $orderNumber;
    private $orderValue;
    private $dateTime;

    public function __construct($orderNumber, $orderValue)
    {
       $this->orderNumber = $orderNumber;
       $this->orderValue = $orderValue;
       $this->dateTime = new DateTime('NOW');
    }

    public function getValue()
    {
        return $this->orderValue;
    }
    
    public function getOrderNum()
    {
        return $this->orderNumber;
    }

    public function getDate()
    {
        return $this->dateTime->format('Y-m-d');
    }

    public function getTime()
    {

        return $this->dateTime->format('H:i:s');
    }
}