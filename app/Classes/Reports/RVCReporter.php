<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;

class RVCReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $companyID;
    private $result;
    private $tier;
    private $perSales;
    private $months = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Sep", "Oct", "Nov", "Dic"];

    public function setParams($params)
    {
        if (empty($params['daterange']) || $params['daterange'] == "All") {
            $this->initDate = date("Y-m-01", strtotime(date("Y-m-d")));
            $this->endDate = date("Y-m-t", strtotime(date("Y-m-d")));
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        if (empty($params['location']) || $params['location'] == "All") {
            $location = new UserLocation();
            $empresasUsuario = $location->getHierachy(1, $params['idUsuario']);
            if ($empresasUsuario[0]["id"])
                $this->location = $empresasUsuario[0]["id"];
            else
                $this->location = 0;

            $params["location"] = $this->location;
        } else {
            $this->location = $params["location"];
        }

        $this->tier = empty($params["tier"]) ? 0 : $params["tier"];

        // if(is_numeric($params["location"]))
        //     $locations = $this->getLocations($params["location"]);
        // else
        //     $locations = $this->getLocation($params["location"]);

        // $this->location = $locations[0];
        // $this->locationID = $locations[1];
        // $this->companyID = $locations[2];

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc'] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->companyID = $location->company;
        $this->perSales = empty($params["perSales"]) ? 100 : $params["perSales"];
    }

    public function getLocation($idLocation)
    {
        $sql = "SELECT id, idEmpresa, idMicros FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql, [$idLocation]);
        return array("'" . $locations[0]->idMicros . "'", $locations[0]->id, $locations[0]->idEmpresa);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT idEmpresa, GROUP_CONCAT(idMicros) AS idMicros, GROUP_CONCAT(id) id FROM sucursales WHERE " . (empty($this->tier) || $this->tier == "null" ? "" : " idTier = " . $this->tier . " AND ") . " idEmpresa = ? GROUP BY idEmpresa;";
        $locations = DB::select($sql, [$idEmpresa]);
        return array($locations[0]->idMicros, $locations[0]->id, $locations[0]->idEmpresa);
    }

    public function runReport()
    {
    }

    public function exportReport()
    {
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

    public function widget()
    {
        $sql = "SELECT RVC.rvc RvcName, VT.* FROM (SELECT G.rvc, SUM(G.guests) guests, SUM(G.netSales) netSales, SUM(G.netSales)/COALESCE(SUM(G.guests),1) avgCheck, SUM(G.guestsBreakfast) gb, SUM(G.guestsLunch) gl, SUM(G.guestsDinner) gd, SUM(G.guestsNight) gn,  SUM(G.netSalesBreakfast) nsb, SUM(G.netSalesLunch) nsl, SUM(G.netSalesDinner) nsd, SUM(G.netSalesNight) nsn,SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1) avgb, SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1) avgl, SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1) avgd, SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) avgn FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY G.rvc) VT INNER JOIN sucursales_rvc RVC ON VT.rvc = RVC.idRvc WHERE RVC.idEmpresa = ? ORDER BY RVC.idRvc;";
        $rvcs = DB::select($sql, [$this->initDate, $this->endDate, $this->companyID]);
        $month = date("m", strtotime($this->initDate));
        $finalData = array();

        $headers = array("", $this->months[intval($month)], "Visitas", "Cheque");
        $formats = array("T", "N", "N", "N");

        $finalData = array("titulo" => "Centro de Ingresos", "headers" => $headers, "data" => array(), "formats" => $formats);

        foreach ($rvcs as $rvc) {
            $finalData["data"][] = array($rvc->RvcName, $rvc->netSales, $rvc->guests, number_format($rvc->avgCheck, 2, ".", ""));
        }

        $this->result = json_decode(json_encode($finalData));
    }
}
