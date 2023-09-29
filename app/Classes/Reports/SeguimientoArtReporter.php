<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;
use Exception;

class SeguimientoArtReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $companyID;
    private $result;
    private $user;

    private $widgetType;

    public function setParams($params)
    {

        if (empty($params["daterange"]) || $params["daterange"] == "All") {
            $this->initDate = date("Y-m-01");
            $this->endDate = date("Y-m-t");
        } else {
            $tmpDates = explode(" - ", $params["daterange"]);
            $this->initDate = $tmpDates[0];
            $this->endDate = $tmpDates[1];
        }

        $this->widgetType = empty($params["refs"]) ? 0 : $params["refs"];

        $location = new UserLocation();
        $location->get($params["location"], $params['typeLoc'], $params['idUsuario']);
        $this->user = $params['idUsuario'];
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->companyID = $location->company;
    }

    public function runReport()
    {

        $sql = "SELECT 'Sin Grupo' as nombre ,0 AS grupo, idItem AS item FROM producto_seguimiento_item WHERE idUsuario = ? UNION SELECT C.grupo as nombre, A.idGrupo AS grupo, B.idProducto AS item FROM producto_seguimiento_grupo A INNER JOIN producto_agrupador B ON A.idGrupo = B.idGrupo INNER JOIN producto_grupo C ON B.idGrupo = C.idGrupo WHERE idUsuario = ?";
        $items = DB::select($sql, [$this->user, $this->user]);
        $sql = '';

        foreach ($items as $key => $value) {
            if (!empty($sql))
                $sql .= ",";
            $sql .= "'$value->item'";
        }

        $permiso = DB::table('config_app_access')
            ->select('idRole')
            ->where('idAplicacion', 23)
            ->where('idUsuario', $this->user)
            ->get()
            ->toArray();

        $permiso = $permiso[0]->idRole;

        if ($this->user == 0) {
            //TODO: Query para sacar la informacion de las demas empresas
            $sql = "SELECT fecha ,idSucMicros, idMicros, idItemMicros, SUM(cantidad) AS cantidad, SUM(ventaNeta) AS ventaNeta FROM venta_mes_producto_micros Z INNER JOIN dashboard_sucursal_usuario S ON Z.idsucursal = S.idSucursal WHERE Z.idMicros IN ($sql) AND S.idUsuario = ? AND Z.fecha = DATE(NOW()) GROUP BY fecha , idSucMicros, idMicros, idItemMicros;";
            $venta = DB::select($sql, [$this->user]);
        } else {
            $sql = "SELECT fecha ,idSucMicros, idMicros, idItemMicros, SUM(cantidad) AS cantidad, SUM(ventaNeta) AS ventaNeta FROM venta_mes_producto_micros Z WHERE Z.idMicros IN ($sql) AND Z.idSucMicros LIKE 'EKE%' AND Z.fecha = DATE(NOW()) GROUP BY fecha , idSucMicros, idMicros, idItemMicros;";
        }
        $venta = DB::select($sql, [$this->user]);

        $sucursales = ['Menu Item'];
        $formats = [];

        $tables = [];

        $ventasPorSucursal = [];

        if (!empty($venta)) {
            foreach ($items as $key => $value) {
                $ventasPorSucursal[$value->item] = [];
            }
        }

        foreach ($venta as $key => $value) {
            if (!in_array($value->idSucMicros, $sucursales)) {
                $sucursales[] = $value->idSucMicros;
            }
            if (!in_array($value->idItemMicros, $ventasPorSucursal[$value->idMicros])) {
                $ventasPorSucursal[$value->idMicros][] = $value->idItemMicros;
            }
            $ventasPorSucursal[$value->idMicros][] = $value->cantidad;
        }
        $grupoAnt = 0;
        $index = 0;
        $dataTemp = [];
        $sucursales[] = 'Total';
        $formats = array_fill(0, count($sucursales) - 1, 'N');


        array_unshift($formats, 'T');
        if (!empty($ventasPorSucursal)) {
            foreach ($items as $key => $value) {
                if ($value->grupo != $grupoAnt) {
                    $grupoAnt = $value->grupo;
                    $totales = [];
                    $totales = array_fill(0, count($sucursales), 0);
                    $totales[0] = 'Total';
                    $totalesItem = 0;
                    foreach ($dataTemp as $keyTemp => $item) {
                        foreach ($sucursales as $keySuc => $suc) {
                            if ($suc == 'Total') {
                                $totalesItem = array_sum(array_filter($item, 'is_numeric'));
                                $dataTemp[$keyTemp][] = $totalesItem;
                            }
                            if (array_key_exists($keySuc, $dataTemp[$keyTemp])) {
                                if (is_numeric($dataTemp[$keyTemp][$keySuc])) {
                                    $totales[$keySuc] += $dataTemp[$keyTemp][$keySuc];
                                }
                            } else {
                                $dataTemp[$keyTemp][] = 0;
                            }
                        }
                    }
                    array_push($dataTemp, $totales);
                    $tables[$index]['grupo'] = $items[$key - 1]->nombre;
                    $tables[$index]['headers'] = $sucursales;
                    $tables[$index]['data'] = $dataTemp;
                    $tables[$index]['formats'] = $formats;
                    $dataTemp = [];
                    $index++;
                }

                $dataTemp[] = $ventasPorSucursal[$value->item];


                if (count($items) - 1 == $key) {
                    $totales = [];
                    $totales = array_fill(0, count($sucursales), 0);
                    $totales[0] = 'Total';
                    $totalesItem = 0;
                    foreach ($dataTemp as $keyTemp => $item) {
                        foreach ($sucursales as $keySuc => $suc) {
                            if ($suc == 'Total') {
                                $totalesItem = array_sum(array_filter($item, 'is_numeric'));
                                $dataTemp[$keyTemp][] = $totalesItem;
                            }
                            if (array_key_exists($keySuc, $dataTemp[$keyTemp])) {
                                if (is_numeric($dataTemp[$keyTemp][$keySuc])) {
                                    $totales[$keySuc] += $dataTemp[$keyTemp][$keySuc];
                                }
                            } else {
                                $dataTemp[$keyTemp][] = 0;
                            }
                        }
                    }
                    array_push($dataTemp, $totales);
                    $tables[$index]['grupo'] = $items[$key - 1]->nombre;
                    $tables[$index]['headers'] = $sucursales;
                    $tables[$index]['data'] = $dataTemp;
                    $tables[$index]['formats'] = $formats;
                }
            }
        } else {
            $tables[0]['grupo'] = 'Sin grupo';
            $tables[0]['headers'] = $sucursales;
            $tables[0]['data'] = [];
            $tables[0]['formats'] = [];
        }
        $this->result = $tables;
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
        $spreadsheet = new Spreadsheet();
        $sugestedItems = $spreadsheet->getActiveSheet();

        $tables = $this->result;

        $sugestedItems->getColumnDimension('A')->setWidth(25);
        $sugestedItems->getColumnDimension('B')->setWidth(14);
        $sugestedItems->getColumnDimension('C')->setWidth(14);
        $sugestedItems->getColumnDimension('D')->setWidth(14);
        $sugestedItems->getColumnDimension('E')->setWidth(14);
        $sugestedItems->getColumnDimension('F')->setWidth(14);
        $sugestedItems->getColumnDimension('G')->setWidth(14);
        $sugestedItems->getColumnDimension('H')->setWidth(10);

        $sugestedItems->setTitle('Report');
        $sugestedItems = $spreadsheet->getActiveSheet();
        $sugestedItems->setTitle('Report');

        $sugestedItems->setCellValue('A1', 'Reporte');
        $sugestedItems->setCellValue('A2', 'Hora');
        $sugestedItems->setCellValue('B2', date("H:i", strtotime(date("Y-m-d H:i:s") . " +8 HOURS")));

        $row = 5;
        foreach ($tables as $key => $table) {
            $col = 1;
            $sucursales = $table['headers'];
            $dataFina = $table['data'];
            $grupo = $table['grupo'];

            $columnLetter = Coordinate::stringFromColumnIndex(count($sucursales) + 1);
            $sugestedItems->mergeCells("A$row:$columnLetter" . "$row");
            $style = $sugestedItems->getStyle("A$row");
            $alignment = $style->getAlignment();
            $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sugestedItems->setCellValue("A$row", $grupo);
            $row++;

            foreach ($sucursales as $suc) {
                $sugestedItems->setCellValueByColumnAndRow($col, $row, $suc);
                $col++;
            }
            $row++;
            foreach ($dataFina as  $iditem => $Items) {
                $col = 1;
                foreach ($Items as $item) {
                    $sugestedItems->setCellValueByColumnAndRow($col, $row, $item);
                    $col++;
                }
                $row++;
            }
            $row++;
        }


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="menuitem_report_' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }


    public function widget($tipo = 0)
    {
    }
}
