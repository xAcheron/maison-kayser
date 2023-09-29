<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;
use Exception;

class VentaSucReporter implements iReporter
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
    }

    public function runReport()
    {

        $sql = "SELECT * FROM vta_suc_dia WHERE idSucursal = ? AND fecha BETWEEN ? AND ?";
        $venta = DB::select($sql, [$this->locationID, $this->initDate, $this->endDate]);
        $sql = "SELECT idSucursal, SUM(Z.ventaNeta) AS ventaNeta, SUM(Z.ventaBruta) AS ventaBruta, SUM(Z.impuesto) AS impuesto, SUM(Z.descuentos) AS descuentos, SUM(Z.servicio) AS servicio FROM vta_suc_dia Z WHERE idSucursal = ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal";
        $ventaGlobal = DB::select($sql, [$this->locationID, $this->initDate, $this->endDate]);

        $this->result = json_decode(json_encode(array("venta" => $venta, "ventaGlobal" => $ventaGlobal)));
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
        $venta = $this->result->venta;

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

        $currentSheet->setCellValue('B2', 'Venta Sucursal');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $col = 1;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue('Dia');
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue('Fecha');
        $col++;

        $currentSheet->mergeCells(Coordinate::stringFromColumnIndex($col) . "7:" . Coordinate::stringFromColumnIndex($col + 2) . "7");
        $currentSheet->getCellByColumnAndRow($col, 7)->setValue("Total General");
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Venta Neta");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Venta Bruta");
        $col++;
        $currentSheet->getCellByColumnAndRow($col, 8)->setValue("Impuesto");

        $letra = Coordinate::stringFromColumnIndex($col);

        $spreadsheet->getActiveSheet()->getStyle('A7:' . $letra . '8')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');


        $row = 9;
        $col = 1;
        $jsonstr = json_encode($venta);
        $ventaArr = json_decode($jsonstr, true);

        $format_numbers = array();


        foreach ($ventaArr as $fecha => $venta) {
            if ($venta['fecha'] >= $this->initDate) {
                $currentSheet->getCellByColumnAndRow(1, $row)->setValue($this->dias[date("w", strtotime($venta['fecha']))]);
                $currentSheet->getCellByColumnAndRow(2, $row)->setValue($venta['fecha']);
                $col = 3;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($venta['ventaNeta']);
                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($venta['ventaBruta']);
                $col++;
                $currentSheet->getCellByColumnAndRow($col, $row)->setValue($venta['impuesto']);
                if (!in_array($col, $format_numbers))
                    $format_numbers[] = $col;
                $row++;
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Venta_sucursal' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }

    public function widget($tipo = 0)
    {
    }
}
