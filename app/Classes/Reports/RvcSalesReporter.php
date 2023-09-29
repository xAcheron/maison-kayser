<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;

class RvcSalesReporter implements iReporter {
    private $initDate;
    private $endDate;
    private $location;
    private $result;
    private $initMonth;
    private $locationsID;

    public function setParams($params){
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = date("Y-m-d",  strtotime($tmpDates[0] ." -7 day")) ;
        $this->initMonth = $tmpDates[0] ;
        $this->endDate = $tmpDates[1];
        $this->location = $params["location"];
        $this->locationsID ="56,57,58,61,69,70";
    }

    private function diaSem($dia, $lan=1){
        return $dia == 0 ? ($lan==1?"Lunes":"Monday"):( $dia == 1 ? ($lan==1?"Martes":"Tuesday"):( $dia == 2 ? ($lan==1?"Miercoles":"Wednesday") : ($dia == 3 ? ($lan==1?"Jueves":"Thursday") : ($dia == 4 ? ($lan==1?"Viernes":"Friday") : ($dia == 5 ? ($lan==1?"Sabado":"Saturday") :  ($lan==1?"Domingo":"Sunday"))))));
    }

    public function runReport()
    {        
        $sql = "SELECT MAX(WEEKDAY(fecha)) DiaSemana ,fecha ,  MAX(WEEK(fecha,7)) semana,SUM(IF(rvc='Vitrina', netSales, 0)) 'qserv', SUM(IF(rvc='Servicio a Domicilio', netSales, 0)) 'delivery', SUM(IF(rvc='Catering', netSales, 0)) 'catering', SUM(netSales) total, SUM(A.checks) checks FROM vds_rvc A WHERE A.idSucursal IN (".$this->locationsID.") AND fecha BETWEEN ? AND ? GROUP BY fecha ORDER BY fecha;";
        $sales = DB::select($sql,[$this->initDate,$this->endDate]);
        $salesRvc = array();
        foreach($sales as $index => $dateSales) 
        {
            if($dateSales->fecha >= $this->initMonth)
            {
                $salesRvc[] = array( "fecha" => $dateSales->fecha , "diaSem" => $this->diaSem($dateSales->DiaSemana), "semana" => $dateSales->semana, "qserv" => $dateSales->qserv, "qservAnt" =>1-($dateSales->qserv/$sales[$index-7]->qserv), "delivery" => $dateSales->delivery,  "deliveryAnt" =>1-($dateSales->delivery/(!empty($sales[$index-7]->delivery)?$sales[$index-7]->delivery:1000)), "catering" => $dateSales->catering,  "cateringAnt" =>1-($dateSales->catering/(!empty($sales[$index-7]->catering)?$sales[$index-7]->catering:1000)), "total"=> $dateSales->total, "totalAnt" =>1-($dateSales->total/$sales[$index-7]->total), "checks"=> $dateSales->checks, "checksAnt" =>1-($dateSales->checks/(!empty($sales[$index-7]->checks)?$sales[$index-7]->checks:1000)),"avgcheck" => $dateSales->total/$dateSales->checks, "avgcheckAnt" =>1-(($dateSales->total/$dateSales->checks)/($sales[$index-7]->total/(!empty($sales[$index-7]->checks)?$sales[$index-7]->checks:1000))));
            }
        }
        $this->result = array( "salesRvc"=>$salesRvc, "Sales" => $sales);
        dd($this->result);
    }

    public function getResult($type){

    }

}