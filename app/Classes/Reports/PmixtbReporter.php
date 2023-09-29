<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PmixtbReporter implements iReporter {

    private $initDate;
    private $endDate;
    private $location;    
    private $locationID;
    private $result;
    private $family;
    private $company;

    public function setParams($params)
    {

        $tmpDates = explode(" - ", $params["daterange"]);
        
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        $this->location = $params["location"];
        $this->family = $params["family"];
        $this->tier = empty($params["tier"])?0:$params["tier"];

        if(is_numeric($params["location"]))
            $locations = $this->getLocations($params["location"]);
        else
            $locations = $this->getLocation($params["location"]);
        
        $this->location = $locations[0];
        $this->locationID = $locations[1];
        $this->company = $locations[2];
        $this->perSales = empty($params["perSales"])?100:$params["perSales"];
        $this->storelabel = $locations[2];

    }
    
    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql,[$idLocation]);
        
        return array( "'".$locations[0]->idMicros."'", $locations[0]->id,  $locations[0]->idEmpresa);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT S.*, empresas.comun, S.idEmpresa ,T.tier FROM sucursales S LEFT JOIN sucursales_tier T ON S.idTier = T.idTier INNER JOIN empresas ON empresas.idEmpresa = S.idEmpresa WHERE ".(empty($this->tier) || $this->tier == "null" ?"": " S.idTier = ".$this->tier." AND ")." S.idEmpresa = ?;";
        $locations = DB::select($sql,[$idEmpresa]);
        
        $locationArr = array();
        $locationIDArr = array();

        foreach($locations as $location)
        {
            $locationArr[] = "'".$location->idMicros."'";
            $locationIDArr[] = "'".$location->id."'";
            $empresa = $location->idEmpresa;
        }

        return array(implode(",",$locationArr), implode(",",$locationIDArr), $empresa);
        
    }

    public function runReport(){
        $sql = "SELECT VT.*, F.family,MP.itemName ,COALESCE(rp.costo,0) * VT.preference AS cost, ((COALESCE(rp.costo,0) * VT.preference)/VT.netSales)*100 costoper, (VT.netSales - (COALESCE(rp.costo,0) * VT.preference)) margin ,  COALESCE(C.idMajor, 0)idMajor , COALESCE(C.idFamily, 0) idFamily  FROM (
            SELECT Z.idArticulo, SUM(IF(Z.idDayPart=1, Z.quantity,0)) AS breakfast, SUM(IF(Z.idDayPart=2, Z.quantity,0)) AS Lunch, SUM(IF(Z.idDayPart=3, Z.quantity,0)) AS Dinner, SUM(IF(Z.idDayPart=4, Z.quantity,0)) AS night, SUM(Z.quantity) AS preference, SUM(Z.netSales)/1.16 netSales
            FROM vds_producto_daypart Z WHERE (Z.fecha BETWEEN ? AND ?) AND Z.idSucursal IN (".$this->locationID.")
            GROUP BY Z.idArticulo ORDER BY netSales DESC LIMIT 0,20) AS VT INNER JOIN micros_producto MP ON MP.idItemMicros = VT.idArticulo
            LEFT JOIN micros_producto_clasificacion C ON VT.idArticulo = C.idMicros
            LEFT JOIN micros_family_group F ON (C.idFamily = F.idFamily AND F.idEmpresa = ".$this->company." )
            LEFT JOIN recetas_platillo rp ON rp.idMicros = VT.idArticulo ".(!empty($this->family)? " WHERE C.idFamily IN (".$this->family.")": "")." ORDER BY VT.netSales DESC;";
        $top = DB::select($sql,[$this->initDate, $this->endDate]);

        $sql = "SELECT VT.*, F.family,MP.itemName ,COALESCE(rp.costo,0) * VT.preference AS cost, ((COALESCE(rp.costo,0) * VT.preference)/VT.netSales)*100 costoper, (VT.netSales - (COALESCE(rp.costo,0) * VT.preference)) margin , COALESCE(C.idMajor, 0)idMajor , COALESCE(C.idFamily, 0) idFamily  FROM (
            SELECT Z.idArticulo, SUM(IF(Z.idDayPart=1, Z.quantity,0)) AS breakfast, SUM(IF(Z.idDayPart=2, Z.quantity,0)) AS Lunch, SUM(IF(Z.idDayPart=3, Z.quantity,0)) AS Dinner, SUM(IF(Z.idDayPart=4, Z.quantity,0)) AS night, SUM(Z.quantity) AS preference, SUM(Z.netSales)/1.16 netSales
            FROM vds_producto_daypart Z LEFT JOIN micros_producto_clasificacion CC ON Z.idArticulo = CC.idMicros WHERE (Z.fecha BETWEEN ? AND ?) AND Z.idSucursal IN (".$this->locationID.") AND NOT( CC.idFamily IN(1010, 1018, 1028))
            GROUP BY Z.idArticulo HAVING SUM(Z.netSales)>0 ORDER BY netSales ASC LIMIT 0,20) AS VT INNER JOIN micros_producto MP ON MP.idItemMicros = VT.idArticulo
            LEFT JOIN micros_producto_clasificacion C ON VT.idArticulo = C.idMicros
            LEFT JOIN micros_family_group F ON (C.idFamily = F.idFamily AND F.idEmpresa = ".$this->company." )
            LEFT JOIN recetas_platillo rp ON rp.idMicros = VT.idArticulo ".(!empty($this->family)? " WHERE C.idFamily IN (".$this->family.")": "")." ORDER BY VT.netSales ASC ;";
        $btn = DB::select($sql,[$this->initDate, $this->endDate]);

        $this->result =json_decode(json_encode(array("top" => $top, "btn" => $btn )));        
        
    }

    public function getResult($type){
        if($type == "xlsx"){
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
 
        $sql = "SELECT VT.*, F.family, MP.itemName ,COALESCE(rp.costo,0) * VT.preference AS cost, ((COALESCE(rp.costo,0) * VT.preference)/VT.netSales)*100 costoper, (VT.netSales - (COALESCE(rp.costo,0) * VT.preference)) margin , 0 perFamily, 0 perMajor, 0 perMenu, COALESCE(C.idMajor, 0)idMajor , COALESCE(C.idFamily, 0) idFamily  FROM (
            SELECT Z.idArticulo, SUM(IF(Z.idDayPart=1, Z.quantity,0)) AS breakfast, SUM(IF(Z.idDayPart=2, Z.quantity,0)) AS Lunch, SUM(IF(Z.idDayPart=3, Z.quantity,0)) AS Dinner, SUM(IF(Z.idDayPart=4, Z.quantity,0)) AS night, SUM(Z.quantity) AS preference, SUM(Z.netSales)/1.16 netSales
            FROM vds_producto_daypart Z WHERE (Z.fecha BETWEEN ? AND ?) AND Z.idSucursal IN (".$this->locationID.")
            GROUP BY Z.idArticulo) AS VT INNER JOIN micros_producto MP ON MP.idItemMicros = VT.idArticulo
            LEFT JOIN micros_producto_clasificacion C ON VT.idArticulo = C.idMicros
            LEFT JOIN micros_family_group F ON  (C.idFamily = F.idFamily AND F.idEmpresa = ".$this->company." )
            LEFT JOIN recetas_platillo rp ON rp.idMicros = VT.idArticulo ".(!empty($this->family)? " WHERE C.idFamily IN (".$this->family.")": "")." ORDER BY VT.netSales DESC , cost ;";

        $tmpResult = DB::select($sql,[$this->initDate, $this->endDate]);

        $sql = "SELECT COALESCE(C.idMajor, 0)idMajor , COALESCE(C.idFamily, 0) idFamily, SUM(W.netSales) netSales FROM vds_producto_daypart W INNER JOIN micros_producto_clasificacion C ON W.idArticulo = C.idMicros WHERE W.idSucursal IN (".$this->locationID.") AND W.fecha BETWEEN ? AND ? GROUP BY C.idMajor, C.idFamily;";
        $families = DB::select($sql,[$this->initDate, $this->endDate]);
        $majorsArr = array();
        $majorsArr[0] = 0;
        $familiesArr  = array();
        $familiesArr[0] = 0;
        $totalSales =  0;

        foreach($families as $family)
        {
            $familiesArr[$family->idFamily]  = $family->netSales;
            if(empty($majorsArr[$family->idMajor]))
                $majorsArr[$family->idMajor]=0;
            $majorsArr[$family->idMajor]  += $family->netSales;
            $totalSales += $family->netSales;
        }
        //dd($familiesArr);
        foreach($tmpResult as $id => $row){
            if(empty($familiesArr[$row->idFamily]))
            $tmpResult[$id]->perFamily = 100;
            else
            $tmpResult[$id]->perFamily = $row->netSales/$familiesArr[$row->idFamily]*100;
            if(empty($majorsArr[$row->idMajor]))
            $tmpResult[$id]->perMajor = 100;
            else
            $tmpResult[$id]->perMajor= $row->netSales/$majorsArr[$row->idMajor]*100;

            $tmpResult[$id]->perMenu= $row->netSales/$totalSales*100;
            //$this->result[$id]->netSales = $this->result[$id]->netSales;
        }

        
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


        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );    
        
        $spreadsheet->getActiveSheet()->getStyle('A7:O7')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('ffe3e3e3');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Business Date');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');
    
        $currentSheet->setCellValue('B2', 'Product Mix');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $currentSheet->setCellValue('A7', 'Item #');
        $currentSheet->setCellValue('B7', 'Family');
        $currentSheet->setCellValue('C7', 'Menu Item');
        $currentSheet->setCellValue('D7', 'Breakfast');
        $currentSheet->setCellValue('E7', 'Lunch');
        $currentSheet->setCellValue('F7', 'Dinner');
        $currentSheet->setCellValue('G7', 'Night');
        $currentSheet->setCellValue('H7', 'Preference');
        $currentSheet->setCellValue('I7', 'Net Sales');
        $currentSheet->setCellValue('J7', 'Family %');
        $currentSheet->setCellValue('K7', 'Major %');
        $currentSheet->setCellValue('L7', 'Menu %');
        $currentSheet->setCellValue('M7', 'COGS');
        $currentSheet->setCellValue('N7', 'COGS %');
        $currentSheet->setCellValue('O7', 'Gross Margin');


        $row = 8;
        
        foreach($tmpResult as $data)
        {
            $currentSheet->setCellValue('A'.$row, $data->idArticulo);
            $currentSheet->setCellValue('B'.$row, $data->family);
            $currentSheet->setCellValue('C'.$row, $this->clean($data->itemName));
            $currentSheet->setCellValue('D'.$row, $data->breakfast);
            $currentSheet->setCellValue('E'.$row, $data->Lunch);
            $currentSheet->setCellValue('F'.$row, $data->Dinner);
            $currentSheet->setCellValue('G'.$row, $data->night);
            $currentSheet->setCellValue('H'.$row, $data->preference);
            $currentSheet->setCellValue('I'.$row, $data->netSales);            
            $currentSheet->setCellValue('J'.$row, $data->perFamily);
            $currentSheet->setCellValue('K'.$row, $data->perMajor);
            $currentSheet->setCellValue('L'.$row, $data->perMenu);
            $currentSheet->setCellValue('M'.$row, $data->cost);
            $currentSheet->setCellValue('N'.$row, $data->costoper);
            $currentSheet->setCellValue('O'.$row, $data->margin);
            $row++;
        }

        $endRowFormat = $row-1;
        
        $spreadsheet->getActiveSheet()->getStyle('A8:A'.$endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
        $spreadsheet->getActiveSheet()->getStyle('A7:O'.$endRowFormat)->getBorders()->applyFromArray( [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ] ] );

        

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="ProductMix'.date("Ymd").'.xlsx"');
        $writer->save("php://output");

    }

    function clean($string) {
        //$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
     
        return preg_replace('/[^A-Za-z0-9\s]/', '', $string); // Removes special chars.
     }

}