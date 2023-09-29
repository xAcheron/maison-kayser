<?php 

namespace App\Classes\Reports;

interface IReportParser
{
    public function parse($content,$params);
}