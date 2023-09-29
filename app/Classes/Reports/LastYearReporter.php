<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;
use Illuminate\Support\Facades\Auth;


class LastYearReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $initLYDate;
    private $endLYDate;
    private $location;
    private $locationID;
    private $result;
    private $company;
    private $months = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Sep", "Oct", "Nov", "Dic"];

    public function setParams($params)
    {

        if (empty($params['daterange']) || $params['daterange'] == "All") {
            $day = date("d");
            if ($day == 1) {
                $this->initDate = date("Y-m-01", strtotime(date("Y-m-d") . "-1 MONTH"));
                $this->endDate = date("Y-m-t", strtotime(date("Y-m-d") . "-1 MONTH"));
            } else {
                $this->initDate = date("Y-m-01");
                $this->endDate = date("Y-m-d");
            }
        } else {

            if (strlen($params['daterange']) > 10) {
                $array = explode('-', $params['daterange']);
                $array = array_slice($array, 0, 3);

                $params['daterange'] = implode('-', $array);
            }

            $this->initDate = date("Y-m-01", strtotime($params['daterange']));
            $this->endDate = date("Y-m-t", strtotime($params['daterange']));
        }

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc'], $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->company = $location->company;

        $this->initLYDate = date("Y-m-01", strtotime($this->initDate . " -1 YEAR"));
        $this->endLYDate = date("Y-m-t", strtotime($this->initDate . " -1 YEAR"));
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
        if (empty($this->initDate)) {

            $Year = date("Y");
            $month = date("m");
            $day = date("d");
            $day = ($day < 10 ? "0" : "") . ($day - 1);
        } else {
            $fecha = explode("-", $this->initDate);
            $Year = $fecha[0];
            $month = $fecha[1];
            $day = $fecha[2];
            $fecha  = explode("-", $this->endDate);
            $endday = $fecha[2];
        }

        $ventas = array();

        $idEmpresa = empty($this->company) ? 1 : $this->company;

        $lastYear = 2022;

        $dini = $Year . "-01-" . "01";
        $dfin =  $Year . "-" . $month . "-" . $endday;
        $dlyini = $lastYear . "-01-" . "01";
        $dlyfin = $lastYear . "-" . $month . "-" . $endday;

        $repDate1 = $dini . " to " . $dfin;
        $repDate2 = $dlyini . " to " . $dlyfin;

        /*
            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) <= ? AND MAX(fecha) < ? UNION ALL SELECT vds_rvc.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal HAVING SUM(netSales)=0 ) AS venta GROUP BY tipo;";
            $cerradas = DB::select($sql, [$idEmpresa, $dlyfin, $dini, $idEmpresa, $dini, $dfin]);

            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'Abiertas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc  INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) > ? AND MAX(fecha) >= ?) AS venta GROUP BY tipo;";
            $abiertas = DB::select($sql, [$idEmpresa, $dlyfin, $dini]);
            
            var_dump($cerradas);
            echo "<br>";
            var_dump($abiertas);
*/
        $sucursalesExcluidas = empty($abiertas[0]) ? null : $abiertas[0]->sucursales;
        $sucursalesExcluidas = empty($cerradas[0]) ? $sucursalesExcluidas : ((empty($sucursalesExcluidas) ? "" : $sucursalesExcluidas . (empty($cerradas) ? "" : ",")) . $cerradas[0]->sucursales);

        $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.mes,actual.netSales AS CurrentNetSales, COALESCE(anterior.netSales,0) AS LYNetSales, actual.netSales*100/anterior.netSales AS LY, 
            actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
            anterior.Institucional AS InstitucionalLY, actual.Vitrina*100/anterior.Vitrina AS VLY, actual.Salon*100/anterior.Salon AS SLY, actual.Delivery*100/anterior.Delivery AS DLY, 
            actual.Institucional*100/anterior.Institucional AS ILY, 0 AS Budget FROM 
            (SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha) mes, SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE " . (!empty($sucursalesExcluidas) ? " NOT (s.id IN (" . $sucursalesExcluidas . ")) AND " : "") . " s.id IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
            LEFT JOIN 
            (SELECT vds_rvc.idSucursal,MONTH(fecha) mes, SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery FROM vds_rvc WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
            ON (actual.idSucursal = anterior.idSucursal AND  actual.mes = anterior.mes) ORDER BY actual.netSales desc;";
// s.idEmpresa IN ($idEmpresa)
        $ventas = DB::select($sql, [$dini, $dfin, $dlyini, $dlyfin]);
        //echo "$idEmpresa, $dini, $dfin, $dlyini, $dlyfin <br>";
        $sql = "SELECT idSucursal,MONTH(fecha) mes, SUM(budget) monto FROM budget_dia_sucursal_o A INNER JOIN sucursales S ON A.idSucursal = S.id WHERE S.idEmpresa IN ($idEmpresa) AND A.fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha);";
        $budget = DB::select($sql, [$dini, $dfin]);

        $finalData = array();

        $headers = array("", $this->months[intval($month)], "BM%", "LY%", "Acum", "BA%", "LY%");
        $formats = array("T", "N", "P", "P", "N", "P", "P");


        $finalData = array("titulo" => "Venta Total", "headers" => $headers, "data" => array(), "formats" => $formats);

        if (!empty($budget)) {

            $sucBudget = array();
            $dataReport = array();

            foreach ($budget as $b) {
                $sucBudget[$b->idSucursal][$b->mes] = $b->monto;
            }

            $dataReport[0][0][0] = 0;
            $dataReport[0][0][1] = 0;
            $dataReport[0][0][2] = 0;
            $dataReport[0][1][0] = 0;
            $dataReport[0][1][1] = 0;
            $dataReport[0][1][2] = 0;
            $dataReport[1][0][0] = 0;
            $dataReport[1][0][1] = 0;
            $dataReport[1][0][2] = 0;
            $dataReport[1][1][0] = 0;
            $dataReport[1][1][1] = 0;
            $dataReport[1][1][2] = 0;
            $dataReport[2][0][0] = 0;
            $dataReport[2][0][1] = 0;
            $dataReport[2][0][2] = 0;
            $dataReport[2][1][0] = 0;
            $dataReport[2][1][1] = 0;
            $dataReport[2][1][2] = 0;

            foreach ($ventas as $venta) {

                $dataReport[0][0][0] += $venta->CurrentNetSales;
                //echo "<br>".$dataReport[0][0][0];
                $dataReport[0][0][1] +=  $venta->LYNetSales;

                $dataReport[0][0][2] +=  (empty($sucBudget[$venta->idSucursal][$venta->mes]) ? 0 : $sucBudget[$venta->idSucursal][$venta->mes]);

                if ($venta->mes == intval($month)) {
                    $dataReport[0][1][0] += $venta->CurrentNetSales;
                    $dataReport[0][1][1] += $venta->LYNetSales;
                    $dataReport[0][1][2] += (empty($sucBudget[$venta->idSucursal][$venta->mes]) ? 0 : $sucBudget[$venta->idSucursal][$venta->mes]);
                }

                //if (!empty($venta->CurrentNetSales) || !empty($venta->LYNetSales)) {
                    if (!empty($venta->CurrentNetSales) && !empty($venta->LYNetSales)) {
                        $dataReport[1][0][0] += $venta->CurrentNetSales;
                        $dataReport[1][0][1] += $venta->LYNetSales;
                        $dataReport[1][0][2] += (empty($sucBudget[$venta->idSucursal]) ? 0 : (empty($sucBudget[$venta->idSucursal][$venta->mes])?0:$sucBudget[$venta->idSucursal][$venta->mes]));

                        if ($venta->mes == intval($month)) {
                            $dataReport[1][1][0] += $venta->CurrentNetSales;
                            $dataReport[1][1][1] += $venta->LYNetSales;
                            $dataReport[1][1][2] += (empty($sucBudget[$venta->idSucursal]) ? 0 : (empty($sucBudget[$venta->idSucursal][$venta->mes])?0:$sucBudget[$venta->idSucursal][$venta->mes]));
                        }
                    }
                    if (!empty($venta->CurrentNetSales) && empty($venta->LYNetSales)) {
                        $dataReport[2][0][0] = (empty($dataReport[2][0][0]) ? 0 : $dataReport[2][0][0]) + $venta->CurrentNetSales;
                        $dataReport[2][0][1] = (empty($dataReport[2][0][1]) ? 0 : $dataReport[2][0][1]) + $venta->LYNetSales;
                        $dataReport[2][0][2] = (empty($dataReport[2][0][2]) ? 0 : $dataReport[2][0][2]) + (empty($sucBudget[$venta->idSucursal][$venta->mes]) ? 0 : $sucBudget[$venta->idSucursal][$venta->mes]);

                        if ($venta->mes == intval($month)) {
                            $dataReport[2][1][0] = (empty($dataReport[2][1][0]) ? 0 : $dataReport[2][1][0]) + $venta->CurrentNetSales;
                            $dataReport[2][1][1] = (empty($dataReport[2][1][1]) ? 0 : $dataReport[2][1][1]) + $venta->LYNetSales;
                            $dataReport[2][1][2] = (empty($dataReport[2][1][2]) ? 0 : $dataReport[2][1][2]) + (empty($sucBudget[$venta->idSucursal][$venta->mes]) ? 0 : $sucBudget[$venta->idSucursal][$venta->mes]);
                        }
                    }
                //}
            }


            $finalData["data"][] = array("TT", $dataReport[0][1][0], ($dataReport[0][1][0] / $dataReport[0][1][2] * 100), ($dataReport[0][1][0] / $dataReport[0][1][1] * 100), $dataReport[0][0][0], ($dataReport[0][0][0] / $dataReport[0][0][2] * 100), ($dataReport[0][0][0] / $dataReport[0][0][1] * 100));
            $finalData["data"][] = array("MT", $dataReport[1][1][0], ($dataReport[1][1][0] / $dataReport[1][1][2] * 100), ($dataReport[1][1][0] / $dataReport[1][1][1] * 100), $dataReport[1][0][0], ($dataReport[1][0][0] / $dataReport[1][0][2] * 100), ($dataReport[1][0][0] / $dataReport[1][0][1] * 100));
            if(empty($dataReport[2][0][1]))
                $finalData["data"][] = array("OT", $dataReport[2][1][0], empty($dataReport[2][1][2])?0:($dataReport[2][1][0] / $dataReport[2][1][2] * 100), empty($dataReport[2][1][1]) ? 0 : (($dataReport[2][1][0] / $dataReport[2][1][1] * 100) > 300 ? 0 : ( empty($dataReport[2][1][1])?0:$dataReport[2][1][0] / $dataReport[2][1][1] * 100)), $dataReport[2][0][0], empty($dataReport[2][0][2])?0:($dataReport[2][0][0] / $dataReport[2][0][2] * 100), 0);
            else
                $finalData["data"][] = array("OT", $dataReport[2][1][0], ($dataReport[2][1][0] / $dataReport[2][1][2] * 100), ($dataReport[2][1][0] / $dataReport[2][1][1] * 100) > 300 ? 0 : ($dataReport[2][1][0] / $dataReport[2][1][1] * 100), $dataReport[2][0][0], ($dataReport[2][0][0] / $dataReport[2][0][2] * 100), ($dataReport[2][0][0] / $dataReport[2][0][1] * 100) > 300 ? 0 : ($dataReport[2][0][0] / $dataReport[2][0][1] * 100));
        }

        $this->result = json_decode(json_encode($finalData));
        /*if(Auth::id() ==1)
            dd($ventas);*/
    }
}
