<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DiscountReporter implements iReporter 
{
    
    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $result;
    private $tier;

    public function setParams($params)
    {
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        $this->location = $params["location"];
        $this->tier = empty($params["tier"])?0:$params["tier"];
        
        if(is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);
        
        $this->location = $locations[0];
        $this->locationID = $locations[1];
    }
    
    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql,[$idLocation]);
        return array( "'".$locations[0]->idMicros."'", $locations[0]->id );
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT * FROM sucursales WHERE ".(empty($this->tier) || $this->tier == "null" ?"": " idTier = ".$this->tier." AND ")." idEmpresa = ?;";
        $locations = DB::select($sql,[$idEmpresa]);
        
        $locationArr = array();
        $locationIDArr = array();

        foreach($locations as $location)
        {
            $locationArr[] = "'".$location->idMicros."'";
            $locationIDArr[] = "'".$location->id."'";
        }
        return array(implode(",",$locationArr), implode(",",$locationIDArr));
        
    }

    public function runReport()
    {
        $sql = "SELECT DSC.idDescuento, MD.discount, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento DSC LEFT JOIN micros_descuento MD ON DSC.idDescuento = MD.idDiscountMicros WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.fecha BETWEEN ? AND ? GROUP BY DSC.idDescuento, MD.discount ORDER BY descuento;";
        $discounts = DB::select($sql,[$this->initDate, $this->endDate]);
        $sql = "SELECT DSC.idDescuento, MD.discount,S.nombre location, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento DSC LEFT JOIN micros_descuento MD ON DSC.idDescuento = MD.idDiscountMicros INNER JOIN sucursales S ON S.id = DSC.idSucursal WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.fecha BETWEEN ? AND ?  GROUP BY DSC.idDescuento,MD.discount,DSC.idSucursal,S.nombre ORDER BY descuento;";
        $locations = DB::select($sql,[$this->initDate, $this->endDate]);
        $this->result = json_decode(json_encode(array("discounts" => $discounts, "locations" => $locations )));
    }

    public function getResult($type)
    {
        if($type == "xlsx")
        {
            $this->exportReport();
        }
        else
        {
            $parser = new ReportParser($type);
            return $parser->parse($this->result);
        }
    }

    public function exportReport()
    {

        $sql = "SELECT DSC.idDescuento, MD.discount, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento DSC LEFT JOIN micros_descuento MD ON DSC.idDescuento = MD.idDiscountMicros WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.fecha BETWEEN ? AND ? GROUP BY DSC.idDescuento, MD.discount ORDER BY descuento;";
        $discounts = DB::select($sql,[$this->initDate, $this->endDate]);
        $sql = "SELECT DSC.idDescuento, MD.discount,S.nombre location, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento DSC LEFT JOIN micros_descuento MD ON DSC.idDescuento = MD.idDiscountMicros INNER JOIN sucursales S ON S.id = DSC.idSucursal WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.fecha BETWEEN ? AND ?  GROUP BY DSC.idDescuento,MD.discount,DSC.idSucursal,S.nombre ORDER BY descuento;";
        $locations = DB::select($sql,[$this->initDate, $this->endDate]);
        $sql = "SELECT MP.idItemMicros ,MP.itemName, SUM(DSC.cantidad) cantidad, SUM(DSC.descuento) descuento FROM vds_descuento_producto DSC LEFT JOIN micros_descuento MD ON DSC.idDescuento = MD.idDiscountMicros INNER JOIN micros_producto MP ON DSC.idArticulo = MP.idItemMicros INNER JOIN sucursales S ON S.id = DSC.idSucursal WHERE DSC.idSucursal IN (".$this->locationID.") AND DSC.fecha BETWEEN ? AND ? GROUP BY DSC.idDescuento,MD.discount,DSC.idSucursal,S.nombre, MP.idItemMicros ,MP.itemName ORDER BY descuento;";
        $products = DB::select($sql,[$this->initDate, $this->endDate]);

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

        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );    
        
        $currentSheet->mergeCells('B2:D2');
        $currentSheet->mergeCells('B3:D3');
        $currentSheet->mergeCells('B4:D4');
        $currentSheet->mergeCells('B5:D5');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Business Dates');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');
    
        $currentSheet->setCellValue('B2', 'Discounts');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $currentSheet->setCellValue('A7', 'Dsc #');
        $currentSheet->setCellValue('B7', 'Discount');
        $currentSheet->setCellValue('C7', 'Redeemed');
        $currentSheet->setCellValue('D7', 'Value');

        $row = 8;
        
        foreach($discounts as $data)
        {
            $currentSheet->setCellValue('A'.$row, $data->idDescuento);
            $currentSheet->setCellValue('B'.$row, $data->discount);
            $currentSheet->setCellValue('C'.$row, $data->cantidad);
            $currentSheet->setCellValue('D'.$row, $data->descuento);
            $row++;
        }

        $endRowFormat = $row-1;
        
        $spreadsheet->getActiveSheet()->getStyle('A8:A'.$endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
        $spreadsheet->getActiveSheet()->getStyle('A7:D'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );
        $row+=2;
        $initRowFormat = $row;
        $currentSheet->mergeCells('A'.$row.':E'.$row);
        $currentSheet->setCellValue('A'.$row, 'Location Discounts');
        $row++;
        $currentSheet->setCellValue('A'.$row, 'Location');
        $currentSheet->setCellValue('B'.$row, 'Dsc #');
        $currentSheet->setCellValue('C'.$row, 'Discount');
        $currentSheet->setCellValue('D'.$row, 'Redeemed');
        $currentSheet->setCellValue('E'.$row, 'Value');

        $spreadsheet->getActiveSheet()->getStyle('A'.($row-1).':E'.$row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('ffe3e3e3');

        $row++;
        foreach($locations as $data)
        {
            $currentSheet->setCellValue('A'.$row, $data->location);
            $currentSheet->setCellValue('B'.$row, $data->idDescuento);
            $currentSheet->setCellValue('C'.$row, $data->discount);
            $currentSheet->setCellValue('D'.$row, $data->cantidad);
            $currentSheet->setCellValue('E'.$row, $data->descuento);
            $row++;
        }

        
        $endRowFormat = $row-1;
        $spreadsheet->getActiveSheet()->getStyle('A'.$initRowFormat.':B'.$endRowFormat)
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A'.$initRowFormat.':E'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );


        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Discounts'.date("Ymd").'.xlsx"');
        $writer->save("php://output");

    }

}