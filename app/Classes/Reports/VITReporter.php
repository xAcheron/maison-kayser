<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Auth;

class VITReporter implements iReporter {

    private $initDate;
    private $endDate;
    private $initDateLw;
    private $endDateLw;
    private $location;
    private $locationID;
    private $result;
    private $tier;
    private $split;
    private $company;
    private $storelabel;

    public function setParams($params)
    {
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];

        $this->split = $params["desglosar"] == "true"?1:0;

        $date = strtotime($tmpDates[0]);

        $this->initDateLw = date('Y-m-d', strtotime("-7 day", $date));
        $this->endDateLw = date('Y-m-d', strtotime("-1 day", $date));

        $this->location = $params["location"];
        $this->tier = empty($params["tier"])?0:$params["tier"];
        
        if(is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);
        
        $this->location = $locations[0];
        $this->locationID = $locations[1];
        $this->company = $locations[2];
        $this->perSales = empty($params["perSales"])?100:$params["perSales"];
        $this->storelabel = $locations[2];
    }
    
    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql,[$idLocation]);
        
        return array( "'".$locations[0]->idMicros."'", $locations[0]->id,  $locations[0]->idEmpresa);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT S.*, empresas.comun, T.tier FROM sucursales S LEFT JOIN sucursales_tier T ON S.idTier = T.idTier INNER JOIN empresas ON empresas.idEmpresa = S.idEmpresa WHERE ".(empty($this->tier) || $this->tier == "null" ?"": " S.idTier = ".$this->tier." AND ")." S.idEmpresa = ?;";
        $locations = DB::select($sql,[$idEmpresa]);
        
        $locationArr = array();
        $locationIDArr = array();

        foreach($locations as $location)
        {
            $locationArr[] = "'".$location->idMicros."'";
            $locationIDArr[] = "'".$location->id."'";
            $empresa = (empty($this->tier) || $this->tier == "null" ?$location->comun:$location->tier);
        }

        return array(implode(",",$locationArr), implode(",",$locationIDArr), $idEmpresa);
        
    }

    public function runReport(){

        //$sql = "SELECT G.rvc, SUM(G.guestsBreakfast), SUM(G.guestsLunch), SUM(G.guestsDinner), SUM(G.guestsNight),  SUM(G.netSalesBreakfast), SUM(G.netSalesLunch), SUM(G.netSalesDinner), SUM(G.netSalesNight),SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1), SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1), SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1), SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) FROM vds_guests G WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ? GROUP BY G.rvc";
        $sql = "SELECT 
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsBreakfast,0)) gbl, 
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsBreakfast,0)) gbm, 
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsBreakfast,0)) gbmr, 
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsBreakfast,0)) gbj, 
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsBreakfast,0)) gbv, 
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsBreakfast,0)) gbs, 
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsBreakfast,0)) gbd,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsLunch,0)) gll,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsLunch,0)) glm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsLunch,0)) glmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsLunch,0)) glj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsLunch,0)) glv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsLunch,0)) gls,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsLunch,0)) gld,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsDinner,0)) gdl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsDinner,0)) gdm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsDinner,0)) gdmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsDinner,0)) gdj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsDinner,0)) gdv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsDinner,0)) gds,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsDinner,0)) gdd,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsNight,0)) gnl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsNight,0)) gnm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsNight,0)) gnmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsNight,0)) gnj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsNight,0)) gnv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsNight,0)) gns,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsNight,0)) gnd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesBreakfast,0)) nsbl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesBreakfast,0)) nsbm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesBreakfast,0)) nsbmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesBreakfast,0)) nsbj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesBreakfast,0)) nsbv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesBreakfast,0)) nsbs,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesBreakfast,0)) nsbd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesLunch,0)) nsll,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesLunch,0)) nslm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesLunch,0)) nslmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesLunch,0)) nslj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesLunch,0)) nslv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesLunch,0)) nsls,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesLunch,0)) nsld,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesDinner,0)) nsdl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesDinner,0)) nsdm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesDinner,0)) nsdmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesDinner,0)) nsdj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesDinner,0)) nsdv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesDinner,0)) nsds,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesDinner,0)) nsdd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesNight,0)) nsnl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesNight,0)) nsnm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesNight,0)) nsnmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesNight,0)) nsnj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesNight,0)) nsnv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesNight,0)) nsns,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesNight,0)) nsnd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsBreakfast,0)),1) avgbl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsBreakfast,0)),1) avgbm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsBreakfast,0)),1) avgbmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsBreakfast,0)),1) avgbj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsBreakfast,0)),1) avgbv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsBreakfast,0)),1) avgbs,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsBreakfast,0)),1) avgbd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsLunch,0)),1) avgll,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsLunch,0)),1) avglm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsLunch,0)),1) avglmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsLunch,0)),1) avglj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsLunch,0)),1) avglv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsLunch,0)),1) avgls,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsLunch,0)),1) avgld,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsDinner,0)),1) avgdl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsDinner,0)),1) avgdm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsDinner,0)),1) avgdmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsDinner,0)),1) avgdj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsDinner,0)),1) avgdv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsDinner,0)),1) avgds,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsDinner,0)),1) avgdd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsNight,0)),1) avgnl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsNight,0)),1) avgnm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsNight,0)),1) avgnmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsNight,0)),1) avgnj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsNight,0)),1) avgnv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsNight,0)),1) avgns,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsNight,0)),1) avgnd,
        SUM(G.guestsBreakfast) gb,
        SUM(G.guestsLunch) gl,
        SUM(G.guestsDinner) gd,
        SUM(G.guestsNight) gn,
        SUM(G.netSalesBreakfast) nsb,
        SUM(G.netSalesLunch) nsl,
        SUM(G.netSalesDinner) nsd,
        SUM(G.netSalesNight) nsn,
        COALESCE(SUM(G.netSalesBreakfast),0)/ COALESCE(SUM(G.guestsBreakfast),1) avgb,
        COALESCE(SUM(G.netSalesLunch),0)/ COALESCE(SUM(G.guestsLunch),1) avgl,
        COALESCE(SUM(G.netSalesDinner),0)/ COALESCE(SUM(G.guestsDinner),1) avgd,
        COALESCE(SUM(G.netSalesNight),0)/ COALESCE(SUM(G.guestsNight),1) avgn,
        ".($this->split==1?" S.nombre ":" '".$this->storelabel."'")."  AS sucursal
        FROM vds_guests G".($this->split==1?" INNER JOIN sucursales S ON S.id = G.idSucursal ":"")."
        WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ?
        GROUP BY YEAR(fecha)".($this->split==1?" , idSucursal,S.nombre":"").";";
        $rvc = DB::select($sql,[$this->initDate, $this->endDate]);

        $sql = "SELECT 
        SUM(G.guestsBreakfast) gb,
        SUM(G.guestsLunch) gl,
        SUM(G.guestsDinner) gd,
        SUM(G.guestsNight) gn,
        SUM(G.netSalesBreakfast) nsb,
        SUM(G.netSalesLunch) nsl,
        SUM(G.netSalesDinner) nsd,
        SUM(G.netSalesNight) nsn,
        COALESCE(SUM(G.netSalesBreakfast),0)/ COALESCE(SUM(G.guestsBreakfast),1) avgb,
        COALESCE(SUM(G.netSalesLunch),0)/ COALESCE(SUM(G.guestsLunch),1) avgl,
        COALESCE(SUM(G.netSalesDinner),0)/ COALESCE(SUM(G.guestsDinner),1) avgd,
        COALESCE(SUM(G.netSalesNight),0)/ COALESCE(SUM(G.guestsNight),1) avgn,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsBreakfast,0)) gbl, 
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsBreakfast,0)) gbm, 
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsBreakfast,0)) gbmr, 
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsBreakfast,0)) gbj, 
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsBreakfast,0)) gbv, 
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsBreakfast,0)) gbs, 
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsBreakfast,0)) gbd,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsLunch,0)) gll,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsLunch,0)) glm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsLunch,0)) glmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsLunch,0)) glj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsLunch,0)) glv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsLunch,0)) gls,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsLunch,0)) gld,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsDinner,0)) gdl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsDinner,0)) gdm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsDinner,0)) gdmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsDinner,0)) gdj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsDinner,0)) gdv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsDinner,0)) gds,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsDinner,0)) gdd,
        SUM(IF(DAYOFWEEK(fecha)=2, G.guestsNight,0)) gnl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.guestsNight,0)) gnm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.guestsNight,0)) gnmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.guestsNight,0)) gnj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.guestsNight,0)) gnv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.guestsNight,0)) gns,
        SUM(IF(DAYOFWEEK(fecha)=1, G.guestsNight,0)) gnd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesBreakfast,0)) nsbl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesBreakfast,0)) nsbm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesBreakfast,0)) nsbmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesBreakfast,0)) nsbj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesBreakfast,0)) nsbv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesBreakfast,0)) nsbs,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesBreakfast,0)) nsbd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesLunch,0)) nsll,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesLunch,0)) nslm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesLunch,0)) nslmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesLunch,0)) nslj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesLunch,0)) nslv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesLunch,0)) nsls,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesLunch,0)) nsld,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesDinner,0)) nsdl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesDinner,0)) nsdm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesDinner,0)) nsdmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesDinner,0)) nsdj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesDinner,0)) nsdv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesDinner,0)) nsds,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesDinner,0)) nsdd,        
        SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesNight,0)) nsnl,
        SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesNight,0)) nsnm,
        SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesNight,0)) nsnmr,
        SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesNight,0)) nsnj,
        SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesNight,0)) nsnv,
        SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesNight,0)) nsns,
        SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesNight,0)) nsnd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsBreakfast,0)),1) avgbl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsBreakfast,0)),1) avgbm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsBreakfast,0)),1) avgbmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsBreakfast,0)),1) avgbj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsBreakfast,0)),1) avgbv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsBreakfast,0)),1) avgbs,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesBreakfast,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsBreakfast,0)),1) avgbd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsLunch,0)),1) avgll,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsLunch,0)),1) avglm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsLunch,0)),1) avglmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsLunch,0)),1) avglj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsLunch,0)),1) avglv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsLunch,0)),1) avgls,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesLunch,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsLunch,0)),1) avgld,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsDinner,0)),1) avgdl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsDinner,0)),1) avgdm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsDinner,0)),1) avgdmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsDinner,0)),1) avgdj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsDinner,0)),1) avgdv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsDinner,0)),1) avgds,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesDinner,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsDinner,0)),1) avgdd,        
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=2, G.guestsNight,0)),1) avgnl,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=3, G.guestsNight,0)),1) avgnm,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=4, G.guestsNight,0)),1) avgnmr,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=5, G.guestsNight,0)),1) avgnj,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=6, G.guestsNight,0)),1) avgnv,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=7, G.guestsNight,0)),1) avgns,
        COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.netSalesNight,0)),0)/ COALESCE(SUM(IF(DAYOFWEEK(fecha)=1, G.guestsNight,0)),1) avgnd,
        ".($this->split==1?" S.nombre ":" '".$this->storelabel."'")."  AS sucursal
        FROM vds_guests G".($this->split==1?" INNER JOIN sucursales S ON S.id = G.idSucursal ":"")."
        WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ?
        GROUP BY YEAR(fecha)".($this->split==1?" , idSucursal,S.nombre":"").";";
        $rvclw = DB::select($sql,[$this->initDateLw, $this->endDateLw]);
        
        foreach($rvclw AS $index =>$val)
        {

            $rvclw[$index]->gb = (1 -($rvclw[$index]->gb/(!empty($rvc[$index]->gb)?$rvc[$index]->gb:1)))*100 ;
            $rvclw[$index]->gl = (1 -($rvclw[$index]->gl/(!empty($rvc[$index]->gl)?$rvc[$index]->gl:1)))*100 ;
            $rvclw[$index]->gd = (1 -($rvclw[$index]->gd/(!empty($rvc[$index]->gd)?$rvc[$index]->gd:1)))*100 ;
            $rvclw[$index]->gn = (1 -($rvclw[$index]->gn/(!empty($rvc[$index]->gn)?$rvc[$index]->gn:1)))*100 ;
            $rvclw[$index]->nsb = (1 -($rvclw[$index]->nsb/(!empty($rvc[$index]->nsb)?$rvc[$index]->nsb:1)))*100 ;
            $rvclw[$index]->nsl = (1 -($rvclw[$index]->nsl/(!empty($rvc[$index]->nsl)?$rvc[$index]->nsl:1)))*100 ;
            $rvclw[$index]->nsd = (1 -($rvclw[$index]->nsd/(!empty($rvc[$index]->nsd)?$rvc[$index]->nsd:1)))*100 ;
            $rvclw[$index]->nsn = (1 -($rvclw[$index]->nsn/(!empty($rvc[$index]->nsn)?$rvc[$index]->nsn:1)))*100 ;
            $rvclw[$index]->avgb = (1 -($rvclw[$index]->avgb/(!empty($rvc[$index]->avgb)?$rvc[$index]->avgb:1)))*100 ;
            $rvclw[$index]->avgl = (1 -($rvclw[$index]->avgl/(!empty($rvc[$index]->avgl)?$rvc[$index]->avgl:1)))*100 ;
            $rvclw[$index]->avgd = (1 -($rvclw[$index]->avgd/(!empty($rvc[$index]->avgd)?$rvc[$index]->avgd:1)))*100 ;
            $rvclw[$index]->avgn = (1 -($rvclw[$index]->avgn/(!empty($rvc[$index]->avgn)?$rvc[$index]->avgn:1)))*100 ;        
            $rvclw[$index]->gbl = (1 -($rvclw[$index]->gbl/(!empty($rvc[$index]->gbl)?$rvc[$index]->gbl:1)))*100;
            $rvclw[$index]->gbm = (1 -($rvclw[$index]->gbm/(!empty($rvc[$index]->gbm)?$rvc[$index]->gbm:1)))*100;
            $rvclw[$index]->gbmr = (1 -($rvclw[$index]->gbmr/(!empty($rvc[$index]->gbmr)?$rvc[$index]->gbmr:1)))*100;
            $rvclw[$index]->gbj = (1 -($rvclw[$index]->gbj/(!empty($rvc[$index]->gbj)?$rvc[$index]->gbj:1)))*100;
            $rvclw[$index]->gbv = (1 -($rvclw[$index]->gbv/(!empty($rvc[$index]->gbv)?$rvc[$index]->gbv:1)))*100 ;
            $rvclw[$index]->gbs = (1 -($rvclw[$index]->gbs/(!empty($rvc[$index]->gbs)?$rvc[$index]->gbs:1)))*100 ;
            $rvclw[$index]->gbd = (1 -($rvclw[$index]->gbd/(!empty($rvc[$index]->gbd)?$rvc[$index]->gbd:1)))*100 ;
            $rvclw[$index]->gll = (1 -($rvclw[$index]->gll/(!empty($rvc[$index]->gll)?$rvc[$index]->gll:1)))*100 ;
            $rvclw[$index]->glm = (1 -($rvclw[$index]->glm/(!empty($rvc[$index]->glm)?$rvc[$index]->glm:1)))*100 ;
            $rvclw[$index]->glmr = (1 -($rvclw[$index]->glmr/(!empty($rvc[$index]->glmr)?$rvc[$index]->glmr:1)))*100 ;
            $rvclw[$index]->glj = (1 -($rvclw[$index]->glj/(!empty($rvc[$index]->glj)?$rvc[$index]->glj:1)))*100 ;
            $rvclw[$index]->glv = (1 -($rvclw[$index]->glv/(!empty($rvc[$index]->glv)?$rvc[$index]->glv:1)))*100 ;
            $rvclw[$index]->gls = (1 -($rvclw[$index]->gls/(!empty($rvc[$index]->gls)?$rvc[$index]->gls:1)))*100 ;
            $rvclw[$index]->gld = (1 -($rvclw[$index]->gld/(!empty($rvc[$index]->gld)?$rvc[$index]->gld:1)))*100 ;
            $rvclw[$index]->gdl = (1 -($rvclw[$index]->gdl/(!empty($rvc[$index]->gdl)?$rvc[$index]->gdl:1)))*100 ;
            $rvclw[$index]->gdm = (1 -($rvclw[$index]->gdm/(!empty($rvc[$index]->gdm)?$rvc[$index]->gdm:1)))*100 ;
            $rvclw[$index]->gdmr = (1 -($rvclw[$index]->gdmr/(!empty($rvc[$index]->gdmr)?$rvc[$index]->gdmr:1)))*100 ;
            $rvclw[$index]->gdj = (1 -($rvclw[$index]->gdj/(!empty($rvc[$index]->gdj)?$rvc[$index]->gdj:1)))*100 ;
            $rvclw[$index]->gdv = (1 -($rvclw[$index]->gdv/(!empty($rvc[$index]->gdv)?$rvc[$index]->gdv:1)))*100 ;
            $rvclw[$index]->gds = (1 -($rvclw[$index]->gds/(!empty($rvc[$index]->gds)?$rvc[$index]->gds:1)))*100 ;
            $rvclw[$index]->gdd = (1 -($rvclw[$index]->gdd/(!empty($rvc[$index]->gdd)?$rvc[$index]->gdd:1)))*100 ;
            $rvclw[$index]->gnl = (1 -($rvclw[$index]->gnl/(!empty($rvc[$index]->gnl)?$rvc[$index]->gnl:1)))*100 ;
            $rvclw[$index]->gnm = (1 -($rvclw[$index]->gnm/(!empty($rvc[$index]->gnm)?$rvc[$index]->gnm:1)))*100 ;
            $rvclw[$index]->gnmr = (1 -($rvclw[$index]->gnmr/(!empty($rvc[$index]->gnmr)?$rvc[$index]->gnmr:1)))*100 ;
            $rvclw[$index]->gnj = (1 -($rvclw[$index]->gnj/(!empty($rvc[$index]->gnj)?$rvc[$index]->gnj:1)))*100 ;
            $rvclw[$index]->gnv = (1 -($rvclw[$index]->gnv/(!empty($rvc[$index]->gnv)?$rvc[$index]->gnv:1)))*100 ;
            $rvclw[$index]->gns = (1 -($rvclw[$index]->gns/(!empty($rvc[$index]->gns)?$rvc[$index]->gns:1)))*100 ;
            $rvclw[$index]->gnd = (1 -($rvclw[$index]->gnd/(!empty($rvc[$index]->gnd)?$rvc[$index]->gnd:1)))*100 ;
            $rvclw[$index]->nsbl = (1 -($rvclw[$index]->nsbl/(!empty($rvc[$index]->nsbl)?$rvc[$index]->nsbl:1)))*100 ;
            $rvclw[$index]->nsbm = (1 -($rvclw[$index]->nsbm/(!empty($rvc[$index]->nsbm)?$rvc[$index]->nsbm:1)))*100 ;
            $rvclw[$index]->nsbmr = (1 -($rvclw[$index]->nsbmr/(!empty($rvc[$index]->nsbmr)?$rvc[$index]->nsbmr:1)))*100 ;
            $rvclw[$index]->nsbj = (1 -($rvclw[$index]->nsbj/(!empty($rvc[$index]->nsbj)?$rvc[$index]->nsbj:1)))*100 ;
            $rvclw[$index]->nsbv = (1 -($rvclw[$index]->nsbv/(!empty($rvc[$index]->nsbv)?$rvc[$index]->nsbv:1)))*100 ;
            $rvclw[$index]->nsbs = (1 -($rvclw[$index]->nsbs/(!empty($rvc[$index]->nsbs)?$rvc[$index]->nsbs:1)))*100 ;
            $rvclw[$index]->nsbd = (1 -($rvclw[$index]->nsbd/(!empty($rvc[$index]->nsbd)?$rvc[$index]->nsbd:1)))*100 ;
            $rvclw[$index]->nsll = (1 -($rvclw[$index]->nsll/(!empty($rvc[$index]->nsll)?$rvc[$index]->nsll:1)))*100 ;
            $rvclw[$index]->nslm = (1 -($rvclw[$index]->nslm/(!empty($rvc[$index]->nslm)?$rvc[$index]->nslm:1)))*100 ;
            $rvclw[$index]->nslmr = (1 -($rvclw[$index]->nslmr/(!empty($rvc[$index]->nslmr)?$rvc[$index]->nslmr:1)))*100 ;
            $rvclw[$index]->nslj = (1 -($rvclw[$index]->nslj/(!empty($rvc[$index]->nslj)?$rvc[$index]->nslj:1)))*100 ;
            $rvclw[$index]->nslv = (1 -($rvclw[$index]->nslv/(!empty($rvc[$index]->nslv)?$rvc[$index]->nslv:1)))*100 ;
            $rvclw[$index]->nsls = (1 -($rvclw[$index]->nsls/(!empty($rvc[$index]->nsls)?$rvc[$index]->nsls:1)))*100 ;
            $rvclw[$index]->nsld = (1 -($rvclw[$index]->nsld/(!empty($rvc[$index]->nsld)?$rvc[$index]->nsld:1)))*100 ;
            $rvclw[$index]->nsdl = (1 -($rvclw[$index]->nsdl/(!empty($rvc[$index]->nsdl)?$rvc[$index]->nsdl:1)))*100 ;
            $rvclw[$index]->nsdm = (1 -($rvclw[$index]->nsdm/(!empty($rvc[$index]->nsdm)?$rvc[$index]->nsdm:1)))*100 ;
            $rvclw[$index]->nsdmr = (1 -($rvclw[$index]->nsdmr/(!empty($rvc[$index]->nsdmr)?$rvc[$index]->nsdmr:1)))*100 ;
            $rvclw[$index]->nsdj = (1 -($rvclw[$index]->nsdj/(!empty($rvc[$index]->nsdj)?$rvc[$index]->nsdj:1)))*100 ;
            $rvclw[$index]->nsdv = (1 -($rvclw[$index]->nsdv/(!empty($rvc[$index]->nsdv)?$rvc[$index]->nsdv:1)))*100 ;
            $rvclw[$index]->nsds = (1 -($rvclw[$index]->nsds/(!empty($rvc[$index]->nsds)?$rvc[$index]->nsds:1)))*100 ;
            $rvclw[$index]->nsdd = (1 -($rvclw[$index]->nsdd/(!empty($rvc[$index]->nsdd)?$rvc[$index]->nsdd:1)))*100 ;
            $rvclw[$index]->nsnl = (1 -($rvclw[$index]->nsnl/(!empty($rvc[$index]->nsnl)?$rvc[$index]->nsnl:1)))*100 ;
            $rvclw[$index]->nsnm = (1 -($rvclw[$index]->nsnm/(!empty($rvc[$index]->nsnm)?$rvc[$index]->nsnm:1)))*100 ;
            $rvclw[$index]->nsnmr = (1 -($rvclw[$index]->nsnmr/(!empty($rvc[$index]->nsnmr)?$rvc[$index]->nsnmr:1)))*100 ;
            $rvclw[$index]->nsnj = (1 -($rvclw[$index]->nsnj/(!empty($rvc[$index]->nsnj)?$rvc[$index]->nsnj:1)))*100 ;
            $rvclw[$index]->nsnv = (1 -($rvclw[$index]->nsnv/(!empty($rvc[$index]->nsnv)?$rvc[$index]->nsnv:1)))*100 ;
            $rvclw[$index]->nsns = (1 -($rvclw[$index]->nsns/(!empty($rvc[$index]->nsns)?$rvc[$index]->nsns:1)))*100 ;
            $rvclw[$index]->nsnd = (1 -($rvclw[$index]->nsnd/(!empty($rvc[$index]->nsnd)?$rvc[$index]->nsnd:1)))*100 ;
            $rvclw[$index]->avgbl = (1 -($rvclw[$index]->avgbl/(!empty($rvc[$index]->avgbl)?$rvc[$index]->avgbl:1)))*100 ;
            $rvclw[$index]->avgbm = (1 -($rvclw[$index]->avgbm/(!empty($rvc[$index]->avgbm)?$rvc[$index]->avgbm:1)))*100 ;
            $rvclw[$index]->avgbmr = (1 -($rvclw[$index]->avgbmr/(!empty($rvc[$index]->avgbmr)?$rvc[$index]->avgbmr:1)))*100 ;
            $rvclw[$index]->avgbj = (1 -($rvclw[$index]->avgbj/(!empty($rvc[$index]->avgbj)?$rvc[$index]->avgbj:1)))*100 ;
            $rvclw[$index]->avgbv = (1 -($rvclw[$index]->avgbv/(!empty($rvc[$index]->avgbv)?$rvc[$index]->avgbv:1)))*100 ;
            $rvclw[$index]->avgbs = (1 -($rvclw[$index]->avgbs/(!empty($rvc[$index]->avgbs)?$rvc[$index]->avgbs:1)))*100 ;
            $rvclw[$index]->avgbd = (1 -($rvclw[$index]->avgbd/(!empty($rvc[$index]->avgbd)?$rvc[$index]->avgbd:1)))*100 ;
            $rvclw[$index]->avgll = (1 -($rvclw[$index]->avgll/(!empty($rvc[$index]->avgll)?$rvc[$index]->avgll:1)))*100 ;
            $rvclw[$index]->avglm = (1 -($rvclw[$index]->avglm/(!empty($rvc[$index]->avglm)?$rvc[$index]->avglm:1)))*100 ;
            $rvclw[$index]->avglmr = (1 -($rvclw[$index]->avglmr/(!empty($rvc[$index]->avglmr)?$rvc[$index]->avglmr:1)))*100 ;
            $rvclw[$index]->avglj = (1 -($rvclw[$index]->avglj/(!empty($rvc[$index]->avglj)?$rvc[$index]->avglj:1)))*100 ;
            $rvclw[$index]->avglv = (1 -($rvclw[$index]->avglv/(!empty($rvc[$index]->avglv)?$rvc[$index]->avglv:1)))*100 ;
            $rvclw[$index]->avgls = (1 -($rvclw[$index]->avgls/(!empty($rvc[$index]->avgls)?$rvc[$index]->avgls:1)))*100 ;
            $rvclw[$index]->avgld = (1 -($rvclw[$index]->avgld/(!empty($rvc[$index]->avgld)?$rvc[$index]->avgld:1)))*100 ;
            $rvclw[$index]->avgdl = (1 -($rvclw[$index]->avgdl/(!empty($rvc[$index]->avgdl)?$rvc[$index]->avgdl:1)))*100 ;
            $rvclw[$index]->avgdm = (1 -($rvclw[$index]->avgdm/(!empty($rvc[$index]->avgdm)?$rvc[$index]->avgdm:1)))*100 ;
            $rvclw[$index]->avgdmr = (1 -($rvclw[$index]->avgdmr/(!empty($rvc[$index]->avgdmr)?$rvc[$index]->avgdmr:1)))*100 ;
            $rvclw[$index]->avgdj = (1 -($rvclw[$index]->avgdj/(!empty($rvc[$index]->avgdj)?$rvc[$index]->avgdj:1)))*100 ;
            $rvclw[$index]->avgdv = (1 -($rvclw[$index]->avgdv/(!empty($rvc[$index]->avgdv)?$rvc[$index]->avgdv:1)))*100 ;
            $rvclw[$index]->avgds = (1 -($rvclw[$index]->avgds/(!empty($rvc[$index]->avgds)?$rvc[$index]->avgds:1)))*100 ;
            $rvclw[$index]->avgdd = (1 -($rvclw[$index]->avgdd/(!empty($rvc[$index]->avgdd)?$rvc[$index]->avgdd:1)))*100 ;
            $rvclw[$index]->avgnl = (1 -($rvclw[$index]->avgnl/(!empty($rvc[$index]->avgnl)?$rvc[$index]->avgnl:1)))*100 ;
            $rvclw[$index]->avgnm = (1 -($rvclw[$index]->avgnm/(!empty($rvc[$index]->avgnm)?$rvc[$index]->avgnm:1)))*100 ;
            $rvclw[$index]->avgnmr = (1 -($rvclw[$index]->avgnmr/(!empty($rvc[$index]->avgnmr)?$rvc[$index]->avgnmr:1)))*100 ;
            $rvclw[$index]->avgnj = (1 -($rvclw[$index]->avgnj/(!empty($rvc[$index]->avgnj)?$rvc[$index]->avgnj:1)))*100 ;
            $rvclw[$index]->avgnv = (1 -($rvclw[$index]->avgnv/(!empty($rvc[$index]->avgnv)?$rvc[$index]->avgnv:1)))*100 ;
            $rvclw[$index]->avgns = (1 -($rvclw[$index]->avgns/(!empty($rvc[$index]->avgns)?$rvc[$index]->avgns:1)))*100 ;
            $rvclw[$index]->avgnd = (1 -($rvclw[$index]->avgnd/(!empty($rvc[$index]->avgnd)?$rvc[$index]->avgnd:1)))*100 ;
        }
        $this->result = json_decode(json_encode(array("dps" => $rvc, "dpslw" => $rvclw, "store" => $this->storelabel )));

    }

    public function getResult($type){
        if($type == "xlsx"){
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

        $rvc = $this->result->dps;

        $rvclw = $this->result->dpslw;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $currentSheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(13);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);

        $currentSheet->mergeCells('B2:D2');
        $currentSheet->mergeCells('B3:D3');
        $currentSheet->mergeCells('B4:D4');
        $currentSheet->mergeCells('B5:D5');
        $currentSheet->mergeCells('B6:D6');

        $spreadsheet->getActiveSheet()->getStyle('A2:A6')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


        $spreadsheet->getActiveSheet()->getStyle('A2:D6')->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );    
        
        $spreadsheet->getActiveSheet()->getStyle('A8:Q9')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('ffe3e3e3');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Day Part');
        $currentSheet->setCellValue('A4', 'Day Part LW');
        $currentSheet->setCellValue('A5', 'Location');
        $currentSheet->setCellValue('A6', 'Export Date');
    
        $currentSheet->setCellValue('B2', 'Daypart VIT');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', $this->initDateLw . " - " . $this->endDateLw);
        $currentSheet->setCellValue('B5', strtoupper($this->location));
        $currentSheet->setCellValue('B6', date("Y-m-d"));

        $currentSheet->setCellValue('A8', 'Guests');
        $currentSheet->setCellValue('A9', 'Daypart');
        $currentSheet->setCellValue('B9', 'Tienda');
        $currentSheet->setCellValue('C9', 'Lunes');
        $currentSheet->setCellValue('D9', '% LW');
        $currentSheet->setCellValue('E9', 'Martes');
        $currentSheet->setCellValue('F9', '% LW');
        $currentSheet->setCellValue('G9', 'Miercoles');
        $currentSheet->setCellValue('H9', '% LW');
        $currentSheet->setCellValue('I9', 'Jueves');
        $currentSheet->setCellValue('J9', '% LW');
        $currentSheet->setCellValue('K9', 'Viernes');
        $currentSheet->setCellValue('L9', '% LW');
        $currentSheet->setCellValue('M9', 'Sabado');
        $currentSheet->setCellValue('N9', '% LW');
        $currentSheet->setCellValue('O9', 'Domingo');
        $currentSheet->setCellValue('P9', '% LW');
        $currentSheet->setCellValue('Q9', '$ DIF LW');
        

        $row = 10;
        $startRowFormat=$row;
        if(!empty($rvc[0]))
        {

            foreach($rvc as $index => $rvcData)
            {    
                $currentSheet->setCellValue('A'.$row, "Breakfast");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->gbl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->gbl);
                $currentSheet->setCellValue('E'.$row, $rvcData->gbm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->gbm);
                $currentSheet->setCellValue('G'.$row, $rvcData->gbmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->gbmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->gbj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->gbj);
                $currentSheet->setCellValue('K'.$row, $rvcData->gbv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->gbv);
                $currentSheet->setCellValue('M'.$row, $rvcData->gbs);
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->gbs);            
                $currentSheet->setCellValue('O'.$row, $rvcData->gbd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->gbd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->gb);
                
                $row++;
                $currentSheet->setCellValue('A'.$row, "Lunch");
                $currentSheet->setCellValue('B'.$row,$rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->gll);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->gll);
                $currentSheet->setCellValue('E'.$row, $rvcData->glm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->glm);
                $currentSheet->setCellValue('G'.$row, $rvcData->glmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->glmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->glj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->glj);
                $currentSheet->setCellValue('K'.$row, $rvcData->glv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->glv);
                $currentSheet->setCellValue('M'.$row, $rvcData->gls);
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->gls);
                $currentSheet->setCellValue('O'.$row, $rvcData->gld);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->gld);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->gl);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Dinner");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->gdl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->gdl);
                $currentSheet->setCellValue('E'.$row, $rvcData->gdm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->gdm);
                $currentSheet->setCellValue('G'.$row, $rvcData->gdmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->gdmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->gdj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->gdj);
                $currentSheet->setCellValue('K'.$row, $rvcData->gdv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->gdv);
                $currentSheet->setCellValue('M'.$row, $rvcData->gds);            
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->gds);
                $currentSheet->setCellValue('O'.$row, $rvcData->gdd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->gdd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->gd);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Night");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->gnl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->gnl);
                $currentSheet->setCellValue('E'.$row, $rvcData->gnm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->gnm);
                $currentSheet->setCellValue('G'.$row, $rvcData->gnmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->gnmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->gnj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->gnj);
                $currentSheet->setCellValue('K'.$row, $rvcData->gnv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->gnv);
                $currentSheet->setCellValue('M'.$row, $rvcData->gns);
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->gns);
                $currentSheet->setCellValue('O'.$row, $rvcData->gnd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->gnd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->gn);
                
                $row++;                

            }
            
            $endRowFormat = $row-1;
                
            $spreadsheet->getActiveSheet()->getStyle('A'.$startRowFormat.':Q'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );

            $row++;            
            $currentSheet->setCellValue('A'.$row, 'NetSales');
            $row++;
            $currentSheet->setCellValue('A'.$row, 'Daypart');
            $currentSheet->setCellValue('B'.$row, 'Tienda');
            $currentSheet->setCellValue('C'.$row, 'Lunes');
            $currentSheet->setCellValue('D'.$row, '% LW');
            $currentSheet->setCellValue('E'.$row, 'Martes');
            $currentSheet->setCellValue('F'.$row, '% LW');
            $currentSheet->setCellValue('G'.$row, 'Miercoles');
            $currentSheet->setCellValue('H'.$row, '% LW');
            $currentSheet->setCellValue('I'.$row, 'Jueves');
            $currentSheet->setCellValue('J'.$row, '% LW');
            $currentSheet->setCellValue('K'.$row, 'Viernes');
            $currentSheet->setCellValue('L'.$row, '% LW');
            $currentSheet->setCellValue('M'.$row, 'Sabado');
            $currentSheet->setCellValue('N'.$row, '% LW');
            $currentSheet->setCellValue('O'.$row, 'Domingo');
            $currentSheet->setCellValue('P'.$row, '% LW');
            $currentSheet->setCellValue('Q'.$row, '$ DIF LW');

            $spreadsheet->getActiveSheet()->getStyle('A'.($row-1).':Q'.$row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');
            $row++;
            $startRowFormat=$row;

            foreach($rvc as $index => $rvcData)
            {    
                $currentSheet->setCellValue('A'.$row, "Breakfast");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->nsbl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->nsbl);
                $currentSheet->setCellValue('E'.$row, $rvcData->nsbm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->nsbm);
                $currentSheet->setCellValue('G'.$row, $rvcData->nsbmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->nsbmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->nsbj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->nsbj);
                $currentSheet->setCellValue('K'.$row, $rvcData->nsbv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->nsbv);
                $currentSheet->setCellValue('M'.$row, $rvcData->nsbs);  
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->nsbs);          
                $currentSheet->setCellValue('O'.$row, $rvcData->nsbd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->nsbd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->nsb);
                
                $row++;
                $currentSheet->setCellValue('A'.$row, "Lunch");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->nsll);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->nsll);
                $currentSheet->setCellValue('E'.$row, $rvcData->nslm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->nslm);
                $currentSheet->setCellValue('G'.$row, $rvcData->nslmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->nslmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->nslj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->nslj);
                $currentSheet->setCellValue('K'.$row, $rvcData->nslv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->nslv);
                $currentSheet->setCellValue('M'.$row, $rvcData->nsls);  
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->nsls);          
                $currentSheet->setCellValue('O'.$row, $rvcData->nsld);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->nsld);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->nsl);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Dinner");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->nsdl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->nsdl);
                $currentSheet->setCellValue('E'.$row, $rvcData->nsdm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->nsdm);
                $currentSheet->setCellValue('G'.$row, $rvcData->nsdmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->nsdmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->nsdj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->nsdj);
                $currentSheet->setCellValue('K'.$row, $rvcData->nsdv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->nsdv);
                $currentSheet->setCellValue('M'.$row, $rvcData->nsds); 
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->nsds);           
                $currentSheet->setCellValue('O'.$row, $rvcData->nsdd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->nsdd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->nsd);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Night");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->nsnl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->nsnl);
                $currentSheet->setCellValue('E'.$row, $rvcData->nsnm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->nsnm);
                $currentSheet->setCellValue('G'.$row, $rvcData->nsnmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->nsnmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->nsnj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->nsnj);
                $currentSheet->setCellValue('K'.$row, $rvcData->nsnv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->nsnv);
                $currentSheet->setCellValue('M'.$row, $rvcData->nsns);  
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->nsns);          
                $currentSheet->setCellValue('O'.$row, $rvcData->nsnd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->nsnd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->nsn);
                $row++;
            }
            $endRowFormat = $row-1;
            
            $spreadsheet->getActiveSheet()->getStyle('A'.$startRowFormat.':Q'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );

            $row++;
            $currentSheet->setCellValue('A'.$row, 'Avg Check');
            $row++;
            $currentSheet->setCellValue('A'.$row, 'Daypart');
            $currentSheet->setCellValue('B'.$row, 'Tienda');
            $currentSheet->setCellValue('C'.$row, 'Lunes');
            $currentSheet->setCellValue('D'.$row, '% LW');
            $currentSheet->setCellValue('E'.$row, 'Martes');
            $currentSheet->setCellValue('F'.$row, '% LW');
            $currentSheet->setCellValue('G'.$row, 'Miercoles');
            $currentSheet->setCellValue('H'.$row, '% LW');
            $currentSheet->setCellValue('I'.$row, 'Jueves');
            $currentSheet->setCellValue('J'.$row, '% LW');
            $currentSheet->setCellValue('K'.$row, 'Viernes');
            $currentSheet->setCellValue('L'.$row, '% LW');
            $currentSheet->setCellValue('M'.$row, 'Sabado');
            $currentSheet->setCellValue('N'.$row, '% LW');
            $currentSheet->setCellValue('O'.$row, 'Domingo');
            $currentSheet->setCellValue('P'.$row, '% LW');
            $currentSheet->setCellValue('Q'.$row, '$ DIF LW');

            $spreadsheet->getActiveSheet()->getStyle('A'.($row-1).':Q'.$row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');            
            $row++;
            $startRowFormat=$row;

            foreach($rvc as $index => $rvcData)
            { 
                $currentSheet->setCellValue('A'.$row, "Breakfast");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->avgbl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->avgbl);
                $currentSheet->setCellValue('E'.$row, $rvcData->avgbm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->avgbm);
                $currentSheet->setCellValue('G'.$row, $rvcData->avgbmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->avgbmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->avgbj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->avgbj);
                $currentSheet->setCellValue('K'.$row, $rvcData->avgbv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->avgbv);
                $currentSheet->setCellValue('M'.$row, $rvcData->avgbs);  
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->avgbs);          
                $currentSheet->setCellValue('O'.$row, $rvcData->avgbd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->avgbd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->avgb);
                
                $row++;
                $currentSheet->setCellValue('A'.$row, "Lunch");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->avgll);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->avgll);
                $currentSheet->setCellValue('E'.$row, $rvcData->avglm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->avglm);
                $currentSheet->setCellValue('G'.$row, $rvcData->avglmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->avglmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->avglj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->avglj);
                $currentSheet->setCellValue('K'.$row, $rvcData->avglv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->avglv);
                $currentSheet->setCellValue('M'.$row, $rvcData->avgls);  
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->avgls);          
                $currentSheet->setCellValue('O'.$row, $rvcData->avgld);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->avgld);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->avgl);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Dinner");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->avgdl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->avgdl);
                $currentSheet->setCellValue('E'.$row, $rvcData->avgdm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->avgdm);
                $currentSheet->setCellValue('G'.$row, $rvcData->avgdmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->avgdmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->avgdj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->avgdj);
                $currentSheet->setCellValue('K'.$row, $rvcData->avgdv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->avgdv);
                $currentSheet->setCellValue('M'.$row, $rvcData->avgds);   
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->avgds);         
                $currentSheet->setCellValue('O'.$row, $rvcData->avgdd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->avgdd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->avgd);

                $row++;
                $currentSheet->setCellValue('A'.$row, "Night");
                $currentSheet->setCellValue('B'.$row, $rvcData->sucursal);
                $currentSheet->setCellValue('C'.$row, $rvcData->avgnl);
                $currentSheet->setCellValue('D'.$row, $rvclw[$index]->avgnl);
                $currentSheet->setCellValue('E'.$row, $rvcData->avgnm);
                $currentSheet->setCellValue('F'.$row, $rvclw[$index]->avgnm);
                $currentSheet->setCellValue('G'.$row, $rvcData->avgnmr);
                $currentSheet->setCellValue('H'.$row, $rvclw[$index]->avgnmr);
                $currentSheet->setCellValue('I'.$row, $rvcData->avgnj);
                $currentSheet->setCellValue('J'.$row, $rvclw[$index]->avgnj);
                $currentSheet->setCellValue('K'.$row, $rvcData->avgnv);
                $currentSheet->setCellValue('L'.$row, $rvclw[$index]->avgnv);
                $currentSheet->setCellValue('M'.$row, $rvcData->avgns);   
                $currentSheet->setCellValue('N'.$row, $rvclw[$index]->avgns);         
                $currentSheet->setCellValue('O'.$row, $rvcData->avgnd);
                $currentSheet->setCellValue('P'.$row, $rvclw[$index]->avgnd);
                $currentSheet->setCellValue('Q'.$row, $rvclw[$index]->avgn);
                $row++;
            }
            $endRowFormat = $row-1;
            
            $spreadsheet->getActiveSheet()->getStyle('A'.$startRowFormat.':Q'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );
        }

        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $currentSheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);


        $sql = "SELECT idSucursal, rvc, SUM(A.netSales)netSales, SUM(A.guests) guests , SUM(A.netSales)/SUM(A.guests) AvgCheck, SUM(A.guestsBreakfast)guestsBreakfast, SUM(A.guestsLunch)guestsLunch, SUM(A.guestsDinner)guestsDinner, SUM(A.guestsNight)guestsNight, SUM(A.netSalesBreakfast)netSalesBreakfast, SUM(A.netSalesLunch )netSalesLunch, SUM(A.netSalesDinner)netSalesDinner, SUM(A.netSalesNight)netSalesNight, SUM(A.netSalesBreakfast)/SUM(A.guestsBreakfast) avgCheckBreakfast, SUM(A.netSalesLunch)/SUM(A.guestsLunch) avgCheckLunch, SUM(A.netSalesDinner)/SUM(A.guestsDinner) avgCheckDinner, SUM(A.netSalesNight)/SUM(A.guestsNight) avgCheckNight FROM vds_guests A WHERE A.idSucursal IN (".$this->locationID.") AND A.fecha BETWEEN ? AND ? GROUP BY idSucursal, rvc ;";
        $datos = DB::select($sql, [ $this->initDate, $this->endDate]);
        $finalData = array();
        foreach($datos AS $dato)
        {
            $finalData[$dato->idSucursal][$dato->rvc] = array("netSales"=>$dato->netSales, "guests"=>$dato->guests , "AvgCheck"=>$dato->AvgCheck, "guestsBreakfast"=>$dato->guestsBreakfast, "guestsLunch"=>$dato->guestsLunch, "guestsDinner"=>$dato->guestsDinner, "guestsNight"=>$dato->guestsNight, "netSalesBreakfast"=>$dato->netSalesBreakfast, "netSalesLunch"=>$dato->netSalesLunch, "netSalesDinner"=>$dato->netSalesDinner, "netSalesNight"=>$dato->netSalesNight , "avgCheckBreakfast"=>$dato->avgCheckBreakfast, "avgCheckLunch"=>$dato->avgCheckLunch, "avgCheckDinner"=>$dato->avgCheckDinner, "avgCheckNight"=>$dato->avgCheckNight);
        }
        
        $sql = "SELECT id, nombre, idTier FROM sucursales S WHERE estado =1 AND idTier >0 AND franquicia=0 AND id IN (".$this->locationID.") ORDER BY idTier, nombre;";
        $sucursales = DB::select($sql,[]); 

        $sql = "SELECT T.idTier, T.tier FROM sucursales S INNER JOIN sucursales_tier T ON T.idTier = S.idTier WHERE S.estado =1 AND S.idTier >0 AND franquicia=0 AND S.id IN (".$this->locationID.") GROUP BY T.idTier, T.tier ORDER BY idTier";
        $resTiers = DB::select($sql,[]);

        $tiers = array();
        foreach($resTiers as $t)
        {
            $tiers[$t->idTier]  = $t->tier;
        }

        $row = 7;

        $sql = "SELECT * FROM sucursales_rvc WHERE idEmpresa = ?;";
        $rvcs = DB::select($sql,[$this->company]);
        $rvcsNO = count($rvcs);
        $col = 2;
        $row = 5;

        $currentSheet->getCellByColumnAndRow($col,$row-3)->setValue("Semana " .date("W",strtotime($this->initDate)) );
        $letra = Coordinate::stringFromColumnIndex($col);
        $col+=$rvcsNO*4;
        $letra2 = Coordinate::stringFromColumnIndex($col-1);
        $currentSheet->mergeCells($letra.($row-3).':'.$letra2.($row-3));
        $col = 2;
        $row = 5;
        $currentSheet->getCellByColumnAndRow($col-1,$row)->setValue("Venta");
        foreach($rvcs AS $rvc)
        {
            $currentSheet->getCellByColumnAndRow($col,$row-1)->setValue("Desayuno");
            $currentSheet->getCellByColumnAndRow($col+1,$row-1)->setValue("Comida");
            $currentSheet->getCellByColumnAndRow($col+2,$row-1)->setValue("Cena");
            $currentSheet->getCellByColumnAndRow($col+3,$row-1)->setValue("Nocturno");
            $currentSheet->getCellByColumnAndRow($col,$row-2)->setValue("Fecha " . date("M",strtotime($this->initDate)). " ".date("d",strtotime($this->initDate)). "-" .date("d",strtotime($this->endDate)));
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($rvc->rvc);
            $letra = Coordinate::stringFromColumnIndex($col);
            $col+=$rvcsNO;
            $letra2 = Coordinate::stringFromColumnIndex($col-1);
            $currentSheet->mergeCells($letra.$row.':'.$letra2.$row);
            $currentSheet->mergeCells($letra.($row-2).':'.$letra2.($row-2));
            
        }
        
        $currentSheet->getCellByColumnAndRow($col,$row-1)->setValue("Gran Total");
        $currentSheet->getCellByColumnAndRow($col,$row)->setValue("Suma por Suc");
        $currentSheet->getCellByColumnAndRow($col+1,$row)->setValue("% por cluster");
        $perTierCol = $col;
        $row = 6;

        $currentTier = 0 ;
        $currentTierow = 0 ;
        $currentTierCol=0;
        $RvcTotal = array();
        $tierRvcTotal = 0;
        $tierTotalsvars = array();
        $avgCheckTier = array();
        $avgCheckSuc = array();

        foreach($sucursales AS $sucursal)
        {

            if($currentTier!=$sucursal->idTier)
            {
                
                if($currentTier != 0)
                {
                    $col = 2;
                    $currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($tierRvcTotal)? 0 :$tierRvcTotal );
                    $avgCheckTier[$currentTier] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
                    $tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
                }            
                
                $currentSheet->setCellValue('A'.$row, $tiers[$sucursal->idTier]);
                $currentTier = $sucursal->idTier;
                $currentTierow = $row;
                $tierRvcTotal = 0;
                $row++;
            }

            $currentSheet->setCellValue('A'.$row, $sucursal->nombre);
            $col = 2;
            $sucTotal= 0;
            foreach($rvcs AS $rvc)
            {
                
                if(!empty($finalData[$sucursal->id][$rvc->idRvc]))
                {                        
                    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["netSalesBreakfast"]);
                    $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesBreakfast"];
                    $sucTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesBreakfast"];
                    $RvcTotal[$rvc->idRvc][0] = (empty($RvcTotal[$rvc->idRvc][0]) ? 0: $RvcTotal[$rvc->idRvc][0])  + $finalData[$sucursal->id][$rvc->idRvc]["netSalesBreakfast"];
                    $col++;
                    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["netSalesLunch"]);
                    $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesLunch"];
                    $sucTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesLunch"];
                    $RvcTotal[$rvc->idRvc][1] = (empty($RvcTotal[$rvc->idRvc][1]) ? 0: $RvcTotal[$rvc->idRvc][1])  + $finalData[$sucursal->id][$rvc->idRvc]["netSalesLunch"];
                    $col++;
                    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["netSalesDinner"]);
                    $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesDinner"];
                    $sucTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesDinner"];
                    $RvcTotal[$rvc->idRvc][2] = (empty($RvcTotal[$rvc->idRvc][2]) ? 0: $RvcTotal[$rvc->idRvc][2])  + $finalData[$sucursal->id][$rvc->idRvc]["netSalesDinner"];
                    $col++;
                    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["netSalesNight"]);
                    $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesNight"];
                    $sucTotal += $finalData[$sucursal->id][$rvc->idRvc]["netSalesNight"];
                    $RvcTotal[$rvc->idRvc][3] = (empty($RvcTotal[$rvc->idRvc][3]) ? 0: $RvcTotal[$rvc->idRvc][3])  + $finalData[$sucursal->id][$rvc->idRvc]["netSalesNight"];
                    $col++;
                }
                else
                {
                    $col+=$rvcsNO;
                }
                $currentTierCol = $col;
            }
            $avgCheckSuc[$sucursal->id] = $sucTotal;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($sucTotal);
            $row++;

        }
        $RvcAVGTotal = $RvcTotal;

        $currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($tierRvcTotal)? 0 :$tierRvcTotal );
        $currentSheet->setCellValue('A'.$row, "TOTAL");
        $tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
        $tierBtotal= 0;
        foreach($tierTotalsvars AS $tmpTotals)
        {
            $tierBtotal += $tmpTotals;
        }

        foreach($tierTotalsvars AS $bgTotalTierRow => $tmpTotals)
        {
            $currentSheet->getCellByColumnAndRow($perTierCol+1,$bgTotalTierRow)->setValue(empty($tierBtotal)? 0 : $tmpTotals/$tierBtotal*100);
        }

        $col = 2;

        foreach($rvcs AS $rvc)
        {
            $trvc = empty($RvcTotal[$rvc->idRvc]) ? [0,0,0,0]: $RvcTotal[$rvc->idRvc];
            $temptotal = $trvc[0] +$trvc[1] +$trvc[2] +$trvc[3] ;            
            $currentSheet->getCellByColumnAndRow($col,$row+2)->setValue( empty($temptotal)? 0 :$temptotal );
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[0])? 0 :$trvc[0] );
            $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :$trvc[0]*100/$temptotal );
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[1])? 0 :$trvc[1] );
            $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :$trvc[1]*100/$temptotal );
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[2])? 0 :$trvc[2] );
            $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :$trvc[2]*100/$temptotal );
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[3])? 0 :$trvc[3] );
            $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :$trvc[3]*100/$temptotal );
            $col++;
        }
        $row++;
        $currentSheet->setCellValue('A'.$row, "Participacion");
        $row++;
        $currentSheet->setCellValue('A'.$row, "Gran total");
        $row++;
        

/********************** GUEST */
$row+=7;
$col = 2;

$currentSheet->getCellByColumnAndRow($col,$row-5)->setValue("Semana " .date("W",strtotime($this->initDate)) );
$letra = Coordinate::stringFromColumnIndex($col);
$col+=$rvcsNO*4;
$letra2 = Coordinate::stringFromColumnIndex($col-1);
$currentSheet->mergeCells($letra.($row-5).':'.$letra2.($row-5));
$col = 2;
$currentTier = 0 ;
$currentTierow = 0 ;
$currentTierCol=0;
$RvcTotal = array();
$tierRvcTotal = 0;
$tierTotalsvars = array();
$currentSheet->getCellByColumnAndRow($col-1,$row-2)->setValue("Visitantes");
foreach($rvcs AS $rvc)
{
    $currentSheet->getCellByColumnAndRow($col,$row-3)->setValue("Desayuno");
    $currentSheet->getCellByColumnAndRow($col+1,$row-3)->setValue("Comida");
    $currentSheet->getCellByColumnAndRow($col+2,$row-3)->setValue("Cena");
    $currentSheet->getCellByColumnAndRow($col+3,$row-3)->setValue("Nocturno");
    $currentSheet->getCellByColumnAndRow($col,$row-4)->setValue("Fecha " . date("M",strtotime($this->initDate)). " ".date("d",strtotime($this->initDate)). "-" .date("d",strtotime($this->endDate)));
    $currentSheet->getCellByColumnAndRow($col,$row-2)->setValue($rvc->rvc);
    $letra = Coordinate::stringFromColumnIndex($col);
    $col+=$rvcsNO;
    $letra2 = Coordinate::stringFromColumnIndex($col-1);
    $currentSheet->mergeCells($letra.($row-2).':'.$letra2.($row-2));
    $currentSheet->mergeCells($letra.($row-2).':'.$letra2.($row-2));
    
}

$currentSheet->getCellByColumnAndRow($col,$row-3)->setValue("Gran Total");
$currentSheet->getCellByColumnAndRow($col,$row-2)->setValue("Suma por Suc");
$currentSheet->getCellByColumnAndRow($col+1,$row-2)->setValue("% por cluster");
$perTierCol = $col;
$row--;

foreach($sucursales AS $sucursal)
{

    if($currentTier!=$sucursal->idTier)
    {
        
        if($currentTier != 0)
        {
            $col = 2;
            $currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($tierRvcTotal)? 0 :$tierRvcTotal );
            $avgCheckTier[$currentTier] = empty($tierRvcTotal)? 0 :$avgCheckTier[$currentTier]/$tierRvcTotal;
            $tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
        }            
        
        $currentSheet->setCellValue('A'.$row, $tiers[$sucursal->idTier]);
        $currentTier = $sucursal->idTier;
        $currentTierow = $row;
        $tierRvcTotal = 0;
        $row++;
    }

    $currentSheet->setCellValue('A'.$row, $sucursal->nombre);
    $col = 2;
    $sucTotal = 0;
    foreach($rvcs AS $rvc)
    {
        
        if(!empty($finalData[$sucursal->id][$rvc->idRvc]))
        {    
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["guestsBreakfast"]);
            $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["guestsBreakfast"];
            $sucTotal+= $finalData[$sucursal->id][$rvc->idRvc]["guestsBreakfast"];
            $RvcTotal[$rvc->idRvc][0] = (empty($RvcTotal[$rvc->idRvc][0]) ? 0: $RvcTotal[$rvc->idRvc][0])  + $finalData[$sucursal->id][$rvc->idRvc]["guestsBreakfast"];
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["guestsLunch"]);
            $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["guestsLunch"];
            $sucTotal+= $finalData[$sucursal->id][$rvc->idRvc]["guestsLunch"];
            $RvcTotal[$rvc->idRvc][1] = (empty($RvcTotal[$rvc->idRvc][1]) ? 0: $RvcTotal[$rvc->idRvc][1])  + $finalData[$sucursal->id][$rvc->idRvc]["guestsLunch"];
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["guestsDinner"]);
            $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["guestsDinner"];
            $sucTotal+= $finalData[$sucursal->id][$rvc->idRvc]["guestsDinner"];
            $RvcTotal[$rvc->idRvc][2] = (empty($RvcTotal[$rvc->idRvc][2]) ? 0: $RvcTotal[$rvc->idRvc][2])  + $finalData[$sucursal->id][$rvc->idRvc]["guestsDinner"];
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["guestsNight"]);
            $tierRvcTotal += $finalData[$sucursal->id][$rvc->idRvc]["guestsNight"];
            $sucTotal+= $finalData[$sucursal->id][$rvc->idRvc]["guestsNight"];
            $RvcTotal[$rvc->idRvc][3] = (empty($RvcTotal[$rvc->idRvc][3]) ? 0: $RvcTotal[$rvc->idRvc][3])  + $finalData[$sucursal->id][$rvc->idRvc]["guestsNight"];
            $col++;
        }
        else
        {
            $col+=$rvcsNO;
        }
        
        $currentTierCol = $col;

    }
    $avgCheckSuc[$sucursal->id] = empty($sucTotal) ? 0 : $avgCheckSuc[$sucursal->id]/$sucTotal;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($sucTotal);
    $row++;
}
$currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($tierRvcTotal)? 0 :$tierRvcTotal );
$currentSheet->setCellValue('A'.$row, "TOTAL");
$tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
$tierBtotal= 0;
foreach($tierTotalsvars AS $tmpTotals)
{
    $tierBtotal += $tmpTotals;
}

foreach($tierTotalsvars AS $bgTotalTierRow => $tmpTotals)
{
    $currentSheet->getCellByColumnAndRow($perTierCol+1,$bgTotalTierRow)->setValue(empty($tierBtotal)? 0 : $tmpTotals/$tierBtotal*100);
}
$col = 2;

foreach($rvcs AS $rvc)
{
    $trvc = empty($RvcTotal[$rvc->idRvc]) ? [0,0,0,0]: $RvcTotal[$rvc->idRvc];
    
    $RvcAVGTotal[$rvc->idRvc][0] = empty($RvcTotal[$rvc->idRvc][0]) ? 0: $RvcAVGTotal[$rvc->idRvc][0]/$RvcTotal[$rvc->idRvc][0];
    $RvcAVGTotal[$rvc->idRvc][1] = empty($RvcTotal[$rvc->idRvc][1]) ? 0: $RvcAVGTotal[$rvc->idRvc][1]/$RvcTotal[$rvc->idRvc][1];
    $RvcAVGTotal[$rvc->idRvc][2] = empty($RvcTotal[$rvc->idRvc][2]) ? 0: $RvcAVGTotal[$rvc->idRvc][2]/$RvcTotal[$rvc->idRvc][2];
    $RvcAVGTotal[$rvc->idRvc][3] = empty($RvcTotal[$rvc->idRvc][3]) ? 0: $RvcAVGTotal[$rvc->idRvc][3]/$RvcTotal[$rvc->idRvc][3];

    $temptotal = (empty($trvc[0])?0:$trvc[0]) + (empty($trvc[1])?0:$trvc[1]) + (empty($trvc[2])?0:$trvc[2]) + (empty($trvc[3])?0:$trvc[3]) ;
    $currentSheet->getCellByColumnAndRow($col,$row+2)->setValue( empty($temptotal)? 0 :$temptotal );
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[0])? 0 :$trvc[0] );
    $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :(empty($trvc[0])? 0 :$trvc[0] )*100/$temptotal );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[1])? 0 :$trvc[1] );
    $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 : (empty($trvc[1])? 0 :$trvc[1] )*100/$temptotal );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[2])? 0 :$trvc[2] );
    $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :(empty($trvc[2])? 0 :$trvc[2] )*100/$temptotal );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[3])? 0 :$trvc[3] );
    $currentSheet->getCellByColumnAndRow($col,$row+1)->setValue( empty($temptotal)? 0 :(empty($trvc[3])? 0 :$trvc[3] )*100/$temptotal );
    $col++;
}

$row++;
$currentSheet->setCellValue('A'.$row, "Participacion");
$row++;
$currentSheet->setCellValue('A'.$row, "Gran total");
$row++;

/********************** AVGCHECK */
$row+=7;
$col = 2;
$currentTier = 0 ;
$currentTierow = 0 ;
$currentTierCol=0;
$RvcTotal = array();
$tierRvcTotal = 0;
$tierTotalsvars = array();

$currentSheet->getCellByColumnAndRow($col,$row-5)->setValue("Semana " .date("W",strtotime($this->initDate)) );
$letra = Coordinate::stringFromColumnIndex($col);
$col+=$rvcsNO*4;
$letra2 = Coordinate::stringFromColumnIndex($col-1);
$currentSheet->mergeCells($letra.($row-5).':'.$letra2.($row-5));
$col = 2;
$currentSheet->getCellByColumnAndRow($col-1,$row-2)->setValue("Cheque Promedio");
foreach($rvcs AS $rvc)
{
    $currentSheet->getCellByColumnAndRow($col,$row-3)->setValue("Desayuno");
    $currentSheet->getCellByColumnAndRow($col+1,$row-3)->setValue("Comida");
    $currentSheet->getCellByColumnAndRow($col+2,$row-3)->setValue("Cena");
    $currentSheet->getCellByColumnAndRow($col+3,$row-3)->setValue("Nocturno");
    $currentSheet->getCellByColumnAndRow($col,$row-4)->setValue("Fecha " . date("M",strtotime($this->initDate)). " ".date("d",strtotime($this->initDate)). "-" .date("d",strtotime($this->endDate)));
    $currentSheet->getCellByColumnAndRow($col,$row-2)->setValue($rvc->rvc);
    $letra = Coordinate::stringFromColumnIndex($col);
    $col+=$rvcsNO;
    $letra2 = Coordinate::stringFromColumnIndex($col-1);
    $currentSheet->mergeCells($letra.($row-2).':'.$letra2.($row-2));
    $currentSheet->mergeCells($letra.($row-2).':'.$letra2.($row-2));
    
}

$currentSheet->getCellByColumnAndRow($col,$row-3)->setValue("Gran Total");
$currentSheet->getCellByColumnAndRow($col,$row-2)->setValue("Suma por Suc");
$currentSheet->getCellByColumnAndRow($col+1,$row-2)->setValue("% por cluster");
$perTierCol = $col;
$row--;

foreach($sucursales AS $sucursal)
{

    if($currentTier!=$sucursal->idTier)
    {
        
        if($currentTier != 0)
        {
            $col = 2;
            $currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($avgCheckTier[$sucursal->id])? 0 :$avgCheckTier[$sucursal->id] );
            $tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
        }            
        
        $currentSheet->setCellValue('A'.$row, $tiers[$sucursal->idTier]);
        $currentTier = $sucursal->idTier;
        $currentTierow = $row;
        $tierRvcTotal = 0;
        $row++;
    }

    $currentSheet->setCellValue('A'.$row, $sucursal->nombre);
    $col = 2;
    foreach($rvcs AS $rvc)
    {
        
        if(!empty($finalData[$sucursal->id][$rvc->idRvc]))
        {    
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["avgCheckBreakfast"]);
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["avgCheckLunch"]);
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["avgCheckDinner"]);
            $col++;
            $currentSheet->getCellByColumnAndRow($col,$row)->setValue($finalData[$sucursal->id][$rvc->idRvc]["avgCheckNight"]);
            $col++;
        }
        else
        {
            $col+=$rvcsNO;
        }
        $currentTierCol = $col;
    }
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue($avgCheckSuc[$sucursal->id]);
    $row++;
}
$currentSheet->setCellValue('A'.$row, "TOTAL");
$col = 2;

foreach($rvcs AS $rvc)
{
    $trvc = empty($RvcAVGTotal[$rvc->idRvc]) ? [0,0,0,0]: $RvcAVGTotal[$rvc->idRvc];
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[0])? 0 :$trvc[0] );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[1])? 0 :$trvc[1] );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[2])? 0 :$trvc[2] );
    $col++;
    $currentSheet->getCellByColumnAndRow($col,$row)->setValue( empty($trvc[3])? 0 :$trvc[3] );
    $col++;
}
/*
$currentSheet->getCellByColumnAndRow($currentTierCol,$currentTierow)->setValue( empty($tierRvcTotal)? 0 :$tierRvcTotal );

$tierTotalsvars[$currentTierow] = empty($tierRvcTotal)? 0 :$tierRvcTotal;
$tierBtotal= 0;
foreach($tierTotalsvars AS $tmpTotals)
{
    $tierBtotal += $tmpTotals;
}

foreach($tierTotalsvars AS $bgTotalTierRow => $tmpTotals)
{
    $currentSheet->getCellByColumnAndRow($perTierCol+1,$bgTotalTierRow)->setValue(empty($tierBtotal)? 0 : $tmpTotals/$tierBtotal*100);
}

*/

        /*if(Auth::id()==1)
            dd($rvc); */


        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="DaypartVIT'.date("Ymd").'.xlsx"');
        $writer->save("php://output");

    }

}