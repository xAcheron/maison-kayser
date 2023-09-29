<?php 

namespace App\Classes\Reports;

interface IReporter
{
    public function setParams($params);
    public function runReport();
    public function getResult($type);
}