<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;

class DpartReporter implements iReporter
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
            $empresasUsuario = $location->getHierachy(1);
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
        // $this->perSales = empty($params["perSales"])?100:$params["perSales"];

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
        $sql = "SELECT RVC.rvc RvcName, VT.* FROM (SELECT G.rvc, SUM(G.guests) guests, SUM(G.netSales) netSales, SUM(G.netSales)/COALESCE(SUM(G.guests),1) avgCheck, SUM(G.guestsBreakfast) gb, SUM(G.guestsLunch) gl, SUM(G.guestsDinner) gd, SUM(G.guestsNight) gn,  SUM(G.netSalesBreakfast) nsb, SUM(G.netSalesLunch) nsl, SUM(G.netSalesDinner) nsd, SUM(G.netSalesNight) nsn,SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1) avgb, SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1) avgl, SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1) avgd, SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) avgn FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY G.rvc) VT INNER JOIN sucursales_rvc RVC ON VT.rvc = RVC.idRvc WHERE RVC.idEmpresa = ?;";
        $rvc = DB::select($sql, [$this->initDate, $this->endDate, $this->companyID]);
        $sql = "SELECT S.nombre location, VT.* FROM (SELECT G.idSucursal, SUM(G.guests) guests, SUM(G.netSales) netSales, SUM(G.netSales)/COALESCE(SUM(G.guests),1) avgCheck, SUM(G.guestsBreakfast) gb, SUM(G.guestsLunch) gl, SUM(G.guestsDinner) gd, SUM(G.guestsNight) gn,  SUM(G.netSalesBreakfast) nsb, SUM(G.netSalesLunch) nsl, SUM(G.netSalesDinner) nsd, SUM(G.netSalesNight) nsn,SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1) avgb, SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1) avgl, SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1) avgd, SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) avgn FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY G.idSucursal) VT INNER JOIN sucursales S ON VT.idSucursal = S.id";
        $location  = DB::select($sql, [$this->initDate, $this->endDate]);
        $this->result = json_decode(json_encode(array("rvcs" => $rvc, "locations" => $location)));
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
        /*
        $sql = "SELECT RVC.rvc RvcName, VT.* FROM (SELECT G.rvc, SUM(G.guestsBreakfast) gb, SUM(G.guestsLunch) gl, SUM(G.guestsDinner) gd, SUM(G.guestsNight) gn,  SUM(G.netSalesBreakfast) nsb, SUM(G.netSalesLunch) nsl, SUM(G.netSalesDinner) nsd, SUM(G.netSalesNight) nsn,SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1) avgb, SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1) avgl, SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1) avgd, SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) avgn FROM vds_guests G WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ? GROUP BY G.rvc) VT INNER JOIN sucursales_rvc RVC ON VT.rvc = RVC.idRvc";
        $rvc = DB::select($sql,[$this->initDate, $this->endDate]);
        $sql = "SELECT S.nombre location ,VT.* FROM (SELECT G.idSucursal,  SUM(G.guestsBreakfast) gb, SUM(G.guestsLunch) gl, SUM(G.guestsDinner) gd, SUM(G.guestsNight) gn,  SUM(G.netSalesBreakfast) nsb, SUM(G.netSalesLunch) nsl, SUM(G.netSalesDinner) nsd, SUM(G.netSalesNight) nsn,SUM(G.netSalesBreakfast)/COALESCE(SUM(G.guestsBreakfast),1) avgb, SUM(G.netSalesLunch)/COALESCE(SUM(G.guestsLunch),1) avgl, SUM(G.netSalesDinner)/COALESCE(SUM(G.guestsDinner),1) avgd, SUM(G.netSalesNight)/COALESCE(SUM(G.guestsNight),1) avgn FROM vds_guests G WHERE idSucursal IN (".$this->locationID.") AND fecha BETWEEN ? AND ? GROUP BY G.idSucursal) VT INNER JOIN sucursales S ON VT.idSucursal = S.id";
        $location  = DB::select($sql,[$this->initDate, $this->endDate]);*/

        $rvc = $this->result->rvcs;
        $location = $this->result->locations;
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

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $spreadsheet->getActiveSheet()->getStyle('A7:P8')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Day Part');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');

        $currentSheet->setCellValue('B2', 'Product Mix');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));



        $currentSheet->setCellValue('A7', '');
        $currentSheet->setCellValue('B7', 'GUEST');
        $currentSheet->setCellValue('G7', 'SALES');
        $currentSheet->setCellValue('L7', 'AVG CHECK');
        $currentSheet->setCellValue('A8', 'RVC');
        $currentSheet->setCellValue('B8', 'Breakfast');
        $currentSheet->setCellValue('C8', 'Lunch');
        $currentSheet->setCellValue('D8', 'Dinner');
        $currentSheet->setCellValue('E8', 'Night');
        $currentSheet->setCellValue('F8', 'Total');
        $currentSheet->setCellValue('G8', 'Breakfast');
        $currentSheet->setCellValue('H8', 'Lunch');
        $currentSheet->setCellValue('I8', 'Dinner');
        $currentSheet->setCellValue('J8', 'Night');
        $currentSheet->setCellValue('K8', 'Total');
        $currentSheet->setCellValue('L8', 'Breakfast');
        $currentSheet->setCellValue('M8', 'Lunch');
        $currentSheet->setCellValue('N8', 'Dinner');
        $currentSheet->setCellValue('O8', 'Night');
        $currentSheet->setCellValue('P8', 'Total');

        $gb = 0;
        $gl = 0;
        $gd = 0;
        $gn = 0;
        $guests = 0;
        $nsb = 0;
        $nsl = 0;
        $nsd = 0;
        $nsn = 0;
        $netSales = 0;
        $avgb = 0;
        $avgl = 0;
        $avgd = 0;
        $avgn = 0;
        $avgCheck = 0;

        $row = 9;
        foreach ($rvc as $data) {
            $currentSheet->setCellValue('A' . $row, $data->RvcName);
            $currentSheet->setCellValue('B' . $row, $data->gb);
            $currentSheet->setCellValue('C' . $row, $data->gl);
            $currentSheet->setCellValue('D' . $row, $data->gd);
            $currentSheet->setCellValue('E' . $row, $data->gn);
            $currentSheet->setCellValue('F' . $row, $data->guests);
            $currentSheet->setCellValue('G' . $row, $data->nsb);
            $currentSheet->setCellValue('H' . $row, $data->nsl);
            $currentSheet->setCellValue('I' . $row, $data->nsd);
            $currentSheet->setCellValue('J' . $row, $data->nsn);
            $currentSheet->setCellValue('K' . $row, $data->netSales);
            $currentSheet->setCellValue('L' . $row, $data->avgb);
            $currentSheet->setCellValue('M' . $row, $data->avgl);
            $currentSheet->setCellValue('N' . $row, $data->avgd);
            $currentSheet->setCellValue('O' . $row, $data->avgn);
            $currentSheet->setCellValue('P' . $row, $data->avgCheck);

            $gb += $data->gb;
            $gl += $data->gl;
            $gd += $data->gd;
            $gn += $data->gn;
            $guests += $data->guests;
            $nsb += $data->nsb;
            $nsl += $data->nsl;
            $nsd += $data->nsd;
            $nsn += $data->nsn;
            $netSales += $data->netSales;
            $avgb += $data->avgb;
            $avgl += $data->avgl;
            $avgd += $data->avgd;
            $avgn += $data->avgn;
            $avgCheck += $data->avgCheck;

            $row++;
        }

        $currentSheet->setCellValue('A' . $row, "Total");
        $currentSheet->setCellValue('B' . $row, $gb);
        $currentSheet->setCellValue('C' . $row, $gl);
        $currentSheet->setCellValue('D' . $row, $gd);
        $currentSheet->setCellValue('E' . $row, $gn);
        $currentSheet->setCellValue('F' . $row, $guests);
        $currentSheet->setCellValue('G' . $row, $nsb);
        $currentSheet->setCellValue('H' . $row, $nsl);
        $currentSheet->setCellValue('I' . $row, $nsd);
        $currentSheet->setCellValue('J' . $row, $nsn);
        $currentSheet->setCellValue('K' . $row, $netSales);
        $currentSheet->setCellValue('L' . $row, $avgb);
        $currentSheet->setCellValue('M' . $row, $avgl);
        $currentSheet->setCellValue('N' . $row, $avgd);
        $currentSheet->setCellValue('O' . $row, $avgn);
        $currentSheet->setCellValue('P' . $row, $netSales / (empty($guests) ? 1 : $guests));
        $row++;

        $endRowFormat = $row - 1;

        $spreadsheet->getActiveSheet()->getStyle('A8:A' . $endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A7:P' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $row++;


        $startRowFormat = $row;

        $currentSheet->setCellValue('A' . $row, '');
        $currentSheet->setCellValue('B' . $row, 'GUEST');
        $currentSheet->setCellValue('G' . $row, 'SALES');
        $currentSheet->setCellValue('L' . $row, 'AVG CHECK');
        $row++;
        $currentSheet->setCellValue('A' . $row, 'RVC');
        $currentSheet->setCellValue('B' . $row, 'Breakfast');
        $currentSheet->setCellValue('C' . $row, 'Lunch');
        $currentSheet->setCellValue('D' . $row, 'Dinner');
        $currentSheet->setCellValue('E' . $row, 'Night');
        $currentSheet->setCellValue('F' . $row, 'Total');
        $currentSheet->setCellValue('G' . $row, 'Breakfast');
        $currentSheet->setCellValue('H' . $row, 'Lunch');
        $currentSheet->setCellValue('I' . $row, 'Dinner');
        $currentSheet->setCellValue('J' . $row, 'Night');
        $currentSheet->setCellValue('K' . $row, 'Total');
        $currentSheet->setCellValue('L' . $row, 'Breakfast');
        $currentSheet->setCellValue('M' . $row, 'Lunch');
        $currentSheet->setCellValue('N' . $row, 'Dinner');
        $currentSheet->setCellValue('O' . $row, 'Night');
        $currentSheet->setCellValue('P' . $row, 'Total');

        $gb = 0;
        $gl = 0;
        $gd = 0;
        $gn = 0;
        $guests = 0;
        $nsb = 0;
        $nsl = 0;
        $nsd = 0;
        $nsn = 0;
        $netSales = 0;
        $avgb = 0;
        $avgl = 0;
        $avgd = 0;
        $avgn = 0;
        $avgCheck = 0;

        $row++;
        foreach ($location as $data) {
            $currentSheet->setCellValue('A' . $row, $data->location);
            $currentSheet->setCellValue('B' . $row, $data->gb);
            $currentSheet->setCellValue('C' . $row, $data->gl);
            $currentSheet->setCellValue('D' . $row, $data->gd);
            $currentSheet->setCellValue('E' . $row, $data->gn);
            $currentSheet->setCellValue('F' . $row, $data->guests);
            $currentSheet->setCellValue('G' . $row, $data->nsb);
            $currentSheet->setCellValue('H' . $row, $data->nsl);
            $currentSheet->setCellValue('I' . $row, $data->nsd);
            $currentSheet->setCellValue('J' . $row, $data->nsn);
            $currentSheet->setCellValue('K' . $row, $data->netSales);
            $currentSheet->setCellValue('L' . $row, $data->avgb);
            $currentSheet->setCellValue('M' . $row, $data->avgl);
            $currentSheet->setCellValue('N' . $row, $data->avgd);
            $currentSheet->setCellValue('O' . $row, $data->avgn);
            $currentSheet->setCellValue('P' . $row, $guests > 0 ? $netSales / $guests : 0);

            $gb += $data->gb;
            $gl += $data->gl;
            $gd += $data->gd;
            $gn += $data->gn;
            $guests += $data->guests;
            $nsb += $data->nsb;
            $nsl += $data->nsl;
            $nsd += $data->nsd;
            $nsn += $data->nsn;
            $netSales += $data->netSales;
            $avgb += $data->avgb;
            $avgl += $data->avgl;
            $avgd += $data->avgd;
            $avgn += $data->avgn;
            $avgCheck += $data->avgCheck;

            $row++;
        }

        $currentSheet->setCellValue('A' . $row, "Total");
        $currentSheet->setCellValue('B' . $row, $gb);
        $currentSheet->setCellValue('C' . $row, $gl);
        $currentSheet->setCellValue('D' . $row, $gd);
        $currentSheet->setCellValue('E' . $row, $gn);
        $currentSheet->setCellValue('F' . $row, $guests);
        $currentSheet->setCellValue('G' . $row, $nsb);
        $currentSheet->setCellValue('H' . $row, $nsl);
        $currentSheet->setCellValue('I' . $row, $nsd);
        $currentSheet->setCellValue('J' . $row, $nsn);
        $currentSheet->setCellValue('K' . $row, $netSales);
        $currentSheet->setCellValue('L' . $row, $avgb);
        $currentSheet->setCellValue('M' . $row, $avgl);
        $currentSheet->setCellValue('N' . $row, $avgd);
        $currentSheet->setCellValue('O' . $row, $avgn);
        $currentSheet->setCellValue('P' . $row, $netSales / (empty($guests) ? 1 : $guests));

        $row++;

        $endRowFormat = $row - 1;

        $spreadsheet->getActiveSheet()->getStyle('A' . ($startRowFormat + 1) . ':A' . $endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A' . $startRowFormat . ':P' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $spreadsheet->getActiveSheet()->getStyle('A' . $startRowFormat . ':P' . ($startRowFormat + 1))->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Daypart' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }

    public function widget()
    {
        $sql = "SELECT 
            SUM(G.guestsBreakfast) guestsBreakfast, SUM(netSalesBreakfast) netSalesBreakfast, SUM(netSalesBreakfast)/SUM(G.guestsBreakfast) AS AvgCheckBreakfast,
            SUM(G.guestsLunch) guestsLunch, SUM(netSalesLunch) netSalesLunch, SUM(netSalesLunch)/SUM(G.guestsLunch) AS AvgCheckLunch,
            SUM(G.guestsDinner) guestsDinner, SUM(netSalesDinner) netSalesDinner, SUM(netSalesDinner)/SUM(G.guestsDinner) AS AvgCheckDinner,
            SUM(G.guestsNight) guestsNight, SUM(netSalesNight) netSalesNight, SUM(netSalesNight)/SUM(G.guestsNight) AS AvgCheckNight
        FROM 
            vds_guests G 
        WHERE idSucursal IN (" . $this->locationID . ") AND fecha BETWEEN ? AND ? GROUP BY YEAR(fecha);";

        $dparts = DB::select($sql, [$this->initDate, $this->endDate]);
        $month = date("m", strtotime($this->initDate));
        $finalData = array();

        $headers = array("", $this->months[intval($month)], "Visitas", "Cheque");
        $formats = array("T", "N", "N", "N");

        $finalData = array("titulo" => "Horario", "headers" => $headers, "data" => array(), "formats" => $formats);

        $finalData["data"][] = array("Desayuno", $dparts[0]->netSalesBreakfast, $dparts[0]->guestsBreakfast, number_format($dparts[0]->AvgCheckBreakfast, 0, "", ""));
        $finalData["data"][] = array("Comida", $dparts[0]->netSalesLunch, $dparts[0]->guestsLunch, number_format($dparts[0]->AvgCheckLunch, 0, "", ""));
        $finalData["data"][] = array("Cena", $dparts[0]->netSalesDinner, $dparts[0]->guestsDinner, number_format($dparts[0]->AvgCheckDinner, 0, "", ""));
        $finalData["data"][] = array("Nocturno", $dparts[0]->netSalesNight, $dparts[0]->guestsNight, number_format($dparts[0]->AvgCheckNight, 0, "", ""));


        $this->result = json_decode(json_encode($finalData));
    }
}