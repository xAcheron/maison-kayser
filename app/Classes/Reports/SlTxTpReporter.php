<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;

class SlTxTpReporter implements iReporter {

    private $initDate;
    private $endDate;
    private $location;
    private $result;

    public function setParams($params){
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        $this->location = $params["location"];
    }

    public function runReport(){
        $sql = "SELECT taxCollected, totalDiscount, serviceCharges, amountChecks, netSales, fecha FROM venta_diaria_sucursal WHERE idSucursal= ? AND fecha BETWEEN ? AND ? ORDER BY fecha;";
        $this->result = DB::select($sql,[$this->location, $this->initDate, $this->endDate]);
        $taxCollected = 0;
        $totalDiscount = 0;
        $serviceCharges = 0; 
        $amountChecks = 0;
        $netSales = 0;
        foreach($this->result as $row){
            $taxCollected +=$row->taxCollected ;
            $totalDiscount+=$row->totalDiscount ;
            $serviceCharges+=$row->serviceCharges; 
            $amountChecks+=$row->amountChecks;
            $netSales+=$row->netSales;
        }
        $this->result[] = json_decode(json_encode(array("taxCollected" =>$taxCollected, "totalDiscount" =>$totalDiscount, "serviceCharges" =>$serviceCharges, "amountChecks" =>$amountChecks, "netSales" =>$netSales, "fecha" => "Total")));
    }

    public function getResult($type){
        $parser = new ReportParser($type);
        return $parser->parse($this->result);
    }

}