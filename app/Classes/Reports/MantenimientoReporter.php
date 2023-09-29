<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Classes\Reports\utils\UserLocation;

class MantenimientoReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $location;
    private $result;
    private $locationID;
    private $lastWeek;
    private $months = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    private $tecnico;

    public function setParams($params)
    {
        if ($params["daterange"] == "All") {
            $this->initDate = date("Y-m-") . "01";
            if (date("d") == date("d", strtotime($this->initDate)) && date("d") == "01") {
                $this->initDate =  date("Y-m-", strtotime($this->initDate . " -1 DAY")) . "01";
            }
        } else {
            $tempDate = explode(' - ', $params['daterange']);
            $this->initDate = $tempDate[0];
            $this->endDate = $tempDate[1];
        }


        $location = new UserLocation();
        $location->get($params["location"], $params["typeLoc"] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->tecnico = $params['tecnico'];
    }

    public function runReport()
    {

        $idUsuario = Auth::id();

        $MantoRole = DB::table('config_app_access')
            ->where('idUsuario', $idUsuario)
            ->get()
            ->toArray();

        if (!empty($MantoRole)) {

            $strTecnico = "";

            if ($this->tecnico != 'All') {
                $strTecnico = "AND A.idTecnico = $this->tecnico";
            }

            $sql = "SELECT COUNT(A.idSolicitud) AS total, A.tiempo, SUM(IF(A.estado=3,1,0)) AS resueltos FROM manto_solicitud AS A WHERE A.estado != 4 AND A.fechaCrea BETWEEN ? AND ? AND A.idSucursal IN ($this->locationID) AND A.idTecnico $strTecnico GROUP BY A.tiempo";

            $porRes = DB::select($sql, [$this->initDate, $this->endDate]);

            $porObj = [
                '24' => [
                    'resueltos' => 0,
                    'total' => 0,
                ],
                '48' => [
                    'resueltos' => 0,
                    'total' => 0,
                ],
                '72' => [
                    'resueltos' => 0,
                    'total' => 0,
                ],
                '72Mas' => [
                    'resueltos' => 0,
                    'total' => 0,
                ],
            ];

            foreach ($porRes as $key => $value) {
                if ($value->tiempo == 24 || $value->tiempo == 25) {
                    $porObj['24']['resueltos'] += $value->resueltos;
                    $porObj['24']['total'] += $value->total;
                } else if ($value->tiempo == 48) {
                    $porObj['48']['resueltos'] += $value->resueltos;
                    $porObj['48']['total'] += $value->total;
                } else if ($value->tiempo == 72) {
                    $porObj['72']['resueltos'] += $value->resueltos;
                    $porObj['72']['total'] += $value->total;
                } else if ($value->tiempo > 72) {
                    $porObj['72Mas']['resueltos'] += $value->resueltos;
                    $porObj['72Mas']['total'] += $value->total;
                }
            }

            foreach ($porObj as $key => $value) {
                $porObj[$key] = $value['total'] != 0 ? round(($value['resueltos'] * 100) / $value['total']) . "%" : 'No hay tickets';
            }

            $sql = "SELECT NAME as nombre,idTecnico, res24, res48, res72, res72M, resTotal, atr24, atr48,atr72, atr72M, atrTotal, abrTotal,total FROM (SELECT COUNT(A.idSolicitud) AS total, SUM(IF(A.estado = 3 AND A.tiempo IN (24, 25), 1, 0)) AS res24, SUM(IF(A.estado = 3 AND A.tiempo = 48, 1, 0)) AS res48, SUM(IF(A.estado = 3 AND A.tiempo = 72, 1, 0)) AS res72, SUM(IF(A.estado = 3 AND A.tiempo > 72, 1, 0)) AS res72M, SUM(IF(A.estado = 3, 1, 0)) AS resTotal, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo IN (24,25), 1, 0)) AS atr24, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 48, 1, 0)) AS atr48, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 72, 1, 0)) AS atr72, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo > 72, 1, 0)) AS atr72M, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3, 1, 0)) AS atrTotal, SUM(IF(NOT(A.estado IN (3,4)),1,0)) AS abrTotal, A.idTecnico, B.name FROM manto_solicitud AS A LEFT JOIN users AS B ON A.idTecnico = B.id WHERE A.estado !=4 AND A.fechaCrea BETWEEN ? AND ? AND A.idSucursal IN ($this->locationID) $strTecnico GROUP BY A.idTecnico, B.name) AS tickets";

            $headersTec = ['Tecnico', 'Resueltos 24hrs.', '%', 'Resueltos 48hrs.', '%', 'Resueltos 72hrs.', '%', 'Resueltos +72hrs.', '%', 'Atrasados 24hrs.', '%', 'Atrasados 48hrs.', '%', 'Atrasados 72hrs.', '%', 'Atrasados +72hrs.', '%', 'Total'];

            $tecnicos = DB::select($sql, [$this->initDate, $this->endDate]);

            $sql = "SELECT nombre, res24, res48, res72, res72M, resTotal, atr24, atr48,atr72, atr72M, atrTotal, abrTotal,total FROM (SELECT COUNT(A.idSolicitud) AS total, SUM(IF(A.estado = 3 AND A.tiempo IN (24, 25), 1, 0)) AS res24, SUM(IF(A.estado = 3 AND A.tiempo = 48, 1, 0)) AS res48, SUM(IF(A.estado = 3 AND A.tiempo = 72, 1, 0)) AS res72, SUM(IF(A.estado = 3 AND A.tiempo > 72, 1, 0)) AS res72M, SUM(IF(A.estado = 3, 1, 0)) AS resTotal, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo IN (24,25), 1, 0)) AS atr24, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 48, 1, 0)) AS atr48, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 72, 1, 0)) AS atr72, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo > 72, 1, 0)) AS atr72M, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3, 1, 0)) AS atrTotal, SUM(IF(NOT(A.estado IN (3,4)),1,0)) AS abrTotal,B.id,B.nombre FROM manto_solicitud AS A LEFT JOIN sucursales AS B ON A.idSucursal = B.id WHERE A.estado !=4 AND A.fechaCrea BETWEEN ? AND ? AND B.id IN ($this->locationID) $strTecnico GROUP BY B.id, B.nombre) AS tickets";

            $headersSuc = ['Sucursal', 'Resueltos 24hrs.', '%', 'Resueltos 48hrs.', '%', 'Resueltos 72hrs.', '%', 'Resueltos +72hrs.', '%', 'Atrasados 24hrs.', '%', 'Atrasados 48hrs.', '%', 'Atrasados 72hrs.', '%', 'Atrasados +72hrs.', '%', 'Total'];

            $sucursales = DB::select($sql, [$this->initDate, $this->endDate]);

            $sql = "SELECT nombre,idUsuario, res24, res48, res72, res72M, resTotal, atr24, atr48,atr72, atr72M, atrTotal, abrTotal,total FROM (SELECT COUNT(A.idSolicitud) AS total, SUM(IF(A.estado = 3 AND A.tiempo IN (24, 25), 1, 0)) AS res24, SUM(IF(A.estado = 3 AND A.tiempo = 48, 1, 0)) AS res48, SUM(IF(A.estado = 3 AND A.tiempo = 72, 1, 0)) AS res72, SUM(IF(A.estado = 3 AND A.tiempo > 72, 1, 0)) AS res72M, SUM(IF(A.estado = 3, 1, 0)) AS resTotal, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo IN (24,25), 1, 0)) AS atr24, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 48, 1, 0)) AS atr48, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo = 72, 1, 0)) AS atr72, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3 AND A.tiempo > 72, 1, 0)) AS atr72M, SUM(IF(HOUR(TIMEDIFF(NOW(), CONCAT(A.fechaCrea, ' ', A.horaCrea))) > A.tiempo AND A.estado != 3, 1, 0)) AS atrTotal, SUM(IF(NOT(A.estado IN (3,4)),1,0)) AS abrTotal, B.idUsuario, C.name AS nombre FROM manto_solicitud AS A LEFT JOIN supervisor_sucursal AS B ON A.idSucursal = B.idSucursal LEFT JOIN users AS C ON B.idUsuario = C.id WHERE A.estado !=4 AND A.fechaCrea BETWEEN ? AND ? AND B.idSucursal IN ($this->locationID) $strTecnico GROUP BY B.idUsuario, C.name) AS tickets";

            $headersDis = ['Distrital', 'Resueltos 24hrs.', '%', 'Resueltos 48hrs.', '%', 'Resueltos 72hrs.', '%', 'Resueltos +72hrs.', '%', 'Atrasados 24hrs.', '%', 'Atrasados 48hrs.', '%', 'Atrasados 72hrs.', '%', 'Atrasados +72hrs.', '%', 'Total'];

            $distritales = DB::select($sql, [$this->initDate, $this->endDate]);

            $sql = "SELECT COUNT(A.idSolicitud) AS total, SUM(IF(A.estado = 3, 1, 0)) AS resTotal, MONTH(A.fechaCrea) AS mes FROM manto_solicitud AS A WHERE A.estado !=4 AND A.fechaCrea BETWEEN ? AND ? AND A.idSucursal IN ($this->locationID) $strTecnico GROUP BY  MONTH(A.fechaCrea) ORDER BY MONTH(A.fechaCrea)";

            $dataGraph = DB::select($sql, [date('Y-01-01', strtotime($this->initDate)), date('Y-m-t', strtotime($this->endDate))]);

            $labels = [];
            $datasets = [];
            $lastMonth = date('m');

            foreach ($this->months as $key => $month) {
                $indice = array_search($key, array_column($dataGraph, 'mes'));
                if ($indice !== false && $key != 0) {
                    $labels[] = $month;
                    $datasets[0]['data'][] = round($dataGraph[$indice]->resTotal * 100 / $dataGraph[$indice]->total);
                } else if ($key > $lastMonth) {
                    break;
                } else if ($key != 0) {
                    $labels[] = $month;
                    $datasets[0]['data'][] = 0;
                }
            }

            // foreach ($dataGraph as $key => $value) {
            //     $labels[] = $this->months[$value->mes];
            //     $datasets[0]['data'][] = round($value->resTotal * 100 / $value->total);
            // }

            $datasets[0]['label'] = 'Desempeño %';

            $dataGraph = [
                'labels' => $labels,
                'datasets' => $datasets,
            ];

            $this->result = (object)[
                'porRes' => $porObj,
                'tecnicos' => $tecnicos,
                'sucursales' => $sucursales,
                'distritales' => $distritales,
                'dataGraph' => $dataGraph,
                'headersTec' => $headersTec,
                'headersSuc' => $headersSuc,
                'headersDis' => $headersDis,
            ];
        }
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
