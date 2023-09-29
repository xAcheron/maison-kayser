<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;
use Exception;

class VSemProdReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $initDateLw;
    private $endDateLw;
    private $location;
    private $locationID;
    private $result;
    private $tier;
    private $storelabel;
    private $detailBox;
    private $producto;
    private $companyID;

    public function setParams($params)
    {
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        $this->producto = $params["producto"];
        $date = strtotime($tmpDates[0]);

        $this->initDateLw = date('Y-m-d', strtotime("-7 day", $date));
        $this->endDateLw = date('Y-m-d', strtotime("-1 day", $date));

        $this->location = $params["location"];
        $this->tier = empty($params["tier"]) ? 0 : $params["tier"];

        if (is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);


        $this->detailBox = $params["detailBox"] == "true" ? true : false;

        $location = new UserLocation();
        $location->get($params["location"]);
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->companyID = $location->company;

        $this->perSales = empty($params["perSales"]) ? 100 : $params["perSales"];
        $this->storelabel = $locations[2];
    }

    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql, [$idLocation]);

        return array("'" . $locations[0]->idMicros . "'", $locations[0]->id,  $locations[0]->nombre);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT sucursales.*, empresas.comun FROM sucursales INNER JOIN empresas ON empresas.idEmpresa = sucursales.idEmpresa WHERE " . (empty($this->tier) || $this->tier == "null" ? "" : " idTier = " . $this->tier . " AND ") . " sucursales.idEmpresa = ?;";
        $locations = DB::select($sql, [$idEmpresa]);

        $locationArr = array();
        $locationIDArr = array();

        foreach ($locations as $location) {
            $locationArr[] = "'" . $location->idMicros . "'";
            $locationIDArr[] = "'" . $location->id . "'";
            $empresa = $location->comun;
        }

        return array(implode(",", $locationArr), implode(",", $locationIDArr), $empresa);
    }

    public function runReport()
    {

        //$sql = "SELECT G.rvc, SUM(G.guestsBreakfast), SUM(G.guestsLunch), SUM(G.guestsDinner), SUM(G.guestsNight),  SUM(G.netSalesBreakfast), SUM(G.netSalesLunch), SUM(G.netSalesDinner), SUM(G.netSalesNight),SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1), SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1), SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1), SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) FROM vds_guests G WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ? GROUP BY G.rvc";

        $prods = is_array($this->producto) ? "'" . implode("','", $this->producto) . "'" : $this->producto;

        if ($this->detailBox) {
            $sql = "SELECT VSEM.*, CAJON.cantidadCajon FROM 
            (SELECT A.idSucMicros, A.idsucursal , A.idMicros, MAX(A.idItemMicros) idItemMicros,
                SUM(IF(DAYOFWEEK(fecha)=2, A.ventaNetaImp,0)) AS vlun,
                SUM(IF(DAYOFWEEK(fecha)=3, A.ventaNetaImp,0)) AS vmar,
                SUM(IF(DAYOFWEEK(fecha)=4, A.ventaNetaImp,0)) AS vmie,
                SUM(IF(DAYOFWEEK(fecha)=5, A.ventaNetaImp,0)) AS vjue,
                SUM(IF(DAYOFWEEK(fecha)=6, A.ventaNetaImp,0)) AS vvie,
                SUM(IF(DAYOFWEEK(fecha)=7, A.ventaNetaImp,0)) AS vsab,
                SUM(IF(DAYOFWEEK(fecha)=1, A.ventaNetaImp,0)) AS vdom, 
                SUM(IF(DAYOFWEEK(fecha)=2, A.cantidad,0)) AS clun,
                SUM(IF(DAYOFWEEK(fecha)=3, A.cantidad,0)) AS cmar,
                SUM(IF(DAYOFWEEK(fecha)=4, A.cantidad,0)) AS cmie,
                SUM(IF(DAYOFWEEK(fecha)=5, A.cantidad,0)) AS cjue,
                SUM(IF(DAYOFWEEK(fecha)=6, A.cantidad,0)) AS cvie,
                SUM(IF(DAYOFWEEK(fecha)=7, A.cantidad,0)) AS csab,
                SUM(IF(DAYOFWEEK(fecha)=1, A.cantidad,0)) AS cdom,
                SUM(A.cantidad) AS cantidad,
                SUM(A.ventaNetaImp) AS venta
                FROM venta_mes_producto_micros A 
                WHERE A.idSucMicros IN (" . $this->location . ") AND A.idMicros IN ($prods) AND A.fecha BETWEEN ? AND ? GROUP BY A.idSucMicros, A.idsucursal , A.idMicros
            ) AS VSEM LEFT JOIN
            (
                SELECT Z.idSucursal,Z.idItem, SUM(Z.count) cantidadCajon , MIN(Z.fecha) mindate, MAX(Z.fecha) maxdate 
                FROM vds_modificador Z 
                WHERE Z.idsucursal IN (" . $this->locationID . ") AND Z.itemNumber = 99002389 AND Z.idItem IN ($prods) AND Z.fecha BETWEEN ? AND ? GROUP BY Z.idSucursal,Z.idItem
            ) CAJON
            ON (VSEM.idsucursal = CAJON.idSucursal AND VSEM.idMicros = CAJON.idItem );";

            $params = [$this->initDate, $this->endDate,  $this->initDate, $this->endDate];
        } else {
            $sql = "SELECT A.idSucMicros , A.idMicros, MAX(A.idItemMicros) idItemMicros,
            SUM(IF(DAYOFWEEK(fecha)=2, A.ventaNetaImp,0)) AS vlun,
            SUM(IF(DAYOFWEEK(fecha)=3, A.ventaNetaImp,0)) AS vmar,
            SUM(IF(DAYOFWEEK(fecha)=4, A.ventaNetaImp,0)) AS vmie,
            SUM(IF(DAYOFWEEK(fecha)=5, A.ventaNetaImp,0)) AS vjue,
            SUM(IF(DAYOFWEEK(fecha)=6, A.ventaNetaImp,0)) AS vvie,
            SUM(IF(DAYOFWEEK(fecha)=7, A.ventaNetaImp,0)) AS vsab,
            SUM(IF(DAYOFWEEK(fecha)=1, A.ventaNetaImp,0)) AS vdom, 
            SUM(IF(DAYOFWEEK(fecha)=2, A.cantidad,0)) AS clun,
            SUM(IF(DAYOFWEEK(fecha)=3, A.cantidad,0)) AS cmar,
            SUM(IF(DAYOFWEEK(fecha)=4, A.cantidad,0)) AS cmie,
            SUM(IF(DAYOFWEEK(fecha)=5, A.cantidad,0)) AS cjue,
            SUM(IF(DAYOFWEEK(fecha)=6, A.cantidad,0)) AS cvie,
            SUM(IF(DAYOFWEEK(fecha)=7, A.cantidad,0)) AS csab,
            SUM(IF(DAYOFWEEK(fecha)=1, A.cantidad,0)) AS cdom,
            SUM(A.cantidad) AS cantidad,
            SUM(A.ventaNetaImp) AS venta
            FROM venta_mes_producto_micros A WHERE 
            A.idSucMicros IN (" . $this->location . ") AND A.idMicros IN ($prods) AND A.fecha BETWEEN ? AND ? GROUP BY A.idSucMicros , A.idMicros;";
            $params = [$this->initDate, $this->endDate];
        }
        $prodsSelect = DB::select($sql, $params);

        $prodGraph = [];
        if (is_array($this->producto) && count($this->producto) == 1) {
            $sql = "SELECT WEEK(fecha, 3) as semana, YEAR(fecha) as id,SUM(cantidad) as cantidad FROM venta_mes_producto_micros A WHERE A.idMicros IN ($prods) AND YEAR(A.fecha) BETWEEN ? AND ? AND A.idSucMicros IN ({$this->location}) GROUP BY YEAR(fecha), WEEK(fecha, 3)";
            $prodGraph = DB::select($sql, [date('Y', strtotime("-1 YEAR")), date('Y')]);

            $array = [];
            foreach ($prodGraph as $key => $value) {
                if ($value->id == date('Y', strtotime("-1 YEAR")) || ($value->id == date('Y') && $value->semana <= date('W'))) {
                    $array[] = $value;
                } else {
                }
            }
            $prodGraph = $array;
        } else {
            $sql = "SELECT WEEK(fecha, 3) as semana, A.idItemMicros as id,SUM(cantidad) as cantidad FROM venta_mes_producto_micros A WHERE A.idMicros IN ($prods) AND YEAR(A.fecha) = ? AND A.idSucMicros IN ({$this->location}) GROUP BY YEAR(fecha), WEEK(fecha, 3), A.idMicros, A.idItemMicros ORDER BY A.idMicros,WEEK(fecha, 3)";
            $prodGraph = DB::select($sql, [date('Y')]);

            $array = [];
            foreach ($prodGraph as $key => $value) {
                if ($value->semana <= date('W')) {
                    $array[] = $value;
                }
            }
            $prodGraph = $array;
        }

        $this->result = json_decode(json_encode(array("prods" => $prodsSelect, "detailBox" => $this->detailBox, "prodGraph" => $prodGraph)));
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
        if ($this->detailBox) {
            $sql = "SELECT VSEM.*, CAJON.cantidadCajon FROM 
            (SELECT A.idSucMicros, A.idsucursal , A.idMicros, MAX(A.idItemMicros) idItemMicros,
                SUM(IF(DAYOFWEEK(fecha)=2, A.ventaNetaImp,0)) AS vlun,
                SUM(IF(DAYOFWEEK(fecha)=3, A.ventaNetaImp,0)) AS vmar,
                SUM(IF(DAYOFWEEK(fecha)=4, A.ventaNetaImp,0)) AS vmie,
                SUM(IF(DAYOFWEEK(fecha)=5, A.ventaNetaImp,0)) AS vjue,
                SUM(IF(DAYOFWEEK(fecha)=6, A.ventaNetaImp,0)) AS vvie,
                SUM(IF(DAYOFWEEK(fecha)=7, A.ventaNetaImp,0)) AS vsab,
                SUM(IF(DAYOFWEEK(fecha)=1, A.ventaNetaImp,0)) AS vdom, 
                SUM(IF(DAYOFWEEK(fecha)=2, A.cantidad,0)) AS clun,
                SUM(IF(DAYOFWEEK(fecha)=3, A.cantidad,0)) AS cmar,
                SUM(IF(DAYOFWEEK(fecha)=4, A.cantidad,0)) AS cmie,
                SUM(IF(DAYOFWEEK(fecha)=5, A.cantidad,0)) AS cjue,
                SUM(IF(DAYOFWEEK(fecha)=6, A.cantidad,0)) AS cvie,
                SUM(IF(DAYOFWEEK(fecha)=7, A.cantidad,0)) AS csab,
                SUM(IF(DAYOFWEEK(fecha)=1, A.cantidad,0)) AS cdom,
                SUM(A.cantidad) AS cantidad,
                SUM(A.ventaNetaImp) AS venta
                FROM venta_mes_producto_micros A 
                WHERE A.idSucMicros IN (" . $this->location . ") AND A.idMicros IN (" . $this->producto . ") AND A.fecha BETWEEN ? AND ? GROUP BY A.idSucMicros, A.idsucursal , A.idMicros
            ) AS VSEM LEFT JOIN
            (
                SELECT Z.idSucursal,Z.idItem, SUM(Z.count) cantidadCajon , MIN(Z.fecha) mindate, MAX(Z.fecha) maxdate 
                FROM vds_modificador Z 
                WHERE Z.idsucursal IN (" . $this->locationID . ") AND Z.itemNumber = 99002389 AND Z.idItem IN (" . $this->producto . ") AND Z.fecha BETWEEN ? AND ? GROUP BY Z.idSucursal,Z.idItem
            ) CAJON
            ON (VSEM.idsucursal = CAJON.idSucursal AND VSEM.idMicros = CAJON.idItem );";

            $params = [$this->initDate, $this->endDate, $this->initDate, $this->endDate];
        } else {
            $sql = "SELECT A.idSucMicros , A.idMicros, MAX(A.idItemMicros) idItemMicros,
            SUM(IF(DAYOFWEEK(fecha)=2, A.ventaNetaImp,0)) AS vlun,
            SUM(IF(DAYOFWEEK(fecha)=3, A.ventaNetaImp,0)) AS vmar,
            SUM(IF(DAYOFWEEK(fecha)=4, A.ventaNetaImp,0)) AS vmie,
            SUM(IF(DAYOFWEEK(fecha)=5, A.ventaNetaImp,0)) AS vjue,
            SUM(IF(DAYOFWEEK(fecha)=6, A.ventaNetaImp,0)) AS vvie,
            SUM(IF(DAYOFWEEK(fecha)=7, A.ventaNetaImp,0)) AS vsab,
            SUM(IF(DAYOFWEEK(fecha)=1, A.ventaNetaImp,0)) AS vdom, 
            SUM(IF(DAYOFWEEK(fecha)=2, A.cantidad,0)) AS clun,
            SUM(IF(DAYOFWEEK(fecha)=3, A.cantidad,0)) AS cmar,
            SUM(IF(DAYOFWEEK(fecha)=4, A.cantidad,0)) AS cmie,
            SUM(IF(DAYOFWEEK(fecha)=5, A.cantidad,0)) AS cjue,
            SUM(IF(DAYOFWEEK(fecha)=6, A.cantidad,0)) AS cvie,
            SUM(IF(DAYOFWEEK(fecha)=7, A.cantidad,0)) AS csab,
            SUM(IF(DAYOFWEEK(fecha)=1, A.cantidad,0)) AS cdom,
            SUM(A.cantidad) AS cantidad,
            SUM(A.ventaNetaImp) AS venta
            FROM venta_mes_producto_micros A WHERE 
            A.idSucMicros IN (" . $this->location . ") AND A.idMicros IN (" . $this->producto . ") AND A.fecha BETWEEN ? AND ? GROUP BY A.idSucMicros , A.idMicros;";
            $params = [$this->initDate, $this->endDate];
        }
        $prods = DB::select($sql, $params);

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


        $spreadsheet->getActiveSheet()->getStyle('A2:D6')->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $spreadsheet->getActiveSheet()->getStyle('A9' . ($this->detailBox ? ':S9' : ':R9'))->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Producto');
        $currentSheet->setCellValue('A4', 'Fechas');
        $currentSheet->setCellValue('A5', 'Location');
        $currentSheet->setCellValue('A6', 'Export Date');

        $currentSheet->setCellValue('B2', 'Venta producto semanal');
        $currentSheet->setCellValue('B3', $this->producto);
        $currentSheet->setCellValue('B4', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B5', strtoupper($this->location));
        $currentSheet->setCellValue('B6', date("Y-m-d"));

        $currentSheet->setCellValue('A9', 'Producto');
        $currentSheet->setCellValue('B9', 'Sucursal');
        $currentSheet->setCellValue('C9', 'Lunes $');
        $currentSheet->setCellValue('D9', 'Lunes #');
        $currentSheet->setCellValue('E9', 'Martes $');
        $currentSheet->setCellValue('F9', 'Martes #');
        $currentSheet->setCellValue('G9', 'Miercoles $');
        $currentSheet->setCellValue('H9', 'Miercoles #');
        $currentSheet->setCellValue('I9', 'Jueves $');
        $currentSheet->setCellValue('J9', 'Jueves #');
        $currentSheet->setCellValue('K9', 'Viernes $');
        $currentSheet->setCellValue('L9', 'Viernes #');
        $currentSheet->setCellValue('M9', 'Sabado $');
        $currentSheet->setCellValue('N9', 'Sabado #');
        $currentSheet->setCellValue('O9', 'Domingo $');
        $currentSheet->setCellValue('P9', 'Domingo #');
        $currentSheet->setCellValue('Q9', 'Venta semanal');
        $currentSheet->setCellValue('R9', 'Cantidad semanal');
        if ($this->detailBox)
            $currentSheet->setCellValue('S9', 'Cajon Impulso');

        $row = 10;
        $startRowFormat = $row;
        foreach ($prods as $prod) {
            $currentSheet->setCellValue('A' . $row, $prod->idItemMicros);
            $currentSheet->setCellValue('B' . $row, $prod->idSucMicros);
            $currentSheet->setCellValue('C' . $row, $prod->vlun);
            $currentSheet->setCellValue('D' . $row, $prod->clun);
            $currentSheet->setCellValue('E' . $row, $prod->vmar);
            $currentSheet->setCellValue('F' . $row, $prod->cmar);
            $currentSheet->setCellValue('G' . $row, $prod->vmie);
            $currentSheet->setCellValue('H' . $row, $prod->cmie);
            $currentSheet->setCellValue('I' . $row, $prod->vjue);
            $currentSheet->setCellValue('J' . $row, $prod->cjue);
            $currentSheet->setCellValue('K' . $row, $prod->vvie);
            $currentSheet->setCellValue('L' . $row, $prod->cvie);
            $currentSheet->setCellValue('M' . $row, $prod->vsab);
            $currentSheet->setCellValue('N' . $row, $prod->csab);
            $currentSheet->setCellValue('O' . $row, $prod->vdom);
            $currentSheet->setCellValue('P' . $row, $prod->cdom);
            $currentSheet->setCellValue('Q' . $row, $prod->venta);
            $currentSheet->setCellValue('R' . $row, $prod->cantidad);
            if ($this->detailBox)
                $currentSheet->setCellValue('S' . $row, $prod->cantidadCajon);
            $row++;
        }
        $endRowFormat = $row - 1;

        $spreadsheet->getActiveSheet()->getStyle('A' . $startRowFormat . ($this->detailBox ? ':S' : ':R') . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="VentaSemProd' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }
}
