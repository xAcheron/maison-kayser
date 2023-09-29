<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;

class PaLReporter implements iReporter
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
    private $subtitulo;

    public function setParams($params)
    {

        if (empty($params['daterange']) || $params['daterange'] == "All") {

            $day = date("d");

            if ($day < 10) {
                $this->initDate = date("Y-01-01", strtotime(date("Y-m-d") . "-2 MONTH"));
                $this->endDate = date("Y-m-01", strtotime(date("Y-m-d") . "-2 MONTH"));
            } else {
                $this->initDate = date("Y-01-01", strtotime(date("Y-m-d") . "-1 MONTH"));
                $this->endDate = date("Y-m-01", strtotime(date("Y-m-28") . "-1 MONTH"));
            }
        } else {
            if (strlen($params['daterange']) > 10) {
                $array = explode('-', $params['daterange']);
                $array = array_slice($array, 0, 3);

                $params['daterange'] = implode('-', $array);

                $sql = "SELECT MAX(W.fecha) as fecha FROM finanzas_pl_mes W INNER JOIN sucursales S ON S.id = W.idSucursal GROUP BY S.idEmpresa";
                $select = DB::select($sql, []);

                if (date("m", strtotime($params['daterange'])) > date("m", strtotime($select[0]->fecha))) {
                    $params['daterange'] = $select[0]->fecha;
                    $this->subtitulo = 'Datos mas recientes';
                }
            }


            $this->initDate = date("Y-01-01", strtotime($params['daterange']));
            $this->endDate = date("Y-m-01", strtotime($params['daterange']));
        }

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc'] ?? '', $params['idUsuario'] ?? '');
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

        /*********************************TODO TERMINAR ESTE QUERY */
        $sql = "SELECT * FROM (
            SELECT idEmpresa,
            SUM(W.cogs)/ SUM(W.netSales) pcogs, SUM(W.bcogs)/ SUM(W.netSales) pbcogs, SUM(W.cogs)/ SUM(W.bcogs) pmcogs,
            SUM(W.labor)/ SUM(W.netSales) plabor, SUM(W.blabor)/ SUM(W.netSales) pblabor, SUM(W.labor)/ SUM(W.blabor) pmlabor, 
            SUM(W.ebitda)/ SUM(W.netSales) pebitda, SUM(W.bebitda)/ SUM(W.netSales) pbebitda, SUM(W.ebitda)/ SUM(W.bebitda) pmebitda
            FROM finanzas_pl_mes W
            INNER JOIN sucursales S ON S.id = W.idSucursal
            WHERE W.fecha BETWEEN ? AND ? AND S.id IN ({$this->locationID})
            GROUP BY S.idEmpresa ) mes
            INNER JOIN 
            (SELECT idEmpresa,
            SUM(W.cogs)/ SUM(W.netSales) apcogs, SUM(W.bcogs)/ SUM(W.netSales) apbcogs, SUM(W.cogs)/ SUM(W.bcogs) apmcogs,
            SUM(W.labor)/ SUM(W.netSales) aplabor, SUM(W.blabor)/ SUM(W.netSales) apblabor, SUM(W.labor)/ SUM(W.blabor) apmlabor, 
            SUM(W.ebitda)/ SUM(W.netSales) apebitda, SUM(W.bebitda)/ SUM(W.netSales) apbebitda, SUM(W.ebitda)/ SUM(W.bebitda) apmebitda
            FROM finanzas_pl_mes W
            INNER JOIN sucursales S ON S.id = W.idSucursal
            WHERE W.fecha BETWEEN ? AND ? AND S.id IN ({$this->locationID})
            GROUP BY S.idEmpresa) AS acumulado ON mes.idEmpresa = acumulado.idEmpresa;";

        $report = DB::select($sql, [$this->endDate, $this->endDate, $this->initDate, $this->endDate]);

        $finalData = array();
        $month = date("m", strtotime($this->endDate));
        $headers = array("", $this->months[intval($month)] . "%", "BM%", "Acum%", "BA%");
        $formats = array("T", "T", "T", "T", "T");


        $finalData = array("titulo" => "Otros P&L", "subtitulo" => $this->subtitulo, "headers" => $headers, "data" => array(), "formats" => $formats);
        $finalData["data"][] = array("COGS", number_format($report[0]->pcogs * 100, 1, ".", ""), number_format($report[0]->pbcogs * 100, 1, ".", ""), number_format($report[0]->apcogs * 100, 1, ".", ""), number_format($report[0]->apbcogs * 100, 1, ".", ""));
        $finalData["data"][] = array("LABOR", number_format($report[0]->plabor * 100, 1, ".", ""), number_format($report[0]->pblabor * 100, 1, ".", ""), number_format($report[0]->aplabor * 100, 1, ".", ""), number_format($report[0]->apblabor * 100, 1, ".", ""));
        $finalData["data"][] = array("EBITDA", number_format($report[0]->pebitda * 100, 1, ".", ""), number_format($report[0]->pbebitda * 100, 1, ".", ""), number_format($report[0]->apebitda * 100, 1, ".", ""), number_format($report[0]->apbebitda * 100, 1, ".", ""));

        $this->result = json_decode(json_encode($finalData));
    }
}
