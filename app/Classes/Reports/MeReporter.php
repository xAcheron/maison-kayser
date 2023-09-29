<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MeReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $result;
    private $company;

    public function setParams($params)
    {
        $tmpDates = explode(" - ", $params["daterange"]);
        $this->initDate = $tmpDates[0];
        $this->endDate = $tmpDates[1];
        if (is_numeric($params["location"])) {
            $this->company = $params["location"];
            $locations = $this->getLocations($params["location"]);
            $this->location = $locations[0];
            $this->locationID = $locations[1];
        } else {
            $tmpLocationInfo = $this->getLocation($params["location"]);
            $locations = "'" . $params["location"] . "'";
            $this->company = $tmpLocationInfo[2];
            $this->location = $tmpLocationInfo[0];
            $this->locationID = $tmpLocationInfo[1];
        }
        $this->result = null;
    }

    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql, [$idLocation]);
        return array("'" . $locations[0]->idMicros . "'", $locations[0]->id,  $locations[0]->idEmpresa);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT * FROM sucursales WHERE idEmpresa = ?;";
        $locations = DB::select($sql, [$idEmpresa]);
        $locationArr = array();
        $locationIDArr = array();

        foreach ($locations as $location) {
            $locationArr[] = "'" . $location->idMicros . "'";
            $locationIDArr[] = $location->id;
        }
        return array(implode(",", $locationArr), implode(",", $locationIDArr));
    }

    public function runReport()
    {
        /*
        $sql = "SELECT clas.idMajor, mmg.major, SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(descuento)descuento, 0 AS salesPercent, SUM(vmpm.cantidad*rp.costo) AS costo FROM venta_mes_producto_micros vmpm LEFT JOIN micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?)  LEFT JOIN recetas_platillo rp ON rp.idMicros = vmpm.idMicros WHERE clas.idMajor != 0 AND vmpm.idSucMicros IN (".$this->location.") AND vmpm.fecha BETWEEN ? AND ?  GROUP BY clas.idMajor, mmg.major ORDER BY clas.idMajor;";
        $this->result = DB::select($sql,[$this->company, $this->initDate, $this->endDate]);
        */

        $sql = "SELECT clas.idMajor, mmg.major, SUM(costo) AS costo, SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta,SUM(ventaNetaImp) ventaNetaImp, SUM(descuento)descuento, 0 AS salesPercent  FROM 
        (SELECT VM.idItem AS idMicros, SUM( VM.count * rp.costo) costo, SUM(VM.netSales) ventaBruta, SUM(VM.netSales) ventaNeta,SUM(VM.netSales/1.16)ventaNetaImp , 0 AS descuento
        FROM vds_modificador VM LEFT JOIN recetas_platillo rp ON rp.idMicros = VM.itemNumber 
        WHERE VM.idSucursal IN(" . $this->locationID . ") AND VM.fecha BETWEEN ? AND ? GROUP BY VM.idItem
        UNION ALL
        SELECT vmpm.idMicros, SUM(vmpm.cantidad* rp.costo) costo , SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(descuento)descuento FROM venta_mes_producto_micros vmpm
        LEFT JOIN recetas_platillo rp ON rp.idMicros = vmpm.idMicros
        WHERE vmpm.idSucMicros IN (" . $this->location . ") AND  vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idMicros
        ) AS VT
        LEFT JOIN micros_producto_clasificacion clas ON VT.idMicros = clas.idMicros
        LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?)  
        GROUP BY clas.idMajor, mmg.major ORDER BY clas.idMajor;";

        $sql = "SELECT
            idMajor, major,SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta, SUM(ventaNetaImp)ventaNetaImp, SUM(descuento)descuento, SUM(cantidad) cantidad, SUM(costo) costo, SUM(SCMB) SCMB, SUM(ventaBruta) - SUM(costo) Margen, 0 salesPercent
        FROM 
        (
            SELECT VTSC.idMajor,VTSC.major, SUM(VTSC.ventaBruta) ventaBruta ,  SUM(VTSC.ventaNeta) ventaNeta,  SUM(VTSC.ventaNetaImp) ventaNetaImp,  SUM(VTSC.descuento) descuento,  SUM(VTSC.cantidad) cantidad,  SUM(VTSC.cantidad- COALESCE(CM.cantidad,0)) AS SCMB,  SUM(((VTSC.cantidad- COALESCE(CM.cantidad,0)) * VTSC.costo)) AS costo FROM 
                (SELECT vmpm.idMicros ,vmpm.idItemMicros, mmg.idMajor, mmg.major , SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad, MAX(rp.costo) AS costo
                FROM 
                    venta_mes_producto_micros vmpm 
                LEFT JOIN 
                    micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros 
                LEFT JOIN 
                    micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                LEFT JOIN 
                recetas_platillo rp ON rp.idMicros = vmpm.idMicros
                WHERE vmpm.idSucMicros IN (" . $this->location . ") AND vmpm.fecha BETWEEN ? AND ?  GROUP BY vmpm.idMicros, vmpm.idItemMicros, mmg.idMajor, mmg.major HAVING SUM(vmpm.ventaNeta) > 0
                ) VTSC
                LEFT JOIN (SELECT CMB.itemNumber, SUM(CMB.count) cantidad FROM vds_combo CMB WHERE CMB.idSucursal IN (" . $this->locationID . ") AND CMB.fecha BETWEEN ? AND ?  GROUP BY CMB.itemNumber) CM ON CM.itemNumber = VTSC.idMicros
                GROUP BY VTSC.idMajor ,VTSC.major
            UNION ALL
                SELECT 
                mmg.idMajor, mmg.major, SUM(VM.netSales) ventaBruta,SUM(VM.netSales) ventaNeta, SUM(VM.netSales)ventaNetaImp, 0 descuento, 0 cantidad, 0 SCMB, SUM(VM.count * rp.costo) costo
                FROM vds_modificador VM 
                LEFT JOIN 
                    micros_producto_clasificacion clas ON VM.idItem = clas.idMicros 
                LEFT JOIN 
                    micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                LEFT JOIN recetas_platillo rp ON rp.idMicros = VM.itemNumber 
                WHERE 
                VM.idSucursal IN (" . $this->locationID . ") AND VM.fecha BETWEEN ? AND ? 
                GROUP BY mmg.idMajor,mmg.major
        ) AS VT GROUP BY idMajor,major ORDER BY ventaBruta DESC";

        $sql = "SELECT idMajor, major,  SUM(ventaNeta) AS ventaNeta, SUM(ventaBruta) AS ventaBruta, SUM(ventaNetaImp) AS ventaNetaImp, SUM(descuento) AS descuento, SUM(costo + costoMods + costoCMB) AS costo , SUM(cantidad - cantidadCMB) AS cantidad FROM 
        (
            SELECT 
            VTSC.*, clas.idMajor,mmg.major, ((VTSC.cantidad-COALESCE( CMB.cantidad,0)) * COALESCE(rp.costo,0)) costo, COALESCE( CMB.cantidad,0) cantidadCMB, COALESCE( CMB2.costo,0) costoCMB, COALESCE(MODS.costo,0) costoMods FROM 
            (
                SELECT vmpm.idsucursal AS id,vmpm.idSucMicros,vmpm.idMicros ,vmpm.idItemMicros, SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad
                FROM venta_mes_producto_micros vmpm 
                WHERE vmpm.idsucursal IN (" . $this->locationID . ") AND vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idsucursal, vmpm.idSucMicros,vmpm.idMicros, vmpm.idItemMicros HAVING SUM(vmpm.ventaNeta) > 0
            ) VTSC
            LEFT JOIN micros_producto_clasificacion clas ON VTSC.idMicros = clas.idMicros
            LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?)
            LEFT JOIN recetas_platillo_costo rp ON (rp.idMicros = VTSC.idMicros AND rp.anio = YEAR(?) AND rp.mes = MONTH(?))
            LEFT JOIN 
            (
                SELECT CM.idSucursal, CM.idItemMicros, SUM(cantidad) cantidad FROM vds_items_combo CM WHERE (CM.fecha BETWEEN ? AND ?) AND CM.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM.idSucursal, CM.idItemMicros
            )	CMB ON (CMB.idSucursal = VTSC.id AND CMB.idItemMicros = VTSC.idMicros)
            LEFT JOIN (
                SELECT CM2.idSucursal, CM2.idCombo, SUM(costo) costo FROM vds_costo_combo CM2 WHERE (CM2.fecha BETWEEN ? AND ?) AND CM2.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM2.idSucursal, CM2.idCombo
            ) CMB2
            ON (CMB2.idCombo = VTSC.idMicros AND CMB2.idSucursal = VTSC.id)
            LEFT JOIN (
                SELECT MD.idSucursal, MD.idItem , SUM(costo) costo FROM vds_costo_modificador MD WHERE (MD.fecha BETWEEN ? AND ?) AND MD.idSucursal IN (" . $this->locationID . ")
                GROUP BY MD.idSucursal, MD.idItem
            ) MODS
            ON (MODS.idItem = VTSC.idMicros AND MODS.idSucursal = VTSC.id)        
        ) AS VTAT GROUP BY idMajor, major ORDER BY ventaNeta DESC, major ";
        
        $this->result = DB::select($sql, [ $this->initDate, $this->endDate, $this->company, $this->initDate,$this->initDate,$this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate]);
        
        //$this->result = DB::select($sql, [$this->company, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->company, $this->initDate, $this->endDate]);
        
        /*
        $sql = "SELECT SUM(A.count * B.costo) costo FROM vds_combo A INNER JOIN recetas_platillo B ON A.itemNumber = B.idMicros INNER JOIN sucursales S ON S.id = A.idSucursal WHERE S.id IN (" . $this->locationID . ") AND NOT(A.idCombo = A.itemNumber) AND A.fecha BETWEEN ? AND ? GROUP BY YEAR(A.fecha)";
        $costoCombo =  DB::select($sql, [$this->initDate, $this->endDate]);
        */

        $tmpGrossTotal = 0;
        $tmpNetTotal = 0;
        $tmpventaNetaImp = 0;
        $tmpCosto = 0;
        $majors = array();

        foreach ($this->result as $row)
        {
            array_push($majors, $row->major);
            $tmpGrossTotal += $row->ventaBruta;
            $tmpNetTotal += $row->ventaNeta;
            $tmpventaNetaImp += $row->ventaNetaImp;
            $tmpCosto += $row->costo;
        }

        foreach ($this->result as $id => $row) {
            $this->result[$id]->salesPercent = !empty($tmpGrossTotal) ? $row->ventaBruta / $tmpGrossTotal : 0;
        }

        $this->result[] = json_decode(json_encode(array("idMajor" => 0, "major" => "Total", "ventaBruta" => $tmpGrossTotal, "ventaNeta" => $tmpNetTotal, "ventaNetaImp" => $tmpventaNetaImp, "salesPercent" => 100, "costo" => $tmpCosto)));
        $this->result = array("majors" => $majors, "report" => $this->result);
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
        $tmpResult = $this->result["report"];
        $sql = "SELECT VT.* , (VT.cantidad * rp.costo) AS costo, VT.ventaBruta - (VT.cantidad * rp.costo) Margen, 0 AS salesPercent  FROM (SELECT vmpm.idMicros, mmg.major ,vmpm.idItemMicros , SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad FROM venta_mes_producto_micros vmpm LEFT JOIN micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) WHERE mmg.idMajor != 0 And NOT(mmg.idMajor = 107) AND vmpm.idSucMicros IN (" . $this->location . ")  AND vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idMicros, vmpm.idItemMicros, mmg.major HAVING SUM(vmpm.ventaNeta) >0 ORDER BY ventaBruta DESC, cantidad DESC) AS VT LEFT JOIN recetas_platillo rp ON rp.idMicros = VT.idMicros ORDER BY VT.ventaBruta DESC;";
        $impuesto = ($this->company<3?1.16:($this->company==4?1.10:1));
/*****************QUERY ORIGINAL ********************
        $sql = "SELECT
                idMicros, MAX(idItemMicros) idItemMicros, MAX(major) major , SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta, SUM(ventaNetaImp)ventaNetaImp, SUM(descuento)descuento, SUM(SCMB) cantidad , SUM(costo) costo, SUM(SCMB) SCMB , SUM(ventaBruta) - SUM(costo) Margen , 0 salesPercent
            FROM 
            (
                SELECT VTSC.idMicros ,VTSC.idItemMicros, VTSC.major , VTSC.ventaBruta, VTSC.ventaNeta, VTSC.ventaNetaImp, VTSC.descuento, VTSC.cantidad,  VTSC.cantidad-COALESCE(CM.cantidad,0) AS SCMB ,(COALESCE(((VTSC.cantidad-COALESCE(CM.cantidad,0)) * VTSC.costo),0) + COALESCE(CM2.costo, 0)) AS costo FROM 
                    (SELECT vmpm.idMicros ,vmpm.idItemMicros, mmg.major , SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad, MAX(rp.costo) AS costo
                    FROM 
                        venta_mes_producto_micros vmpm 
                    LEFT JOIN 
                        micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros 
                    LEFT JOIN 
                        micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                    LEFT JOIN 
                    recetas_platillo rp ON rp.idMicros = vmpm.idMicros
                    WHERE vmpm.idSucMicros IN (" . $this->location . ") AND vmpm.fecha BETWEEN ? AND ?  GROUP BY vmpm.idMicros, vmpm.idItemMicros, mmg.major HAVING SUM(vmpm.ventaNeta) > 0
                    ) VTSC
                    LEFT JOIN (SELECT CMB.itemNumber, SUM(CMB.count) cantidad FROM vds_combo CMB WHERE CMB.idSucursal IN (" . $this->locationID . ") AND CMB.fecha BETWEEN ? AND ?  GROUP BY CMB.itemNumber) CM ON CM.itemNumber = VTSC.idMicros
                    LEFT JOIN (SELECT A.idCombo,SUM(A.count * B.costo) costo FROM vds_combo A INNER JOIN recetas_platillo B ON A.itemNumber = B.idMicros INNER JOIN sucursales S ON S.id = A.idSucursal WHERE S.id IN (" . $this->locationID . ") AND NOT(A.idCombo = A.itemNumber) AND A.fecha BETWEEN ? AND ? GROUP BY A.idCombo) CM2 ON CM2.idCombo = VTSC.idMicros
                UNION ALL
                    SELECT 
                    VM.idItem AS idMicros, '' idItemMicros, mmg.major , SUM(VM.netSales) ventaBruta,SUM(VM.netSales) ventaNeta, SUM(VM.netSales)ventaNetaImp, 0 descuento, 0 cantidad, 0 SCMB, SUM(VM.count * rp.costo) costo
                    FROM vds_modificador VM 
                    LEFT JOIN 
                        micros_producto_clasificacion clas ON VM.idItem = clas.idMicros 
                    LEFT JOIN 
                        micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                    LEFT JOIN recetas_platillo rp ON rp.idMicros = VM.itemNumber 
                    WHERE 
                    VM.idSucursal IN (" . $this->locationID . ") AND VM.fecha BETWEEN ? AND ? 
                    GROUP BY VM.idItem, mmg.major			
            ) AS VT GROUP BY idMicros ORDER BY ventaBruta DESC";
/***************************************** */
/*****************QUERY Nuevo ********************/
        $sql = "SELECT idSucMicros, idMicros, major, idItemMicros, ventaNeta, ventaBruta, ventaNetaImp, descuento, (costo + costoMods + costoCMB) AS costo , (cantidad - cantidadCMB) AS cantidad FROM 
		(
            SELECT VTSC.*, clas.idMajor,mmg.major, ((VTSC.cantidad-COALESCE( CMB.cantidad,0)) * COALESCE(rp.costo,0)) costo, COALESCE( CMB.cantidad,0) cantidadCMB, COALESCE( CMB2.costo,0) costoCMB, COALESCE(MODS.costo,0) costoMods FROM 
            (
                SELECT vmpm.idsucursal AS id,vmpm.idSucMicros,vmpm.idMicros ,vmpm.idItemMicros, SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad
                FROM venta_mes_producto_micros vmpm 
                WHERE vmpm.idsucursal IN (" . $this->locationID . ") AND vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idsucursal, vmpm.idSucMicros,vmpm.idMicros, vmpm.idItemMicros HAVING SUM(vmpm.ventaNeta) > 0
            ) VTSC
            LEFT JOIN micros_producto_clasificacion clas ON VTSC.idMicros = clas.idMicros
            LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?)
            LEFT JOIN recetas_platillo_costo rp ON (rp.idMicros = VTSC.idMicros AND rp.anio = YEAR(?) AND rp.mes = MONTH(?))
            LEFT JOIN 
            (
                SELECT CM.idSucursal, CM.idItemMicros, SUM(cantidad) cantidad FROM vds_items_combo CM WHERE (CM.fecha BETWEEN ? AND ?) AND CM.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM.idSucursal, CM.idItemMicros
            )	CMB ON (CMB.idSucursal = VTSC.id AND CMB.idItemMicros = VTSC.idMicros)
            LEFT JOIN (
                SELECT CM2.idSucursal, CM2.idCombo, SUM(costo) costo FROM vds_costo_combo CM2 WHERE (CM2.fecha BETWEEN ? AND ?) AND CM2.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM2.idSucursal, CM2.idCombo
            ) CMB2
            ON (CMB2.idCombo = VTSC.idMicros AND CMB2.idSucursal = VTSC.id)
            LEFT JOIN (
                SELECT MD.idSucursal, MD.idItem , SUM(costo) costo FROM vds_costo_modificador MD WHERE (MD.fecha BETWEEN ? AND ? ) AND MD.idSucursal IN (" . $this->locationID . ")
                GROUP BY MD.idSucursal, MD.idItem
            ) MODS
            ON (MODS.idItem = VTSC.idMicros AND MODS.idSucursal = VTSC.id)
        )
		AS VTAT ORDER BY idSucMicros , ventaNeta DESC;";

        $tmpDetailResult = DB::select($sql, [$this->initDate, $this->endDate, $this->company, $this->initDate,$this->initDate,$this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate]);
        
        $tmpGrossTotal = 0;
        $tmpNetTotal = 0;
        $tmpventaNetaImp = 0;
        $tmpdescuento = 0;
        $tmpcantidad = 0;
        $tmpcosto = 0;
        foreach ($tmpDetailResult as $row) {
            $tmpGrossTotal += $row->ventaBruta;
            $tmpNetTotal += $row->ventaNeta;
            $tmpventaNetaImp += $row->ventaNetaImp;
            $tmpdescuento += $row->descuento;
            $tmpcantidad += $row->cantidad;
            $tmpcosto += $row->costo;
        }

        foreach ($tmpDetailResult as $id => $row) {
            $tmpDetailResult[$id]->salesPercent = !empty($tmpGrossTotal) ? $row->ventaBruta/ $tmpGrossTotal : 0;
        }

        $tmpDetailResult[] = json_decode(json_encode(array("idSucMicros" =>"","idMicros" => "", "major" => "Total", "idItemMicros" => "", "ventaBruta" => $tmpGrossTotal, "ventaNeta" => $tmpNetTotal, "ventaNetaImp" => $tmpventaNetaImp, "descuento" => $tmpdescuento, "cantidad" => $tmpcantidad, "costo" => $tmpcosto, "margen" => 0, "salesPercent" => "")));

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

        $spreadsheet->getActiveSheet()->getStyle('A7:E7')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $currentSheet->setCellValue('A2', 'Report');
        $currentSheet->setCellValue('A3', 'Business Date');
        $currentSheet->setCellValue('A4', 'Location');
        $currentSheet->setCellValue('A5', 'Export Date');

        $currentSheet->setCellValue('B2', 'Menu Enginerring');
        $currentSheet->setCellValue('B3', $this->initDate . " - " . $this->endDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $currentSheet->setCellValue('A7', 'Major Grp');
        $currentSheet->setCellValue('B7', 'Gross Sales');
        $currentSheet->setCellValue('C7', 'Net Sales');
        $currentSheet->setCellValue('D7', 'Food Cost');
        $currentSheet->setCellValue('E7', 'Sales %');

        $row = 8;
        $initRowFormat=$row;
        foreach ($tmpResult as $data) {
            $currentSheet->setCellValue('A' . $row, $data->major);
            $currentSheet->setCellValue('B' . $row, $data->ventaNeta);
            $currentSheet->setCellValue('C' . $row, $data->ventaBruta);
            $currentSheet->setCellValue('D' . $row, $data->costo);
            $currentSheet->setCellValue('E' . $row, $data->salesPercent);
            $row++;
        }

        $endRowFormat = $row - 1;

        $spreadsheet->getActiveSheet()->getStyle('A8:A' . $endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A7:E' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $spreadsheet->getActiveSheet()->getStyle('B'.$initRowFormat.':D'.$endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $spreadsheet->getActiveSheet()->getStyle('E'.$initRowFormat.':E'.$endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $row += 2;
        $currentSheet->setCellValue('A' . $row, 'Sucursal');
        $currentSheet->setCellValue('B' . $row, 'Grp Mayor');
        $currentSheet->setCellValue('C' . $row, 'Articulo');
        $currentSheet->setCellValue('D' . $row, 'Cantidad');
        $currentSheet->setCellValue('E' . $row, 'Venta Bruta');
        $currentSheet->setCellValue('F' . $row, 'Venta Neta');
        $currentSheet->setCellValue('G' . $row, 'Descuento');
        $currentSheet->setCellValue('H' . $row, 'Costo');
        $currentSheet->setCellValue('I' . $row, 'Venta %');
        $currentSheet->setCellValue('J' . $row, 'C. Unitario');
        $currentSheet->setCellValue('K' . $row, 'Costo %');

        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $row++;
        $initRowFormat = $row;
        foreach ($tmpDetailResult as $data) {
            $currentSheet->setCellValue('A' . $row, $data->idSucMicros);
            $currentSheet->setCellValue('B' . $row, $data->major);
            $currentSheet->setCellValue('C' . $row, $data->idItemMicros);
            $currentSheet->setCellValue('D' . $row, $data->cantidad);
            $currentSheet->setCellValue('E' . $row, $data->ventaNeta);
            $currentSheet->setCellValue('F' . $row, $data->ventaBruta);
            $currentSheet->setCellValue('G' . $row, $data->descuento);
            $currentSheet->setCellValue('H' . $row, $data->costo);
            $currentSheet->setCellValue('I' . $row, $data->salesPercent);
            $currentSheet->setCellValue('J' . $row, (!empty($data->cantidad)?$data->costo/$data->cantidad:0));
            $currentSheet->setCellValue('K' . $row, (!empty($data->ventaNeta) ?$data->costo/($data->ventaNeta/$impuesto):0));
            $row++;
        }
        $endRowFormat = $row - 1;
        $spreadsheet->getActiveSheet()->getStyle('A' . $initRowFormat . ':C' . $endRowFormat)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A' . $initRowFormat . ':K' . $endRowFormat)->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        $spreadsheet->getActiveSheet()->getStyle('E'.$initRowFormat.':J'.$endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $spreadsheet->getActiveSheet()->getStyle('K'.$initRowFormat.':K'.$endRowFormat)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="MenuEng_' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");
    }
}