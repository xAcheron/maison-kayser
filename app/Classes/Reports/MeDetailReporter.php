<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use \Exception;

class MeDetailReporter implements iReporter
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
        $this->location = $params["location"];

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


        $this->major = $params["major"];
        $this->perSales = empty($params["perSales"]) ? 100 : $params["perSales"];
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
            $locationIDArr[] = "'" . $location->id . "'";
        }
        return array(implode(",", $locationArr), implode(",", $locationIDArr));
    }

    public function runReport()
    {
        
        if (empty($this->major)) {
            $majorIs = "IS NULL";
        } else {
            $majorIs = "= ?";
        }

        $sql = "SELECT idMicros, major, idItemMicros, ventaNeta, ventaBruta, ventaNetaImp, descuento, (costo + costoMods + costoCMB) AS costo , (cantidad - cantidadCMB) AS cantidad FROM 
		(
            SELECT VTSC.*, clas.idMajor,mmg.major, ((VTSC.cantidad-COALESCE( CMB.cantidad,0)) * COALESCE(rp.costo,0)) costo, COALESCE( CMB.cantidad,0) cantidadCMB, COALESCE( CMB2.costo,0) costoCMB, COALESCE(MODS.costo,0) costoMods FROM 
            (
                SELECT vmpm.idMicros ,vmpm.idItemMicros, SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad
                FROM venta_mes_producto_micros vmpm 
                WHERE vmpm.idsucursal IN (" . $this->locationID . ") AND vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idMicros, vmpm.idItemMicros HAVING SUM(vmpm.ventaNeta) > 0
            ) VTSC
            LEFT JOIN micros_producto_clasificacion clas ON VTSC.idMicros = clas.idMicros
            LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?)
            LEFT JOIN recetas_platillo_costo rp ON (rp.idMicros = VTSC.idMicros AND rp.anio = YEAR(?) AND rp.mes = MONTH(?))
            LEFT JOIN 
            (
                SELECT CM.idItemMicros, SUM(cantidad) cantidad FROM vds_items_combo CM WHERE (CM.fecha BETWEEN ? AND ?) AND CM.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM.idItemMicros
            )	CMB ON (CMB.idItemMicros = VTSC.idMicros)
            LEFT JOIN (
                SELECT CM2.idCombo, SUM(costo) costo FROM vds_costo_combo CM2 WHERE (CM2.fecha BETWEEN ? AND ?) AND CM2.idSucursal IN (" . $this->locationID . ")
                GROUP BY CM2.idCombo
            ) CMB2
            ON (CMB2.idCombo = VTSC.idMicros)
            LEFT JOIN (
                SELECT MD.idItem , SUM(costo) costo FROM vds_costo_modificador MD WHERE (MD.fecha BETWEEN ? AND ? ) AND MD.idSucursal IN (" . $this->locationID . ")
                GROUP BY MD.idItem
            ) MODS
            ON (MODS.idItem = VTSC.idMicros)
            WHERE mmg.idMajor $majorIs 
        )
		AS VTAT ORDER BY ventaNeta DESC;";
/*
        if ($this->major != 107) {
            $sql = "SELECT
                    idMicros, MAX(idItemMicros) idItemMicros , SUM(ventaBruta) ventaBruta, SUM(ventaNeta) ventaNeta, SUM(ventaNetaImp)ventaNetaImp, SUM(descuento)descuento, SUM(cantidad) cantidad , SUM(costo) costo, SUM(SCMB) SCMB , SUM(ventaBruta) - SUM(costo) Margen , 0 salesPercent
                FROM 
                (
                    SELECT VTSC.idMicros ,VTSC.idItemMicros , VTSC.ventaBruta, VTSC.ventaNeta, VTSC.ventaNetaImp, VTSC.descuento, VTSC.cantidad,  VTSC.cantidad-COALESCE(CM.cantidad,0) AS SCMB ,((VTSC.cantidad-COALESCE(CM.cantidad,0)) * VTSC.costo) AS costo FROM 
	                    (SELECT vmpm.idMicros ,vmpm.idItemMicros , SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad, MAX(rp.costo) AS costo
                        FROM 
                            venta_mes_producto_micros vmpm 
                        LEFT JOIN 
                            micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros 
                        LEFT JOIN 
                            micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                        LEFT JOIN 
                        recetas_platillo rp ON rp.idMicros = vmpm.idMicros
                        WHERE mmg.idMajor $majorIs AND vmpm.idSucMicros IN (" . $this->location . ") AND vmpm.fecha BETWEEN ? AND ?  GROUP BY vmpm.idMicros, vmpm.idItemMicros HAVING SUM(vmpm.ventaNeta) > 0
                        ) VTSC
                        LEFT JOIN (SELECT CMB.itemNumber, SUM(CMB.count) cantidad FROM vds_combo CMB WHERE CMB.idSucursal IN (" . $this->locationID . ") AND CMB.fecha BETWEEN ? AND ?  GROUP BY CMB.itemNumber) CM ON CM.itemNumber = VTSC.idMicros
                    UNION ALL
                        SELECT 
                        VM.idItem AS idMicros, '' idItemMicros , SUM(VM.netSales) ventaBruta,SUM(VM.netSales) ventaNeta, SUM(VM.netSales)ventaNetaImp, 0 descuento, 0 cantidad, 0 SCMB, SUM(VM.count * rp.costo) costo
                        FROM vds_modificador VM 
                        LEFT JOIN 
                            micros_producto_clasificacion clas ON VM.idItem = clas.idMicros 
                        LEFT JOIN 
                            micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) 
                        LEFT JOIN recetas_platillo rp ON rp.idMicros = VM.itemNumber 
                        WHERE 
                        mmg.idMajor $majorIs AND VM.idSucursal IN (" . $this->locationID . ") AND VM.fecha BETWEEN ? AND ? 
                        GROUP BY VM.idItem			
                ) AS VT GROUP BY idMicros ORDER BY ventaBruta DESC";
            if ($this->major == null) {
                $sqlParams = [$this->company, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->company, $this->initDate, $this->endDate];
            } else {
                $sqlParams = [$this->company, $this->major, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->company, $this->major, $this->initDate, $this->endDate];
            }
        } else {
            $sql = "SELECT VT.*, (VT.cantidad) AS SCMB,COALESCE(CM.costo,0) AS costo, VT.ventaBruta - COALESCE(CM.costo,0) Margen, 0 salesPercent FROM (SELECT vmpm.idMicros ,vmpm.idItemMicros , SUM(vmpm.ventaBruta) ventaBruta, SUM(vmpm.ventaNeta) ventaNeta, SUM(vmpm.ventaNetaImp)ventaNetaImp, SUM(vmpm.descuento)descuento, SUM(vmpm.cantidad) cantidad FROM venta_mes_producto_micros vmpm LEFT JOIN micros_producto_clasificacion clas ON vmpm.idMicros = clas.idMicros LEFT JOIN micros_major_group mmg ON (mmg.idMajor = clas.idMajor AND mmg.idEmpresa = ?) WHERE mmg.idMajor = ? AND vmpm.idSucMicros IN (" . $this->location . ") AND vmpm.fecha BETWEEN ? AND ? GROUP BY vmpm.idMicros, vmpm.idItemMicros HAVING SUM(vmpm.ventaNeta) >0 ORDER BY ventaBruta DESC, cantidad DESC) AS VT 
            LEFT JOIN (
                SELECT A.idCombo, SUM(A.count * B.costo) costo FROM vds_combo A INNER JOIN recetas_platillo B ON A.itemNumber = B.idMicros INNER JOIN sucursales S ON S.id = A.idSucursal WHERE S.id IN (" . $this->locationID . ") AND NOT(A.idCombo = A.itemNumber) AND A.fecha BETWEEN ? AND ? GROUP BY A.idCombo
            ) AS CM ON CM.idCombo = VT.idMicros LEFT JOIN recetas_platillo rp ON rp.idMicros = VT.idMicros ORDER BY VT.ventaBruta DESC;";
            $sqlParams = [$this->company, $this->major, $this->initDate, $this->endDate, $this->initDate, $this->endDate];
        }
*/
        if ($this->major == null) {
            $sqlParams = [$this->initDate, $this->endDate, $this->company, $this->initDate,$this->initDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate];
        } else {
            $sqlParams = [$this->initDate, $this->endDate, $this->company, $this->initDate,$this->initDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->initDate, $this->endDate, $this->major];
        }

        try {
            $this->result = DB::select($sql, $sqlParams);
        } catch (Exception $e) {
            echo $this->initDate . '<br>';
            echo $this->endDate . '<br>';
            echo $this->location . '<br>';
            echo $this->locationID . '<br>';
            echo $this->company . '<br>';
            echo 'Excepción capturada: ',  $e->getMessage(), '<br>';
            exit(0);
        }

        $tmpGrossTotal = 0;
        $tmpNetTotal = 0;
        $tmpventaNetaImp = 0;
        $tmpdescuento = 0;
        $tmpcantidad = 0;
        $tmpcosto = 0;
        $tmpsalesPercent = $this->perSales;
        foreach ($this->result as $row) {
            $tmpGrossTotal += $row->ventaBruta;
            $tmpNetTotal += $row->ventaNeta;
            $tmpventaNetaImp += $row->ventaNetaImp;
            $tmpdescuento += $row->descuento;
            $tmpcantidad += $row->cantidad;
            $tmpcosto += $row->costo;
        }

        foreach ($this->result as $id => $row) {
            $this->result[$id]->salesPercent = !empty($tmpGrossTotal) ? $row->ventaBruta * $this->perSales / $tmpGrossTotal : 0;
        }

        $this->result[] = json_decode(json_encode(array("idMicros" => 0, "idItemMicros" => "Total", "ventaBruta" => $tmpGrossTotal, "ventaNeta" => $tmpNetTotal, "ventaNetaImp" => $tmpventaNetaImp, "descuento" => $tmpdescuento, "cantidad" => $tmpcantidad, "costo" => $tmpcosto, "margen" => 0, "salesPercent" => $tmpsalesPercent)));
    }

    public function getResult($type)
    {
        $parser = new ReportParser($type);
        return $parser->parse($this->result);
    }
}