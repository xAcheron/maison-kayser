<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;

class CashReporter implements iReporter {

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
        $sql = "SELECT tender, netSales, fecha FROM vds_tender WHERE tender = 'cash' AND idSucursal= ? AND fecha BETWEEN ? AND ? ORDER BY fecha;";
        $this->result = DB::select($sql,[$this->location, $this->initDate, $this->endDate]);
        $netSales=0;
        foreach($this->result as $row){
            $netSales +=$row->netSales;
        }
        $this->result[] = json_decode(json_encode(array("tender" => 'Total', "fecha" => "Total", "netSales" =>$netSales )));
    }

    public function getResult($type){
        $parser = new ReportParser($type);
        return $parser->parse($this->result);
    }

}