<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Classes\Reports\utils\UserLocation;
use stdClass;

class DeliveryReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $location;
    private $result;
    private $locationID;
    private $lastWeek;

    public function setParams($params)
    {
        if ($params["daterange"] == "All") {
            $this->initDate =  date("Y-m-01");
            $this->endDate =  date("Y-m-d");
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        $location = new UserLocation();
        $location->get($params["location"], $params["typeLoc"] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
    }

    public function getUserLocations($filter = "")
    {
        if (empty($filter)) {
        } else {
        }
    }

    public function runReport()
    {
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

        if (!empty($this->locationID)) {
            $location = "IN ($this->locationID)";
        } else {
            $location = "";
        }


        $dataObj = new stdClass();

        $sql = "SELECT * FROM (
                SELECT SUM(A.total_order) AS total, 'Rappi' as type FROM rappi_order AS A INNER JOIN menu_sucursal_plataforma 
                AS B ON A.store_external_id = B.codigoSucursal AND B.idPlataforma = 4 
                INNER JOIN sucursales AS C ON B.idSucursal = C.id WHERE date(A.created_at) BETWEEN ? AND ? AND C.id $location AND pos_notified != 4 
                UNION ALL
                SELECT SUM(A.total_order) AS total, 'EKM' AS type FROM menu_order AS A INNER JOIN menu_sucursal_plataforma 
                AS B ON A.store_external_id = B.codigoSucursal AND B.idPlataforma = 1 
                INNER JOIN sucursales AS C ON B.idSucursal = C.id WHERE date(A.created_at) BETWEEN ? AND ? AND C.id $location AND pos_notified != 4 
                UNION ALL
                SELECT SUM(A.total_order) AS total, 'Uber' AS type FROM uber_order AS A INNER JOIN menu_sucursal_plataforma 
                AS B ON A.store_id = B.codigoSucursal AND B.idPlataforma = 6
                INNER JOIN sucursales AS C ON B.idSucursal = C.id WHERE date(A.created_at) BETWEEN ? AND ? AND C.id $location AND pos_notified != 4 
                ) AS OD GROUP BY OD.type, OD.total";

        $data = DB::select($sql, [$this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate]);

        $dataObj->titulo = 'Venta Delivery';
        $dataObj->subtitulo = "$this->initDate - $this->endDate";
        $dataset = new stdClass();
        $total = 0;
        foreach ($data as $key => $value) {
            $total += $value->total;
            $dataset->data[] = $value->total;
            $dataObj->data->labels[] = "$value->type";
        }
        $dataObj->data->datasets[] = $dataset;

        foreach ($dataset->data as $key => $value) {
            $porcentaje = $value * 100 / $total;
            $dataObj->data->por[] = round($porcentaje) . "%";
        }

        $this->result = $dataObj;
    }
}
