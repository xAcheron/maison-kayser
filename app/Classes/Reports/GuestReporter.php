<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;
use Exception;

class GuestReporter implements iReporter
{
    private $prevWeekDate;
    private $initDate;
    private $endDate;
    private $LastMonthinitDate;
    private $LastMonthendDate;
    private $LastYearinitDate;
    private $LastYearendDate;
    private $TwoYearinitDate;
    private $TwoYearendDate;
    private $location;
    private $locationID;
    private $companyID;
    private $result;
    private $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");
    private $tier;
    private $widgetType;
    private $excluidas;

    public function setParams($params)
    {

        if (empty($params["daterange"]) || $params["daterange"] == "All") {
            $tmpDate = date("Y-m-d", strtotime(date("Y-m-d") . "-1 DAYS"));
            $daynum = date("N", strtotime($tmpDate));
            $this->initDate = date("Y-m-d", strtotime($tmpDate . "-" . ($daynum - 1) . " DAYS"));
            $this->endDate = date("Y-m-d", strtotime($tmpDate . "+" . (7 - $daynum) . " DAYS"));
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        $this->widgetType = empty($params["refs"]) ? 0 : $params["refs"];
        $this->prevWeekDate = date("Y-m-d", strtotime($this->initDate .  " -1 WEEK"));

        $this->LastMonthinitDate = date("Y-m-", strtotime($this->initDate .  " -1 MONTH")) . "01";
        $this->LastMonthendDate = date("Y-m-t", strtotime($this->initDate .  " -1 MONTH"));
        $this->LastYearinitDate = date("Y-m-", strtotime($this->initDate .  " -1 YEAR")) . "01";
        $this->LastYearendDate = date("Y-m-t", strtotime($this->initDate .  " -1 YEAR"));
        $this->TwoYearinitDate = date("Y-m-", strtotime($this->initDate .  " -2 YEAR")) . "01";
        $this->TwoYearendDate = date("Y-m-t", strtotime($this->initDate .  " -2 YEAR"));



        /*
        $this->location = $params["location"];
        $this->tier = empty($params["tier"])?0:$params["tier"];
        
        if(is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);
        
        $this->location = $locations[0];
        $this->locationID = $locations[1];
        $this->companyID = $locations[2];
        */

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc'] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->companyID = $location->company;

        $this->perSales = empty($params["perSales"]) ? 100 : $params["perSales"];


        $excluidas = null;
        if (!empty($params['mismastiendas']) && $params["mismastiendas"] == "true") {
            $sql = "SELECT GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'OT' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) <= ? AND MAX(fecha) < ? 
            UNION ALL 
            SELECT vds_rvc.idSucursal, 'OT' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal HAVING SUM(netSales)=0 
            UNION ALL 
            SELECT vds_rvc.idSucursal, 'OT' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc  INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) > ? AND MAX(fecha) >= ?
            ) AS venta GROUP BY tipo";

            $excluidas = DB::select($sql, [$this->companyID, $this->LastYearendDate, $this->initDate, $this->companyID, $this->initDate, $this->endDate, $this->companyID, $this->LastYearendDate, $this->initDate]);
        }

        $this->excluidas =  empty($excluidas[0]) ? "0" : $excluidas[0]->sucursales;
    }

    public function runReport()
    {

        $sql = "SELECT * FROM (SELECT G.rvc, fecha, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND fecha BETWEEN ? AND ? GROUP BY G.rvc, fecha) VT INNER JOIN sucursales_rvc RVC ON (RVC.idRvc = VT.rvc AND RVC.idEmpresa = ? ) ORDER BY VT.fecha, VT.rvc;";

        $rvcs = DB::select($sql, [$this->prevWeekDate, $this->endDate, $this->companyID]);

        $sql = "SELECT * FROM (SELECT G.rvc, fecha, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND fecha BETWEEN ? AND ? GROUP BY G.rvc, fecha) VT INNER JOIN sucursales_rvc RVC ON (RVC.idRvc = VT.rvc AND RVC.idEmpresa = ? ) ORDER BY VT.fecha, VT.rvc;";
        $rvcsdly = DB::select($sql, [$this->LastYearinitDate, $this->LastYearendDate, $this->companyID]);

        $sql = "SELECT * FROM (SELECT G.rvc, MONTH(fecha) fecha, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND fecha BETWEEN ? AND ? GROUP BY G.rvc, MONTH(fecha)) VT INNER JOIN sucursales_rvc RVC ON (RVC.idRvc = VT.rvc AND RVC.idEmpresa = ? ) ORDER BY VT.fecha, VT.rvc;";

        $rvcslm = DB::select($sql, [$this->LastMonthinitDate, $this->LastMonthendDate, $this->companyID]);

        $sql = "SELECT * FROM (SELECT G.rvc, YEAR(fecha) fecha, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND fecha BETWEEN ? AND ? GROUP BY G.rvc, YEAR(fecha)) VT INNER JOIN sucursales_rvc RVC ON (RVC.idRvc = VT.rvc AND RVC.idEmpresa = ? ) ORDER BY VT.fecha, VT.rvc;";

        $rvcsly = DB::select($sql, [$this->LastYearinitDate, $this->LastYearendDate, $this->companyID]);

        $sql =  "SELECT G.rvc, WEEK(fecha,3) fecha, AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND YEAR(fecha) = YEAR(?) GROUP BY rvc, WEEK(fecha,3) ORDER BY rvc, WEEK(fecha,3);";
        $weekSales = DB::select($sql, [$this->initDate]);

        $sql =  "SELECT G.rvc, WEEK(fecha,3) fecha, AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND YEAR(fecha) = YEAR(?)-1 GROUP BY rvc, WEEK(fecha,3) ORDER BY rvc, WEEK(fecha,3);";
        $weekSalesLY = DB::select($sql, [$this->initDate]);

        $sql =  "SELECT G.rvc, WEEK(fecha,3) fecha, AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND NOT(idSucursal IN (" . $this->excluidas . ")) AND YEAR(fecha) = YEAR(?)-1 GROUP BY rvc, WEEK(fecha,3) ORDER BY rvc, WEEK(fecha,3);";
        $weekSalesTY = DB::select($sql, [$this->TwoYearinitDate]);


        $sql = "SELECT * FROM sucursales_rvc RVC WHERE idEmpresa = ? ORDER BY idRvc;";
        $rvcNames = DB::select($sql, [$this->companyID]);

        $dataRVC = array();
        $datadRVC = array();

        $tempFecha = "";
        $totNetSales = 0;
        $totGuests = 0;

        $currentTotalRvc = array();
        foreach ($rvcNames as $rvclm) {
            $currentTotalRvc[$rvclm->idRvc][0] = 0;
            $currentTotalRvc[$rvclm->idRvc][1] = 0;
            $currentTotalRvc[$rvclm->idRvc][2] = 0;
            $currentTotalRvc[$rvclm->idRvc][3] = 0;
        }


        foreach ($rvcsdly as $rvc) {
            if (empty($tempFecha) || $tempFecha != $rvc->fecha) {
                $tempFecha = $rvc->fecha;
                foreach ($rvcNames as $rvcname) {
                    $datadRVC[$tempFecha][$rvcname->idRvc][0] = 0;
                    $datadRVC[$tempFecha][$rvcname->idRvc][1] = 0;
                    $datadRVC[$tempFecha][$rvcname->idRvc][2] = 0;
                }
            }

            $datadRVC[$rvc->fecha][$rvc->idRvc][0] = $rvc->netSales;
            $datadRVC[$rvc->fecha][$rvc->idRvc][1] = $rvc->guests;
            $datadRVC[$rvc->fecha][$rvc->idRvc][2] = $rvc->AvgCheck;

            $datadRVC[$rvc->fecha][0] = (empty($datadRVC[$rvc->fecha][0]) ? 0 : $datadRVC[$rvc->fecha][0]) + $rvc->netSales;
        }


        $lastNetSale = 0;

        foreach ($rvcs as $rvc) {

            if ($tempFecha >= $this->initDate) {
                $currentTotalRvc[$rvc->idRvc][0] = (empty($currentTotalRvc[$rvc->idRvc][0]) ? 0 : $currentTotalRvc[$rvc->idRvc][0]) + $rvc->netSales;
                $currentTotalRvc[$rvc->idRvc][1] = (empty($currentTotalRvc[$rvc->idRvc][1]) ? 0 : $currentTotalRvc[$rvc->idRvc][1]) + $rvc->guests;
                $currentTotalRvc[$rvc->idRvc][2] = empty($currentTotalRvc[$rvc->idRvc][1]) ? 0 : $currentTotalRvc[$rvc->idRvc][0] / $currentTotalRvc[$rvc->idRvc][1];
                $currentTotalRvc[$rvc->idRvc][3] = 0;
                $currentTotalRvc[$rvc->idRvc][4] = 0;
            }

            if (empty($tempFecha) || $tempFecha != $rvc->fecha) {

                if (!empty($tempFecha)) {

                    $dataRVC[$tempFecha][0][0] = $totNetSales;
                    $dataRVC[$tempFecha][0][1] = $totGuests;
                    $dataRVC[$tempFecha][0][2] = $totNetSales / (empty($totGuests) ? 1 : $totGuests);

                    if ($tempFecha >= $this->initDate) {
                        $dataRVC[$tempFecha][0][4] = empty($lastNetSale) ? 1 : ($totNetSales / $lastNetSale);
                        $lastWeek = date("Y-m-d", strtotime($tempFecha . " -1 WEEK"));
                        $dataRVC[$tempFecha][0][3] =  !empty($dataRVC[$lastWeek][0][0]) ? $totNetSales / $dataRVC[$lastWeek][0][0] : 1;
                    } else {
                        $dataRVC[$tempFecha][0][3] = 0;
                        $dataRVC[$tempFecha][0][4] = 0;
                    }
                }

                $tempFecha = $rvc->fecha;
                $totNetSales = 0;
                $totGuests = 0;
                $lastNetSale = 0;

                foreach ($rvcNames as $rvcname) {
                    $dataRVC[$tempFecha][$rvcname->idRvc][0] = 0;
                    $dataRVC[$tempFecha][$rvcname->idRvc][1] = 0;
                    $dataRVC[$tempFecha][$rvcname->idRvc][2] = 0;
                    $dataRVC[$tempFecha][$rvcname->idRvc][3] = 0;
                    $dataRVC[$tempFecha][$rvcname->idRvc][4] = 0;
                }
            }

            $dataRVC[$rvc->fecha][$rvc->idRvc][0] = $rvc->netSales;
            $dataRVC[$rvc->fecha][$rvc->idRvc][1] = $rvc->guests;
            $dataRVC[$rvc->fecha][$rvc->idRvc][2] = $rvc->AvgCheck;


            if ($rvc->fecha >= $this->initDate) {

                $lastWeek = date("Y-m-d", strtotime($rvc->fecha . " -1 WEEK"));
                $lastYear = date("Y-m-d", strtotime($rvc->fecha . " -1 YEAR"));

                $dataRVC[$rvc->fecha][$rvc->idRvc][4] = (empty($datadRVC[$lastYear][$rvc->idRvc][0]) ? 1 : $rvc->netSales / $datadRVC[$lastYear][$rvc->idRvc][0]);
                $dataRVC[$rvc->fecha][$rvc->idRvc][3] = (empty($dataRVC[$lastWeek][$rvc->idRvc][0]) ? 1 : $rvc->netSales / $dataRVC[$lastWeek][$rvc->idRvc][0]);
                $lastNetSale += empty($datadRVC[$lastYear][$rvc->idRvc][0]) ? 0 : $datadRVC[$lastYear][$rvc->idRvc][0];
            } else {
                $dataRVC[$rvc->fecha][$rvc->idRvc][4] = 0;
                $dataRVC[$rvc->fecha][$rvc->idRvc][3] = 0;
            }
            $totNetSales += $rvc->netSales;
            $totGuests += $rvc->guests;
        }

        $dataRVC[$tempFecha][0][0] = $totNetSales;
        $dataRVC[$tempFecha][0][1] = $totGuests;
        $dataRVC[$tempFecha][0][2] = empty($totGuests) ? 0 : $totNetSales / $totGuests;

        if ($tempFecha >= $this->initDate) {
            $lastWeek = date("Y-m-d", strtotime($tempFecha . " -1 WEEK"));
            $lastYear = date("Y-m-d", strtotime($tempFecha . " -1 YEAR"));
            $dataRVC[$rvc->fecha][0][4] = (empty($datadRVC[$lastYear][0]) ? 1 : $totNetSales / $datadRVC[$lastYear][0]);
            $dataRVC[$tempFecha][0][3] = empty($dataRVC[$lastWeek][0][0]) ? 1 : $totNetSales / $dataRVC[$lastWeek][0][0];
        } else {
            $dataRVC[$tempFecha][0][3] = 0;
        }

        foreach ($dataRVC as $fecha => $rvcData) {
            if ($fecha < $this->initDate)
                unset($dataRVC[$fecha]);
        }

        foreach ($rvcslm as $rvclm) {
            $currentTotalRvc[$rvclm->idRvc][3] = empty($rvclm->AvgCheck) ? 0 : $currentTotalRvc[$rvclm->idRvc][2] / $rvclm->AvgCheck;
        }

        foreach ($rvcsly as $rvclm) {
            $currentTotalRvc[$rvclm->idRvc][4] = empty($rvclm->AvgCheck) ? 0 : $currentTotalRvc[$rvclm->idRvc][2] / $rvclm->AvgCheck;
        }

        $weekLYRVC = array();

        foreach ($weekSalesLY as $weekone) {
            foreach ($rvcNames as $rvcname) {
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][0] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][1] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][2] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][3] = 0;
            }
            $weekLYRVC[$weekone->fecha][0] = array(0, 0, 0);
        }

        foreach ($weekSalesLY as $weekone) {

            $weekLYRVC[$weekone->fecha][$weekone->rvc][0] = $weekone->netSales;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][1] = $weekone->guests;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][2] = $weekone->AvgCheck;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][3] = $weekone->netSales;
            $weekLYRVC[$weekone->fecha][0][0] = (empty($weekLYRVC[$weekone->fecha][0][0]) ? 0 : $weekLYRVC[$weekone->fecha][0][0]) + $weekone->netSales;
            $weekLYRVC[$weekone->fecha][0][1] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][1]) + $weekone->guests;
            $weekLYRVC[$weekone->fecha][0][2] = (empty($weekLYRVC[$weekone->fecha][0][2]) ? $weekone->AvgCheck : ($weekLYRVC[$weekone->fecha][0][2] + $weekone->AvgCheck) / (empty($weekone->AvgCheck) ? 1 : 2));
        }

        $weekTYRVC = array();

        foreach ($weekSalesTY as $weekone) {
            $weekTYRVC[$weekone->fecha][0] = array(0, 0, 0);
        }

        foreach ($weekSalesTY as $weekone) {
            $weekTYRVC[$weekone->fecha][0][0] = (empty($weekTYRVC[$weekone->fecha][0][0]) ? 0 : $weekTYRVC[$weekone->fecha][0][0]) + $weekone->netSales;
            $weekTYRVC[$weekone->fecha][0][1] = (empty($weekTYRVC[$weekone->fecha][0][1]) ? 0 : $weekTYRVC[$weekone->fecha][0][1]) + $weekone->guests;
            $weekTYRVC[$weekone->fecha][0][2] = (empty($weekTYRVC[$weekone->fecha][0][2]) ? $weekone->AvgCheck : ($weekTYRVC[$weekone->fecha][0][2] + $weekone->AvgCheck) / (empty($weekone->AvgCheck) ? 1 : 2));
        }

        $weekRVC = array();

        foreach ($weekSales as $weekone) {
            foreach ($rvcNames as $rvcname) {
                $weekRVC[$weekone->fecha][$rvcname->idRvc][0] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][1] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][2] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][3] = 0;
            }
            if ($weekone->fecha < 52 || date("W", strtotime($this->endDate)) >= 52)
                $weekRVC[$weekone->fecha][0] = array("actual" => array(0, 0, 0), "anterior" => array(0, 0, 0), "2anios" => array(0, 0, 0));
        }
        foreach ($weekSales as $weekone) {
            /*$weekRVC[$weekone->fecha][$weekone->rvc][0] = $weekone->netSales;
            $weekRVC[$weekone->fecha][$weekone->rvc][1] = $weekone->guests;
            $weekRVC[$weekone->fecha][$weekone->rvc][2] = $weekone->AvgCheck;*/
            $weekRVC[$weekone->fecha][$weekone->rvc][3] = empty($weekLYRVC[$weekone->fecha][$weekone->rvc][0]) ? 0 : $weekone->netSales / $weekLYRVC[$weekone->fecha][$weekone->rvc][0];
            if ($weekone->fecha < 52 || date("W", strtotime($this->endDate)) >= 52) {
                $weekRVC[$weekone->fecha][0]["actual"][0] = (empty($weekRVC[$weekone->fecha][0]["actual"][0]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][0]) + $weekone->netSales;
                $weekRVC[$weekone->fecha][0]["anterior"][0] = (empty($weekLYRVC[$weekone->fecha][0][0]) ? 0 : $weekLYRVC[$weekone->fecha][0][0]);
                $weekRVC[$weekone->fecha][0]["tanios"][0] = (empty($weekTYRVC[$weekone->fecha][0][0]) ? 0 : $weekTYRVC[$weekone->fecha][0][0]);

                $weekRVC[$weekone->fecha][0]["actual"][1] = (empty($weekRVC[$weekone->fecha][0]["actual"][1]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][1]) + $weekone->guests;
                $weekRVC[$weekone->fecha][0]["anterior"][1] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][1]);
                $weekRVC[$weekone->fecha][0]["tanios"][1] = (empty($weekTYRVC[$weekone->fecha][0][1]) ? 0 : $weekTYRVC[$weekone->fecha][0][1]);

                $weekRVC[$weekone->fecha][0]["actual"][2] = empty($weekRVC[$weekone->fecha][0]["actual"][1]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][0] / $weekRVC[$weekone->fecha][0]["actual"][1];
                $weekRVC[$weekone->fecha][0]["anterior"][2] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][0] / $weekLYRVC[$weekone->fecha][0][1]);
                $weekRVC[$weekone->fecha][0]["tanios"][2] = (empty($weekTYRVC[$weekone->fecha][0][1]) ? 0 : $weekTYRVC[$weekone->fecha][0][0] / $weekTYRVC[$weekone->fecha][0][1]);
            }
            //(empty($weekRVC[$weekone->fecha][0]["actual"][2])? $weekone->AvgCheck : ($weekRVC[$weekone->fecha][0]["actual"][2] + $weekone->AvgCheck) / (empty($weekone->AvgCheck) ? 1: 2) );
            //(empty($weekLYRVC[$weekone->fecha][0][2])?0:$weekLYRVC[$weekone->fecha][0][2]);

            $weekRVC[$weekone->fecha][-1] = 0;
            $weekRVC[$weekone->fecha][-2] = empty($weekLYRVC[$weekone->fecha][0]) ? 0 : $weekLYRVC[$weekone->fecha][0];
            $weekRVC[$weekone->fecha][$weekone->rvc][0] = array("actual" => $weekone->netSales, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][0]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][0]);
            $weekRVC[$weekone->fecha][$weekone->rvc][1] = array("actual" => $weekone->guests, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][1]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][1]);
            $weekRVC[$weekone->fecha][$weekone->rvc][2] = array("actual" => $weekone->AvgCheck, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][2]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][2]);
        }

        foreach ($weekRVC as $fecha => $wdata) {
            if ($fecha < 52 || date("W", strtotime($this->endDate)) >= 52)
                $weekRVC[$fecha][-1] = empty($weekLYRVC[$fecha][0]) ? 0 : $weekRVC[$fecha][0]["actual"][0] / $weekLYRVC[$fecha][0][0];
        }

        $this->result = json_decode(json_encode(array("rvcs" => $dataRVC, "rvcnames" => $rvcNames, "currentTotal" => $currentTotalRvc, "weekSales" => $weekRVC)));
    }

    public function getResult($type)
    {
        if ($type == "xlsx") {
            $this->exportReport();
        } else {
            $parser = new ReportParser($type);
            return $parser->parse($this->result);
        }
    }

    public function exportReport()
    {



        $rvcs = $this->result->rvcs;

        $spreadsheet = new Spreadsheet();
        $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $conditional1->addCondition('1');
        $conditional1->getStyle()->getFont()->getColor()->setARGB('FFFF2929');

        $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $conditional2->addCondition('1');
        $conditional2->getStyle()->getFont()->getColor()->setARGB('FF02C016');

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

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Day Part');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');

        $currentSheet->setCellValue('B2', 'Visitas');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $col = 1;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue('Dia');
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue('Fecha');
        $col++;

        foreach ($this->result->rvcnames as $nrvc) {
            $currentSheet->mergeCells(Coordinate::stringFromColumnIndex($col) . "7:" . Coordinate::stringFromColumnIndex($col + 3) . "7");
            $currentSheet->getCellByColumnAndRow($col, 7)->setValue($nrvc->rvc);
            $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Venta");
            $col++;
            $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Visitantes");
            $col++;
            $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Cheque Prom.");
            $col++;
            $currentSheet->getCellByColumnAndRow($col, 8)->setValue("%");
            $col++;
        }
        $currentSheet->mergeCells(Coordinate::stringFromColumnIndex($col) . "7:" . Coordinate::stringFromColumnIndex($col + 3) . "7");
        $currentSheet->getCellByColumnAndRow($col, 7)->setValue("Total General");
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Venta");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Visitantes");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Cheque Prom.");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("%");

        $letra = Coordinate::stringFromColumnIndex($col);

        $spreadsheet->getActiveSheet()->getStyle('A7:' . $letra . '8')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');


        $row = 9;
        $initRowFormat = $row;
        $col = 1;
        $jsonstr = json_encode($rvcs);
        $rvcsArr = json_decode($jsonstr, true);
        $jsonstr = json_encode($this->result->currentTotal);
        $totrvcsArr = json_decode($jsonstr, true);
        $jsonstr = json_encode($this->result->weekSales);
        $weekSales = json_decode($jsonstr, true);

        $percentages = array();
        $format_numbers = array();

        foreach ($rvcsArr as $fecha => $rvc) {

            if ($fecha >= $this->initDate) {
                $currentSheet->getCellByColumnAndRow(1, $row)->setValue($this->dias[date("w", strtotime($fecha))]);
                $currentSheet->getCellByColumnAndRow(2, $row)->setValue($fecha);
                $col = 3;
                foreach ($this->result->rvcnames as $nrvc) {
                    if (!empty($rvc["" . $nrvc->idRvc . ""][0])) {
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[$nrvc->idRvc][0]);
                        if (!in_array($col, $format_numbers))
                            $format_numbers[] = $col;
                        $col++;
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[$nrvc->idRvc][1]);
                        $col++;
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[$nrvc->idRvc][2]);
                        if (!in_array($col, $format_numbers))
                            $format_numbers[] = $col;
                        $col++;
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[$nrvc->idRvc][3]);
                        if (!in_array($col, $percentages))
                            $percentages[] = $col;
                        $col++;
                    } else {
                        $col += 4;
                    }
                }
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[0][0]);
                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[0][1]);
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[0][2]);
                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($rvc[0][3]);
                if (!in_array($col, $percentages))
                    $percentages[] = $col;
                $col++;
                $row++;
            }
        }

        $row++;
        $col = 1;
        $currentSheet->getCellByColumnAndRow($col, $row)->setValue("% Ult. Mes");
        $currentSheet->getCellByColumnAndRow($col, $row + 2)->setValue("% Ult. Año");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, $row)->setValue("Total");
        $col++;
        foreach ($this->result->rvcnames as $nrvc) {
            if (!empty($totrvcsArr[$nrvc->idRvc][0])) {
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($totrvcsArr[$nrvc->idRvc][0]);
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($totrvcsArr[$nrvc->idRvc][1]);
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($totrvcsArr[$nrvc->idRvc][2]);
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($totrvcsArr[$nrvc->idRvc][3]);
                $currentSheet->getCellByColumnAndRow($col, $row + 2)->setValue($totrvcsArr[$nrvc->idRvc][4]);
                $col++;
            } else {
                $col += 4;
            }
        }

        $letra = Coordinate::stringFromColumnIndex($col);
        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':' . $letra . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $letra = Coordinate::stringFromColumnIndex($col);
        $spreadsheet->getActiveSheet()->getStyle('A' . ($row + 2) . ':' . $letra . ($row + 2))->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');
        $row++;
        $row++;
        $row++;
        $endRowFormat = $row - 1;
        //$spreadsheet->getActiveSheet()->getStyle('A' . $initRowFormat . ':K' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);     

        foreach ($percentages as $per) {
            $letra = Coordinate::stringFromColumnIndex($per);
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->setConditionalStyles($conditionalStyles);
        }

        foreach ($format_numbers as $per) {
            $letra = Coordinate::stringFromColumnIndex($per);
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getNumberFormat()->setFormatCode('#,##0');
            //->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }
        $row++;
        $col = 1;
        $currentSheet->getCellByColumnAndRow($col, $row)->setValue('Promedio semanal');
        $col++;
        $currentSheet->getCellByColumnAndRow($col, $row)->setValue('Fecha');
        $col++;

        foreach ($this->result->rvcnames as $nrvc) {
            $currentSheet->getCellByColumnAndRow($col, $row)->setValue($nrvc->rvc);
            $col++;
            $currentSheet->getCellByColumnAndRow($col, $row)->setValue("% Ult. Año");
            $col++;
        }

        $currentSheet->getCellByColumnAndRow($col, $row)->setValue("Total");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, $row)->setValue("% Ult. Año");

        $letra = Coordinate::stringFromColumnIndex($col);
        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':' . $letra . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');
        $col++;
        $row++;

        $format_numbers = array();
        $percentages = array();
        $initRowFormat = $row;

        foreach ($weekSales as $week => $wdata) {
            if (!empty($wdata[0])) {
                $col = 1;

                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($wdata[0]["actual"][0] / 7);

                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue('Semana ' . $week);
                $col++;
                foreach ($this->result->rvcnames as $nrvc) {
                    if (!empty($wdata[$nrvc->idRvc][0]["actual"])) {
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($wdata[$nrvc->idRvc][0]["actual"]);
                        if (!in_array($col, $format_numbers))
                            $format_numbers[] = $col;
                        $col++;
                        $currentSheet->getCellByColumnAndRow($col, $row)->setValue($wdata[$nrvc->idRvc][3]);
                        if (!in_array($col, $percentages))
                            $percentages[] = $col;
                        $col++;
                    } else {
                        $col += 2;
                    }
                }

                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($wdata[0]["actual"][0]);
                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $col++;/*
                $currentSheet->getCellByColumnAndRow($col,$row)->setValue($wdata[-1]["actual"][0]);
                if(!in_array($col,$percentages))
                    $percentages[] = $col;
                $col++;*/
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue(empty($wdata[0]["anterior"][0]) ? 1 : $wdata[0]["actual"][0] / $wdata[0]["anterior"][0]);
                if (!in_array($col, $percentages))
                    $percentages[] = $col;

                $col++;
                $row++;
            }
        }

        $endRowFormat = $row - 1;
        foreach ($percentages as $per) {
            $letra = Coordinate::stringFromColumnIndex($per);
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->setConditionalStyles($conditionalStyles);
        }

        foreach ($format_numbers as $per) {
            $letra = Coordinate::stringFromColumnIndex($per);
            //$spreadsheet->getActiveSheet()->getStyle($letra.$initRowFormat.':'.$letra.$endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $spreadsheet->getActiveSheet()->getStyle($letra . $initRowFormat . ':' . $letra . $endRowFormat)->getNumberFormat()->setFormatCode('#,##0');
        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Visitas' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }

    public function widget($tipo = 0)
    {
        if ($this->widgetType == 1 || empty($this->widgetType)) {
            $sql =  "SELECT AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY YEAR(fecha);";
            $weekSales = DB::select($sql, [$this->initDate, $this->endDate]);

            $sql =  "SELECT AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY YEAR(fecha);";
            $weekSalesLW = DB::select($sql, [date("Y-m-d", strtotime($this->initDate . " -1 WEEK")), date("Y-m-d", strtotime($this->endDate . " -1 WEEK"))]);

            $sql =  "SELECT AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY YEAR(fecha);";
            $weekSalesLY = DB::select($sql, [$this->LastYearinitDate, $this->LastYearendDate]);

            $finalData = array();

            $perSem = $weekSales[0]->netSales / $weekSales[0]->netSales * 100;
            $object = new \stdClass();
            $object->titulo = "Venta Semanal";
            $object->subtitulo = $this->initDate . " - " . $this->endDate;
            $object->footer = "";
            $object->value = $weekSales[0]->netSales;
            $object->type = "N";
            $object->company = 1;
            $object->indicator = ($perSem >= 100 ? "over" : "under");
            $finalData[] = $object;

            $perSem = $weekSales[0]->netSales / $weekSalesLW[0]->netSales * 100;
            $object = new \stdClass();
            $object->titulo = "VS LW";
            $object->subtitulo = "";
            $object->footer = "";
            $object->value = $perSem;
            $object->type = "P";
            $object->company = 1;
            $object->indicator = ($perSem >= 100 ? "over" : "under");
            $finalData[] = $object;

            $perSem = $weekSales[0]->netSales / $weekSalesLY[0]->netSales * 100;
            $object = new \stdClass();
            $object->titulo = "VS LY";
            $object->subtitulo = "";
            $object->footer = "";
            $object->value = $perSem;
            $object->type = "P";
            $object->company = 1;
            $object->indicator = ($perSem >= 100 ? "over" : "under");
            $finalData[] = $object;

            $this->result = json_decode(json_encode(array("titulo" => "Venta Semanal", "subtitulo" => $this->initDate . " - " . $this->endDate, "data" => $finalData)));
        } else {
            $sql =  "SELECT WEEK(fecha,3) fecha, MIN(MONTH(fecha)) minMonth ,AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND YEAR(fecha) = YEAR(?) GROUP BY WEEK(fecha,3) ORDER BY WEEK(fecha,3);";
            $weekSales = DB::select($sql, [$this->initDate]);

            $sql =  "SELECT WEEK(fecha,3) fecha, MIN(MONTH(fecha)) minMonth ,AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND YEAR(fecha) = YEAR(?)-1 GROUP BY WEEK(fecha,3) ORDER BY WEEK(fecha,3);";
            $weekSalesLY = DB::select($sql, [$this->initDate]);

            $sql =  "SELECT WEEK(fecha,3) fecha, MIN(MONTH(fecha)) minMonth ,AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND YEAR(fecha) = YEAR(?)-1 GROUP BY WEEK(fecha,3) ORDER BY WEEK(fecha,3);";
            $weekSalesTY = DB::select($sql, [$this->TwoYearinitDate]);

            $data = array();
            $semanas = array();

            if ($this->widgetType == 2) {


                foreach ($weekSales as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $semanas[] = $week->fecha;
                        $data[0][] = $week->guests;
                    }
                }

                foreach ($weekSalesLY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[1][] = $week->guests;
                    }
                }


                foreach ($weekSalesTY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[2][] = $week->guests;
                    }
                }


                $titulo = "Visitas Semanal";
            } else if ($this->widgetType == 3) {

                foreach ($weekSales as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $semanas[] = $week->fecha;
                        $data[0][] = $week->netSales;
                    }
                }

                foreach ($weekSalesLY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[1][] = $week->netSales;
                    }
                }


                foreach ($weekSalesTY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[2][] = $week->netSales;
                    }
                }

                $titulo = "Venta Semanal";
            } else if ($this->widgetType == 4) {
                foreach ($weekSales as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $semanas[] = $week->fecha;
                        $data[0][] = $week->AvgCheck;
                    }
                }

                foreach ($weekSalesLY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[1][] = $week->AvgCheck;
                    }
                }


                foreach ($weekSalesTY as $week) {
                    if (!($week->minMonth == 1 and $week->fecha == 52)) {
                        $data[2][] = $week->AvgCheck;
                    }
                }

                $titulo = "Cheque Semanal";
            }


            $this->result = json_decode(json_encode(array(
                "titulo" => $titulo, "subtitulo" => $this->initDate . " - " . $this->endDate,
                "data" => array(
                    "labels" => $semanas,
                    "datasets" => array(
                        array("label" => "Act",  "data" =>  $data[0]),
                        array("label" => "Ant",  "data" => $data[1]),
                        array("label" => "2A",  "data" => $data[2])
                    )
                )
            )));
        }
    }
}
