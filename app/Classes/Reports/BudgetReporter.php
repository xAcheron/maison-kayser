<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Classes\Reports\utils\UserLocation;

class BudgetReporter implements iReporter
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
            $this->initDate = date("Y-m-") . "01";
            if (date("d") == date("d", strtotime($this->initDate)) && date("d") == "01") {
                $this->initDate =  date("Y-m-", strtotime($this->initDate . " -1 DAY")) . "01";
            }
        } else
            $this->initDate = $params["daterange"];



        $this->endDate = date("Y-m-d");

        $this->lastWeek = date("Y-m-d", strtotime(date("Y-m-d") . " -1 WEEK"));

        $location = new UserLocation();
        $location->get($params["location"], $params["typeLoc"] ?? '', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
    }

    public function runReport()
    {

        $ddom = abs(7 - date("N"));
        $dlun = abs(date("N") - 1);
        $currentWEEK = date("d", strtotime(date("Y-m-d") . " -" . $dlun . " day")) . " - " . date("d", strtotime(date("Y-m-d") . " +" . $ddom . " day"));

        $sql = "SELECT s.nombre ,B.* FROM budget_dia_sucursal B INNER JOIN sucursales s ON B.idSucursal = s.id WHERE B.idSucursal IN (" . $this->locationID . ") AND MONTH(B.fecha)=MONTH(?) AND YEAR(B.fecha)=YEAR(?) ORDER BY fecha;";
        $budget = DB::select($sql, [$this->initDate, $this->initDate]);
        $sql = "SELECT s.nombre ,B.* FROM budget_mes_sucursal B INNER JOIN sucursales s ON B.idSucursal = s.id WHERE B.idSucursal IN  (" . $this->locationID . ") AND B.fecha=? ORDER BY s.id, B.fecha;";
        $budgetMes = DB::select($sql, [$this->initDate]);
        $sql = "SELECT s.id, s.nombre , SUM(netSales)/1.16 netSales FROM venta_diaria_sucursal B INNER JOIN sucursales s ON B.idSucursal = s.id WHERE B.idSucursal IN  (" . $this->locationID . ") AND MONTH(B.fecha)=MONTH(?) AND YEAR(B.fecha)=YEAR(?) GROUP BY s.id, s.nombre ,idSucursal;";
        $ventaMes = DB::select($sql, [$this->initDate, $this->initDate]);
        $sql = "SELECT s.id, s.nombre , fecha,  netSales/1.16 as netSales FROM venta_diaria_sucursal B INNER JOIN sucursales s ON B.idSucursal = s.id WHERE B.idSucursal IN  (" . $this->locationID . ") AND MONTH(B.fecha)=MONTH(?) AND YEAR(B.fecha)=YEAR(?) ORDER BY idSucursal, fecha;";
        $ventaDia = DB::select($sql, [$this->initDate, $this->initDate]);
        $sql = "SELECT Actual.idSucursal, Actual.netSales/1.16 VentaActual, Anterior.netSales/1.16 VentaAnterior, Budget.budget, Budget.L, Budget.M, Budget.Mr, Budget.J, Budget.V, Budget.S, Budget.D ,BudgetMes.budget BudgetMes FROM 
        (SELECT idSucursal,WEEK(fecha,7) fecha, SUM(netSales) netSales FROM venta_diaria_sucursal WHERE YEAR(fecha) = YEAR(NOW()) AND WEEK(NOW(),7) = WEEK(fecha,7) AND MONTH(fecha)=MONTH(?) AND YEAR(fecha)=YEAR(?) AND idSucursal IN (" . $this->locationID . ") GROUP BY idSucursal,WEEK(fecha,7)) Actual
        LEFT JOIN (SELECT idSucursal,WEEK(fecha,7) fecha, SUM(netSales) netSales FROM venta_diaria_sucursal WHERE YEAR(fecha) = YEAR(NOW()) AND WEEK(?,7) = WEEK(fecha,7) AND MONTH(fecha)=MONTH(?) AND YEAR(fecha)=YEAR(?) AND idSucursal IN (" . $this->locationID . ") GROUP BY idSucursal,WEEK(fecha,7)) Anterior
        ON Actual.idSucursal = Anterior.idSucursal
        LEFT JOIN (SELECT idSucursal, SUM(B.budget) budget, SUM(IF(DAYOFWEEK(fecha)=2,budget,0))AS L,SUM(IF(DAYOFWEEK(fecha)=3, budget,0))AS M, SUM(IF(DAYOFWEEK(fecha)=4,budget,0))AS Mr, SUM(IF(DAYOFWEEK(fecha)=5,budget,0))AS J, SUM(IF(DAYOFWEEK(fecha)=6,budget,0))AS V, SUM(IF(DAYOFWEEK(fecha)=7,budget,0))AS S, SUM(IF(DAYOFWEEK(fecha)=1,budget,0))AS D FROM budget_dia_sucursal B WHERE B.idSucursal IN (" . $this->locationID . ") AND MONTH(fecha)=MONTH(?) AND YEAR(fecha)=YEAR(?) AND WEEK(?,7) = WEEK(fecha,7) GROUP BY idSucursal ORDER BY fecha) Budget
        ON Budget.idSucursal = Actual.idSucursal
        LEFT JOIN (SELECT idSucursal, monto AS budget FROM budget_mes_sucursal C WHERE C.fecha = ? AND idSucursal IN (" . $this->locationID . ")) BudgetMes
        ON BudgetMes.idSucursal= Actual.idSucursal;";
        $budgetSemana = DB::select($sql, [$this->initDate, $this->initDate, $this->lastWeek, $this->initDate, $this->initDate, $this->initDate, $this->initDate, $this->initDate, $this->initDate]);
        $this->result = json_decode(json_encode(array("Bdia" => $budget, "Bmes" => $budgetMes, "venta" => $ventaMes, "ventaDia" => $ventaDia, "Bsem" => $budgetSemana, "SemAnt" => $this->lastWeek, "SemAct" => $currentWEEK)));
    }

    private function exportReport()
    {
        $result = $this->result;

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

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A7:D7')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $currentSheet->mergeCells('B2:D2');
        $currentSheet->mergeCells('B3:D3');
        $currentSheet->mergeCells('B4:D4');
        $currentSheet->mergeCells('B5:D5');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Business Dates');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');

        $currentSheet->setCellValue('B2', 'Budget');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));


        $currentSheet->setCellValue('A7', 'Sucursal');
        $currentSheet->setCellValue('B7', 'Budget');
        $currentSheet->setCellValue('C7', 'Venta acumulada');
        $currentSheet->setCellValue('D7', 'Diferencia');

        $row = 8;
        $initRowFormat = $row;
        foreach ($result->Bmes as $id => $data) {
            $currentSheet->setCellValue('A' . $row, $data->nombre);
            $currentSheet->setCellValue('B' . $row, $data->monto);
            $currentSheet->setCellValue('C' . $row, $result->venta[$id]->netSales);
            $currentSheet->setCellValue('D' . $row, $result->venta[$id]->netSales - $data->monto);
            $row++;
        }

        $endRowFormat = $row - 1;
        $spreadsheet->getActiveSheet()->getStyle('A' . $initRowFormat . ':D' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        $spreadsheet->getActiveSheet()->getStyle('B' . $initRowFormat . ':D' . $endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $row += 2;
        $currentSheet->setCellValue('A' . $row, 'Fecha');
        $currentSheet->setCellValue('B' . $row, 'Budget');
        $currentSheet->setCellValue('C' . $row, 'Venta acumulada');
        $currentSheet->setCellValue('D' . $row, 'Diferencia');

        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $row++;
        $initRowFormat = $row;
        foreach ($result->Bdia as $id => $data) {
            $currentSheet->setCellValue('A' . $row, $data->fecha);
            $currentSheet->setCellValue('B' . $row, $data->budget);
            $currentSheet->setCellValue('C' . $row, $result->ventaDia[$id]->netSales);
            $currentSheet->setCellValue('D' . $row, $result->ventaDia[$id]->netSales - $data->budget);
            $row++;
        }

        $endRowFormat = $row - 1;
        $spreadsheet->getActiveSheet()->getStyle('A' . $initRowFormat . ':D' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        $spreadsheet->getActiveSheet()->getStyle('B' . $initRowFormat . ':D' . $endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Budget_' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
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
        $sql = "SELECT MONTH(B.fecha), SUM(monto) AS monto FROM budget_mes_sucursal B INNER JOIN sucursales s ON B.idSucursal = s.id WHERE B.idSucursal IN  (" . $this->locationID . ") AND B.fecha=? GROUP BY MONTH(B.fecha);";
        $budgetMes = DB::select($sql, [$this->initDate]);
        $sql = "SELECT SUM(netSales)/1.16 netSales FROM venta_diaria_sucursal B WHERE B.idSucursal IN  (" . $this->locationID . ") AND MONTH(B.fecha)=MONTH(?) AND YEAR(B.fecha)=YEAR(?) GROUP BY MONTH(B.fecha);";
        $ventaMes = DB::select($sql, [$this->initDate, $this->endDate]);

        $object = new \stdClass();
        $object->titulo = "Budget";
        $object->subtitulo = "Enero 2023";
        $object->footer = "Venta actual: $" . number_format($ventaMes[0]->netSales, 0);

        //dd($this->initDate);

        $budget = $ventaMes[0]->netSales / $budgetMes[0]->monto * 100;
        $object->value = number_format($budget, 0);
        $object->type = "N";
        $object->company = 1;
        $object->indicator = ($budget >= 100 ? "over" : "under");
        $this->result = json_decode(json_encode($object));
    }
}
