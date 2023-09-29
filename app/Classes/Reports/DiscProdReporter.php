<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DiscProdReporter implements iReporter 
{
    
    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $result;
    private $discount;
    private $tier;

    public function setParams($params)
    {
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        $this->location = $params["location"];
        $this->tier = empty($params["tier"])?0:$params["tier"];
        $this->discount = empty($params["discount"])?0:$params["discount"];

        if(is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);
        
        $this->location = $locations[0];
        $this->locationID = $locations[1];
    }
    
    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql,[$idLocation]);
        return array( "'".$locations[0]->idMicros."'", $locations[0]->id );
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT * FROM sucursales WHERE ".(empty($this->tier) || $this->tier == "null" ?"": " idTier = ".$this->tier." AND ")." idEmpresa = ?;";
        $locations = DB::select($sql,[$idEmpresa]);
        
        $locationArr = array();
        $locationIDArr = array();

        foreach($locations as $location)
        {
            $locationArr[] = "'".$location->idMicros."'";
            $locationIDArr[] = "'".$location->id."'";
        }
        return array(implode(",",$locationArr), implode(",",$locationIDArr));
        
    }

    public function runReport()
    {
        $sql = "SELECT MP.idItemMicros ,MP.itemName, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento_producto DSC INNER JOIN micros_producto MP ON DSC.idArticulo = MP.idItemMicros WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.idDescuento = ? AND DSC.fecha BETWEEN ? AND ? GROUP BY MP.idItemMicros ,MP.itemName ORDER BY descuento;";
        $discounts = DB::select($sql,[$this->discount,$this->initDate, $this->endDate]);
        $this->result = json_decode(json_encode($discounts));
    }

    public function getResult($type)
    {
        if($type == "xlsx")
        {
            $this->exportReport();
        }
        else
        {
            $parser = new ReportParser($type);
            return $parser->parse($this->result);
        }
    }

    public function exportReport()
    {

    }

}