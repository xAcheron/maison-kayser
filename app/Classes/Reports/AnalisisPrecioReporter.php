<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Classes\Reports\utils\UserLocation;

class AnalisisPrecioReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $location;
    private $result;
    private $locationID;
    private $lastWeek;
    private $target;
    private $type;

    public function setParams($params)
    {
        if ($params["daterange"] == "All") {
            $this->initDate = date("Y-m-") . "01";
            if (date("d") == date("d", strtotime($this->initDate)) && date("d") == "01") {
                $this->initDate =  date("Y-m-", strtotime($this->initDate . " -1 DAY")) . "01";
            }
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        $location = new UserLocation();
        $location->get($params["location"], $params["typeLoc"] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->target = $params['target'];
        $this->type = $params['analisis'];
    }

    public function runReport()
    {
        if ($this->type == 1) {

            $sql = "SELECT ZZ.idArticulo ,ZZ.articulo, ZZ.proveedor, ZZ.PrecioMensual,ZZ.PrecioAnual, ZZ.PrecioMaximo, ZZ.PrecioMinimo ,(ZZ.PrecioMensual/ZZ.PrecioAnual) * 100 AS porcentaje, ZZ.mes, ZZ.anio FROM pedidos_proveedor_historico ZZ INNER JOIN pedidos_proveedor PP ON ZZ.idProveedor = PP.codigoSAP WHERE anio >= ? AND anio <= ? AND mes >= ? AND mes <= ? AND PP.idProveedor = ? ORDER BY porcentaje DESC;";

            $articulos = DB::select($sql, [date('Y', strtotime($this->initDate)), date('Y', strtotime($this->endDate)), date('m', strtotime($this->initDate)), date('m', strtotime($this->endDate)), $this->target]);
        } else if ($this->type == 2) {
            $sql = "SELECT ZZ.idArticulo ,ZZ.articulo, ZZ.proveedor, ZZ.PrecioMensual,ZZ.PrecioAnual, ZZ.PrecioMaximo, ZZ.PrecioMinimo ,(ZZ.PrecioMensual/ZZ.PrecioAnual) * 100 AS porcentaje, ZZ.mes, ZZ.anio FROM pedidos_proveedor_historico ZZ WHERE anio >= ? AND anio <= ? AND mes >= ? AND mes <= ? AND ZZ.idArticulo = ? ORDER BY porcentaje DESC;";

            $articulos = DB::select($sql, [date('Y', strtotime($this->initDate)), date('Y', strtotime($this->endDate)), date('m', strtotime($this->initDate)), date('m', strtotime($this->endDate)), $this->target]);
        }

        $this->result = $articulos;
    }

    private function exportReport()
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
    }
}
