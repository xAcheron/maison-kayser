<?php

namespace App\Classes\Reports;

use App\Classes\GoogleCharts\GoogChart;
use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade as PDF;

class MonthlyExecReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $initLYDate;
    private $endLYDate;
    private $initLMDate;
    private $endLMDate;
    private $location;
    private $locationID;
    private $result;
    private $company;
    private $months = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Sep", "Oct", "Nov", "Dic"];

    public function setParams($params)
    {

        if (empty($params['daterange']) || $params['daterange'] == "All") {
            $this->initDate = date("Y-m-01", strtotime(date("Y-m-d") . "-1 MONTH"));
            $this->endDate = date("Y-m-t", strtotime(date("Y-m-d") . "-1 MONTH"));
        } else {
            if (strlen($params['daterange']) > 10) {
                $array = explode('-', $params['daterange']);
                $array = array_slice($array, 0, 3);
                $params['daterange'] = implode('-', $array);
            }

            $this->initDate = date("Y-m-01", strtotime($params['daterange']));
            $this->endDate = date("Y-m-t", strtotime($params['daterange']));
        }

        $location = new UserLocation();
        $location->get($params["location"], 'E', $params['idUsuario'] ?? '');
        $this->location = $location->locationName;
        $this->locationID = $location->locationID;
        $this->company = $location->company;

        $this->initLYDate = date("Y-m-01", strtotime($this->initDate . " -1 YEAR"));
        $this->endLYDate = date("Y-m-t", strtotime($this->initDate . " -1 YEAR"));

        $this->initLMDate = date("Y-m-01", strtotime($this->initDate . " -1 MONTH"));
        $this->endLMDate = date("Y-m-t", strtotime($this->initDate . " -1 MONTH"));
    }

    public function runReport()
    {

        $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.netSales AS CurrentNetSales, anterior.netSales AS LYNetSales, actual.netSales/anterior.netSales AS LY, 
        actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
        anterior.Institucional AS InstitucionalLY, actual.Vitrina/anterior.Vitrina AS VLY, actual.Salon/anterior.Salon AS SLY, actual.Delivery/anterior.Delivery AS DLY, 
        actual.Institucional/anterior.Institucional AS ILY FROM 
        (SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa= ? AND s.estado =1 AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
        LEFT JOIN 
        (SELECT vds_rvc.idSucursal,MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
        ON actual.idSucursal = anterior.idSucursal ORDER BY actual.netSales desc;";
        $venta = DB::select($sql, [$this->company, $this->initDate, $this->endDate, $this->initLYDate, $this->endLYDate]);
        
        $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'Abiertas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc  INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) > ? AND MAX(fecha) >= ?) AS venta GROUP BY tipo;";
        $abiertas = DB::select($sql, [$this->company, $this->endLYDate, $this->initDate,]);

        //$sucursalesExcluidas = empty($abiertas[0]) ? null : $abiertas[0]->sucursales;
        //$sucursalesExcluidas = empty($cerradas[0]) ? $sucursalesExcluidas : ((empty($sucursalesExcluidas) ? "" : $sucursalesExcluidas . (empty($cerradas) ? "" : ",")) . $cerradas[0]->sucursales);
        $abiertasArr = array();

        if (!empty($abiertas[0]))
            $abiertasArr = explode(",", $abiertas[0]->sucursales);

        $CurrentNetSales = 0;
        $LYNetSales = 0;
        $Vitrina  = 0;
        $VitrinaLY = 0;
        $Salon  = 0;
        $SalonLY  = 0;
        $Delivery  = 0;
        $DeliveryLY  = 0;
        $Institucional  = 0;
        $InstitucionalLY  = 0;
        $BudgetTotal = 0;

        $CurrentNetSalesMT = 0;
        $LYNetSalesMT = 0;
        $VitrinaMT = 0;
        $VitrinaLYMT = 0;
        $SalonMT = 0;
        $SalonLYMT = 0;
        $DeliveryMT = 0;
        $DeliveryLYMT = 0;
        $InstitucionalMT = 0;
        $InstitucionalLYMT = 0;
        $BudgetTotalMT = 0;
        $CurrentNetSalesOT =0;
        $LYNetSalesOT =0;
        $VitrinaOT =0;
        $VitrinaLYOT =0;
        $SalonOT =0;
        $SalonLYOT =0;
        $DeliveryOT =0;
        $DeliveryLYOT =0;
        $InstitucionalOT =0;
        $InstitucionalLYOT =0;
        $BudgetTotalOT =0;

        foreach ($venta as $monto) 
        {
            $CurrentNetSales += $monto->CurrentNetSales;
            $LYNetSales += $monto->LYNetSales;
            $Vitrina += $monto->Vitrina;
            $VitrinaLY += $monto->VitrinaLY;
            $Salon += $monto->Salon;
            $SalonLY += $monto->SalonLY;
            $Delivery += $monto->Delivery;
            $DeliveryLY += $monto->DeliveryLY;
            $Institucional += $monto->Institucional;
            $InstitucionalLY += $monto->InstitucionalLY;
            $BudgetTotal += !empty($sucBudget[$monto->idSucursal]) ? $sucBudget[$monto->idSucursal] : 0;
            if (!in_array($monto->idSucursal, $abiertasArr)) 
            {
                $CurrentNetSalesMT += $monto->CurrentNetSales;
                $LYNetSalesMT += $monto->LYNetSales;
                $VitrinaMT += $monto->Vitrina;
                $VitrinaLYMT += $monto->VitrinaLY;
                $SalonMT += $monto->Salon;
                $SalonLYMT += $monto->SalonLY;
                $DeliveryMT += $monto->Delivery;
                $DeliveryLYMT += $monto->DeliveryLY;
                $InstitucionalMT += $monto->Institucional;
                $InstitucionalLYMT += $monto->InstitucionalLY;
                $BudgetTotalMT += !empty($sucBudget[$monto->idSucursal]) ? $sucBudget[$monto->idSucursal] : 0;
            }
            else
            {
                $CurrentNetSalesOT += $monto->CurrentNetSales;
                $LYNetSalesOT += $monto->LYNetSales;
                $VitrinaOT += $monto->Vitrina;
                $VitrinaLYOT += $monto->VitrinaLY;
                $SalonOT += $monto->Salon;
                $SalonLYOT += $monto->SalonLY;
                $DeliveryOT += $monto->Delivery;
                $DeliveryLYOT += $monto->DeliveryLY;
                $InstitucionalOT += $monto->Institucional;
                $InstitucionalLYOT += $monto->InstitucionalLY;
                $BudgetTotalOT += !empty($sucBudget[$monto->idSucursal]) ? $sucBudget[$monto->idSucursal] : 0;
            }
        }

        

        $sql = "SELECT idSucursal, SUM(budget) monto FROM budget_dia_sucursal_o A INNER JOIN sucursales S ON A.idSucursal = S.id WHERE S.idEmpresa =? AND A.fecha BETWEEN ? AND ? GROUP BY idSucursal;";
        $budget = DB::select($sql, [$this->company, $this->initDate, $this->endDate]);
        $sucBudget = array();
        $totBudget=0;
        $totBudgetMT = 0;
        $totBudgetOT = 0;
        foreach ($budget as $b) {
            $sucBudget[$b->idSucursal] = $this->company == 1 || $this->company == 2 ? $b->monto * 1.16 : $b->monto;
            $totBudget += ( $this->company == 1 || $this->company == 2 ? $b->monto * 1.16 : $b->monto );
            if (!in_array($b->idSucursal, $abiertasArr)) 
            {
                $totBudgetMT += ( $this->company == 1 || $this->company == 2 ? $b->monto * 1.16 : $b->monto );
            }
            else
            {
                $totBudgetOT += ( $this->company == 1 || $this->company == 2 ? $b->monto * 1.16 : $b->monto );
            }
        }

        $sql = "SELECT G.idSucursal, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G INNER JOIN sucursales S ON G.idSucursal = S.id WHERE S.idEmpresa =? AND fecha BETWEEN ? AND ? GROUP BY G.idSucursal";
        $guests = DB::select($sql, [$this->company, $this->initDate, $this->endDate]);
        
        $sucGuests = array();
        $totGuests=0;
        $totGuestsMT=0;
        $totGuestsOT=0;
        foreach ($guests as $b) {
            $sucGuests[$b->idSucursal][0] = $b->guests;
            $sucGuests[$b->idSucursal][1] = $b->AvgCheck;
            $sucGuests[$b->idSucursal][2] = $b->netSales;
            $sucGuests[$b->idSucursal][3] = 0;
            $sucGuests[$b->idSucursal][4] = 0;
            $totGuests += $b->guests;
            if (!in_array($b->idSucursal, $abiertasArr)) 
            {
                $totGuestsMT += $b->guests;
            }
            else
            {
                $totGuestsOT += $b->guests;
            }
        }

        $sql = "SELECT G.idSucursal, SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G INNER JOIN sucursales S ON G.idSucursal = S.id WHERE S.idEmpresa =? AND fecha BETWEEN ? AND ? GROUP BY G.idSucursal";
        $guests = DB::select($sql, [$this->company, $this->initLYDate, $this->endLYDate]);
        
        $totGuestsLY=0;
        $totGuestsLYMT=0;
        $totGuestsLYOT=0;
        foreach ($guests as $b) {
            $sucGuests[$b->idSucursal][3] = $b->guests;
            $sucGuests[$b->idSucursal][4] = $b->AvgCheck;
            $totGuestsLY += $b->guests;
            if (!in_array($b->idSucursal, $abiertasArr)) 
            {
                $totGuestsLYMT += $b->guests;
            }
            else
            {
                $totGuestsLYOT += $b->guests;
            }
        }

        $totalesVenta[0] = array("CurrentNetSales" => $CurrentNetSales, "LYNetSales" => $LYNetSales, "Budget" => $totBudget, "Visitas" => $totGuests, "VisitasLY"=> $totGuestsLY, "Cheque" => (empty($totGuests)?0: $CurrentNetSales / $totGuests));
        $totalesVenta[1] = array("CurrentNetSales" => $CurrentNetSalesMT, "LYNetSales" => $LYNetSalesMT, "Budget" => $totBudgetMT, "Visitas" => $totGuestsMT, "VisitasLY"=> $totGuestsLYMT, "Cheque" => (empty($totGuestsMT)?0: $CurrentNetSalesMT / $totGuestsMT));
        $totalesVenta[2] = array("CurrentNetSales" => $CurrentNetSalesOT, "LYNetSales" => $LYNetSalesOT, "Budget" => $totBudgetOT, "Visitas" => $totGuestsOT, "VisitasLY"=> $totGuestsLYOT, "Cheque" => (empty($totGuestsMT)?0: $CurrentNetSalesOT / $totGuestsOT));

        $sql = "SELECT * FROM (SELECT S.id AS idSucursal, SUM(W.netSales) netSales,
        SUM(W.cogs) cogs, SUM(W.cogs)/ SUM(W.netSales) pcogs, SUM(W.bcogs) bcogs, SUM(W.bcogs)/ SUM(W.bnetSales) pbcogs, SUM(W.cogs)/ SUM(W.bcogs) pmcogs,
        SUM(W.gmargin)gmargin, SUM(W.gmargin)/SUM(W.netSales) pgmargin, SUM(W.bgmargin)bgmargin, SUM(W.bgmargin)/SUM(W.bnetSales) pbgmargin, 
        SUM(W.opsexpenses)opsexpenses, SUM(W.opsexpenses)/SUM(W.netSales) popsexpenses, SUM(W.bopsexpenses)bopsexpenses, SUM(W.bopsexpenses)/SUM(W.bnetSales) pbopsexpenses, 
        SUM(W.labor) labor,SUM(W.labor)/ SUM(W.netSales) plabor, SUM(W.blabor)blabor, SUM(W.blabor)/ SUM(W.bnetSales) pblabor, SUM(W.labor)/ SUM(W.blabor) pmlabor, 
        SUM(W.ebitda) ebitda,SUM(W.ebitda)/ SUM(W.netSales) pebitda, SUM(W.bebitda)bebitda, SUM(W.bebitda)/ SUM(W.bnetSales) pbebitda, SUM(W.ebitda)/ SUM(W.bebitda) pmebitda,
        SUM(W.giotto) giotto,SUM(W.giotto)/ SUM(W.netSales) pgiotto, SUM(W.bgiotto)bgiotto, SUM(W.bgiotto)/ SUM(W.bnetSales) pbgiotto, SUM(W.giotto)/ SUM(W.bgiotto) pmgiotto
        FROM finanzas_pl_mes W
        INNER JOIN sucursales S ON S.id = W.idSucursal
        WHERE ( W.fecha BETWEEN ? AND ?  ) AND S.idEmpresa = ? AND S.estado= 1 GROUP BY S.id ) mes
        LEFT JOIN 
        (SELECT S.id AS aid,SUM(W.netSales) anetSales,
        SUM(W.cogs) acogs, SUM(W.cogs)/ SUM(W.netSales) apcogs, SUM(W.bcogs) abcogs, SUM(W.bcogs)/ SUM(W.bnetSales) apbcogs, SUM(W.cogs)/ SUM(W.bcogs) apmcogs,
        SUM(W.gmargin)agmargin, SUM(W.gmargin)/SUM(W.netSales) apgmargin, SUM(W.bgmargin)abgmargin, SUM(W.bgmargin)/SUM(W.bnetSales) apbgmargin, 
        SUM(W.opsexpenses)aopsexpenses, SUM(W.opsexpenses)/SUM(W.netSales) apopsexpenses, SUM(W.bopsexpenses) abopsexpenses, SUM(W.bopsexpenses)/SUM(W.bnetSales) apbopsexpenses, 
        SUM(W.labor) alabor,SUM(W.labor)/ SUM(W.netSales) aplabor, SUM(W.blabor)ablabor, SUM(W.blabor)/ SUM(W.bnetSales) apblabor, SUM(W.labor)/ SUM(W.blabor) apmlabor, 
        SUM(W.ebitda) aebitda,SUM(W.ebitda)/ SUM(W.netSales) apebitda, SUM(W.bebitda)abebitda, SUM(W.bebitda)/ SUM(W.bnetSales) apbebitda, SUM(W.ebitda)/ SUM(W.bebitda) apmebitda,
        SUM(W.giotto) agiotto,SUM(W.giotto)/ SUM(W.netSales) apgiotto, SUM(W.bgiotto)abgiotto, SUM(W.bgiotto)/ SUM(W.bnetSales) apbgiotto, SUM(W.giotto)/ SUM(W.bgiotto) apmgiotto
        FROM finanzas_pl_mes W
        INNER JOIN sucursales S ON S.id = W.idSucursal
        WHERE (W.fecha BETWEEN ? AND ? ) AND S.idEmpresa = ? AND S.estado= 1 GROUP BY S.id ) acumulado
        ON mes.idSucursal = acumulado.aid";
        $pals = DB::select($sql, [ $this->initDate, $this->endDate,$this->company,$this->initLMDate, $this->endLMDate,$this->company]);        
        $sucPal = array();
        foreach ($pals as $b) {
            $sucPal[$b->idSucursal][0] = $b->netSales;
            $sucPal[$b->idSucursal][1] = $b->cogs;
            $sucPal[$b->idSucursal][2] = $b->labor;
            $sucPal[$b->idSucursal][3] = $b->opsexpenses-$b->labor-$b->giotto;
            $sucPal[$b->idSucursal][4] = $b->giotto;
            $sucPal[$b->idSucursal][5] = $b->ebitda;
            $sucPal[$b->idSucursal][6] = $b->pcogs;
            $sucPal[$b->idSucursal][7] = $b->popsexpenses;
            $sucPal[$b->idSucursal][8] = $b->pebitda;
            $sucPal[$b->idSucursal][9] = $b->apcogs;
            $sucPal[$b->idSucursal][10] = $b->apopsexpenses;
            $sucPal[$b->idSucursal][11] = $b->apebitda;
            $sucPal[$b->idSucursal][12] = $b->pcogs-$b->apcogs;
            $sucPal[$b->idSucursal][13] = $b->popsexpenses-$b->apopsexpenses;
            $sucPal[$b->idSucursal][14] = $b->pebitda-$b->apebitda;
        }

        $sql = "SELECT * FROM sucursales_rvc RVC WHERE idEmpresa = ? ORDER BY idRvc;";
        $rvcNames = DB::select($sql, [$this->company]);

        $sql =  "SELECT G.rvc, WEEK(fecha,3) fecha, AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND YEAR(fecha) = YEAR(?) GROUP BY rvc, WEEK(fecha,3) ORDER BY rvc, WEEK(fecha,3);";
        $weekSales = DB::select($sql, [$this->initDate]);

        $sql =  "SELECT G.rvc, WEEK(fecha,3) fecha, AVG(G.netSales) AvgSales ,SUM(G.netSales) netSales, SUM(G.guests) guests, SUM(G.netSales)/SUM(G.guests) AS AvgCheck FROM vds_guests G WHERE idSucursal IN (" . $this->locationID . ") AND YEAR(fecha) = YEAR(?)-1 GROUP BY rvc, WEEK(fecha,3) ORDER BY rvc, WEEK(fecha,3);";
        $weekSalesLY = DB::select($sql, [$this->initDate]);

        $weekLYRVC = array();

        foreach ($weekSalesLY as $weekone) {
            foreach ($rvcNames as $rvcname) {
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][0] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][1] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][2] = 0;
                $weekLYRVC[$weekone->fecha][$rvcname->idRvc][3] = 0;
            }
            $weekLYRVC[$weekone->fecha][0] = array(0, 0, 0);
        }

        foreach ($weekSalesLY as $weekone) {

            $weekLYRVC[$weekone->fecha][$weekone->rvc][0] = $weekone->netSales;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][1] = $weekone->guests;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][2] = $weekone->AvgCheck;
            $weekLYRVC[$weekone->fecha][$weekone->rvc][3] = $weekone->netSales;
            $weekLYRVC[$weekone->fecha][0][0] = (empty($weekLYRVC[$weekone->fecha][0][0]) ? 0 : $weekLYRVC[$weekone->fecha][0][0]) + $weekone->netSales;
            $weekLYRVC[$weekone->fecha][0][1] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][1]) + $weekone->guests;
            $weekLYRVC[$weekone->fecha][0][2] = (empty($weekLYRVC[$weekone->fecha][0][2]) ? $weekone->AvgCheck : ($weekLYRVC[$weekone->fecha][0][2] + $weekone->AvgCheck) / (empty($weekone->AvgCheck) ? 1 : 2));
        }

        $weekRVC = array();

        foreach ($weekSales as $weekone) {
            foreach ($rvcNames as $rvcname) {
                $weekRVC[$weekone->fecha][$rvcname->idRvc][0] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][1] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][2] = array("actual" => 0, "anterior" => 0);
                $weekRVC[$weekone->fecha][$rvcname->idRvc][3] = 0;
            }
            if ($weekone->fecha < 52 || date("W", strtotime($this->endDate)) >= 52)
                $weekRVC[$weekone->fecha][0] = array("actual" => array(0, 0, 0), "anterior" => array(0, 0, 0), "2anios" => array(0, 0, 0));
        }
        foreach ($weekSales as $weekone) {
            $weekRVC[$weekone->fecha][$weekone->rvc][3] = empty($weekLYRVC[$weekone->fecha][$weekone->rvc][0]) ? 0 : $weekone->netSales / $weekLYRVC[$weekone->fecha][$weekone->rvc][0];
            if ($weekone->fecha < 52 || date("W", strtotime($this->endDate)) >= 52) {
                $weekRVC[$weekone->fecha][0]["actual"][0] = (empty($weekRVC[$weekone->fecha][0]["actual"][0]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][0]) + $weekone->netSales;
                $weekRVC[$weekone->fecha][0]["anterior"][0] = (empty($weekLYRVC[$weekone->fecha][0][0]) ? 0 : $weekLYRVC[$weekone->fecha][0][0]);
                $weekRVC[$weekone->fecha][0]["tanios"][0] = (empty($weekTYRVC[$weekone->fecha][0][0]) ? 0 : $weekTYRVC[$weekone->fecha][0][0]);

                $weekRVC[$weekone->fecha][0]["actual"][1] = (empty($weekRVC[$weekone->fecha][0]["actual"][1]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][1]) + $weekone->guests;
                $weekRVC[$weekone->fecha][0]["anterior"][1] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][1]);
                $weekRVC[$weekone->fecha][0]["tanios"][1] = (empty($weekTYRVC[$weekone->fecha][0][1]) ? 0 : $weekTYRVC[$weekone->fecha][0][1]);

                $weekRVC[$weekone->fecha][0]["actual"][2] = empty($weekRVC[$weekone->fecha][0]["actual"][1]) ? 0 : $weekRVC[$weekone->fecha][0]["actual"][0] / $weekRVC[$weekone->fecha][0]["actual"][1];
                $weekRVC[$weekone->fecha][0]["anterior"][2] = (empty($weekLYRVC[$weekone->fecha][0][1]) ? 0 : $weekLYRVC[$weekone->fecha][0][0] / $weekLYRVC[$weekone->fecha][0][1]);
                $weekRVC[$weekone->fecha][0]["tanios"][2] = (empty($weekTYRVC[$weekone->fecha][0][1]) ? 0 : $weekTYRVC[$weekone->fecha][0][0] / $weekTYRVC[$weekone->fecha][0][1]);
            }

            $weekRVC[$weekone->fecha][-1] = 0;
            $weekRVC[$weekone->fecha][-2] = empty($weekLYRVC[$weekone->fecha][0]) ? 0 : $weekLYRVC[$weekone->fecha][0];
            $weekRVC[$weekone->fecha][$weekone->rvc][0] = array("actual" => $weekone->netSales, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][0]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][0]);
            $weekRVC[$weekone->fecha][$weekone->rvc][1] = array("actual" => $weekone->guests, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][1]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][1]);
            $weekRVC[$weekone->fecha][$weekone->rvc][2] = array("actual" => $weekone->AvgCheck, "anterior" => empty($weekLYRVC[$weekone->fecha][$weekone->rvc][2]) ? 0 : $weekLYRVC[$weekone->fecha][$weekone->rvc][2]);
        }

        foreach ($weekRVC as $fecha => $wdata) {
            if ($fecha < 52 || date("W", strtotime($this->endDate)) >= 52)
                $weekRVC[$fecha][-1] = empty($weekLYRVC[$fecha][0]) ? 0 : $weekRVC[$fecha][0]["actual"][0] / $weekLYRVC[$fecha][0][0];
        }

        $sql = "SELECT gen.*, det.*, sup.idUsuario, users.name AS distrital FROM 
        (
            SELECT id, nombre, SUM(IF(EST.Tipo = 0, EST.evaluacion, 0)) Actual, SUM(IF(EST.Tipo = 1, EST.evaluacion, 0)) Anterior, SUM(IF(EST.Tipo = 2, EST.evaluacion, 0)) Anual FROM 
            (
                SELECT 0 AS Tipo, S.id, S.nombre, AVG(Z.puntajeFinal/Z.puntajeMaximo)*100 evaluacion FROM checklist_info AS WW INNER JOIN checklist_generados Z ON WW.id = Z.idTipoCheck INNER JOIN sucursales S ON S.id = Z.idSuc WHERE Z.idSuc IN (" . $this->locationID . ") AND WW.idEvaluacion =1 AND MONTH(Z.fechaGenerada)=? AND YEAR(Z.fechaGenerada)=? GROUP BY S.id, S.nombre
                UNION ALL
                SELECT 1 AS Tipo, S.id, S.nombre, AVG(Z.puntajeFinal/Z.puntajeMaximo)*100 evaluacion FROM checklist_info AS WW INNER JOIN checklist_generados Z ON WW.id = Z.idTipoCheck INNER JOIN sucursales S ON S.id = Z.idSuc WHERE Z.idSuc IN (" . $this->locationID . ") AND WW.idEvaluacion =1 AND MONTH(Z.fechaGenerada)=? AND YEAR(Z.fechaGenerada)=? GROUP BY S.id, S.nombre		
                UNION ALL
                SELECT 2 AS Tipo, S.id, S.nombre, AVG(Z.puntajeFinal/Z.puntajeMaximo)*100 evaluacion FROM checklist_info AS WW INNER JOIN checklist_generados Z ON WW.id = Z.idTipoCheck INNER JOIN sucursales S ON S.id = Z.idSuc WHERE Z.idSuc IN (" . $this->locationID . ") AND WW.idEvaluacion =1 AND YEAR(Z.fechaGenerada)=? GROUP BY S.id, S.nombre		
            ) AS EST GROUP BY id, nombre
        ) gen
        INNER JOIN 
        (SELECT Z.idSuc,SUM(IF(SB.idSub= 'VISUPVITRINA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPVITRINA',1,0)),1) clVit, (SUM(IF(SB.idSub= 'VISUPVITRINA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPVITRINA',SB.valorEvaluacion,0)))/100 punVit,SUM(IF(SB.idSub= 'VISUPCOCINA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPCOCINA',1,0)),1) clCoc, (SUM(IF(SB.idSub= 'VISUPCOCINA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPCOCINA',SB.valorEvaluacion,0)))/100 punCoc,SUM(IF(SB.idSub= 'VISUPPAN',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPPAN',1,0)),1) clPan, (SUM(IF(SB.idSub= 'VISUPPAN',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPPAN',SB.valorEvaluacion,0)))/100 punPan,SUM(IF(SB.idSub= 'VISUPBARRA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPBARRA',1,0)),1) clBar, (SUM(IF(SB.idSub= 'VISUPBARRA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPBARRA',SB.valorEvaluacion,0)))/100 punBar,SUM(IF(SB.idSub= 'VISUPSALON',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPSALON',1,0)),1) clSal, (SUM(IF(SB.idSub= 'VISUPSALON',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPSALON',SB.valorEvaluacion,0)))/100 punSal,SUM(IF(SB.idSub= 'VISUPMKT',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPMKT',1,0)),1) clMkt, (SUM(IF(SB.idSub= 'VISUPMKT',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPMKT',SB.valorEvaluacion,0)))/100 punMkt,SUM(IF(SB.idSub= 'VISUPLEGAL',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPLEGAL',1,0)),1) clLeg, (SUM(IF(SB.idSub= 'VISUPLEGAL',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPLEGAL',SB.valorEvaluacion,0)))/100 punLeg,SUM(IF(SB.idSub= 'VISUPTEMP',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPTEMP',1,0)),1) clProds, (SUM(IF(SB.idSub= 'VISUPTEMP',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPTEMP',SB.valorEvaluacion,0)))/100 punProds,SUM(IF(SB.idSub= 'VISUPAPP',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPAPP',1,0)),1) clApp, (SUM(IF(SB.idSub= 'VISUPAPP',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPAPP',SB.valorEvaluacion,0)))/100 punApp FROM checklist_sub_info SB INNER JOIN checklist_generados_sub Y ON SB.idSubSeccion = Y.idSub INNER JOIN checklist_sub_info W ON Y.idSub = W.idSubseccion INNER JOIN checklist_generados Z ON Z.idCheckList = Y.idCheckList INNER JOIN checklist_info AS WW ON WW.id = Z.idTipoCheck WHERE WW.idEvaluacion =1 AND Z.idSuc IN (" . $this->locationID . ") AND MONTH(Z.fechaGenerada)=? AND YEAR(Z.fechaGenerada)=? GROUP BY Z.idSuc) det ON gen.id = det.idSuc 
        LEFT JOIN supervisor_sucursal sup ON sup.idSucursal = gen.id LEFT JOIN users ON users.id = sup.idUsuario ORDER BY gen.Actual";
        $mes = date("m", strtotime($this->initDate));
        $anio = date("Y", strtotime($this->initDate));
        $evaluaciones = DB::select($sql, [$mes, $anio, $mes - 1, $anio, $anio, $mes, $anio]);

        $this->result = array("venta" => $venta, "guests" => $sucGuests, "budget" => $sucBudget, "pal" => $sucPal, "week" => $weekRVC, "rvcnames" =>$rvcNames, "evaluacion" => $evaluaciones, "totalesVenta" => $totalesVenta);

    }

    public function exportReport()
    {

        //dd($this->result["evaluacion"]);
        $html ="<style> .right{ text-align: right; } 
        .rowborder { border-bottom: 1px solid #000; }
        .rowbordertop { border-top: 1px solid #000; }
        .redWords { color: #f00}
        .blackWords { color: #000}
        .greenWords { color: #197513}
        .yellowWords { color: #F1C40F}
        .page-break {
            page-break-after: always;
        }
        .tcenter { text-align: center; padding-left: 5px;padding-right: 5px}
        td {
            font-size:10px;
        }
        @page {
            margin: 10px 25px;
            }
        </style>";
        // define("DOMPDF_ENABLE_REMOTE", false);
        //$html .= '<H2>Reporte del mes '.(date("m Y", strtotime($this->initDate))).'</H2>';
        $html .= '<div style="width: 100%;height: 20px;margin-bottom:30px;padding: 0px;" ><div style="float:left;"><H2>COMPARATIVO VENTA AÑO ANTERIOR ,MES '.(date("m Y", strtotime($this->initDate))).'</H2></div><div style="float:right; padding: 20px;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/mk_logo_nuevo_200_11.png" /></div></div>';
        $html .="<table cellspacing='0px'>";
        $html .= "<tr><td class='rowborder tcenter'>Sucursal</td><td class='rowborder tcenter'>Ventas</td><td class='rowborder tcenter'>Año Anterior</td><td class='rowborder tcenter'>Budget</td><td width='10px'> </td><td class='rowborder tcenter'>Visitas</td><td class='rowborder tcenter'>Año Anterior</td><td width='10px'> </td><td class='rowborder tcenter'>Cheque</td><td class='rowborder tcenter'>Año Anterior</td></tr>";
        foreach($this->result["venta"] as $v)
        {
            $budget = (empty($this->result["budget"][$v->idSucursal])?"--":number_format($v->CurrentNetSales/$this->result["budget"][$v->idSucursal]*100,0,"",""));
            $LY = (empty($v->LY)?"--":number_format($v->LY*100,0,"",""));
            $venta = (empty($v->CurrentNetSales)?"--":number_format($v->CurrentNetSales,0,"",","));
            $visitas= empty($this->result["guests"][$v->idSucursal])?"--":number_format($this->result["guests"][$v->idSucursal][0],0,"",",");
            $visitasLY= empty($this->result["guests"][$v->idSucursal])?"--": ( empty($this->result["guests"][$v->idSucursal][3])?"--":number_format($this->result["guests"][$v->idSucursal][0]/$this->result["guests"][$v->idSucursal][3],2,".",","));
            $cheque= empty($this->result["guests"][$v->idSucursal])?"--":number_format($this->result["guests"][$v->idSucursal][1],0,"",",");
            $chequeLY= empty($this->result["guests"][$v->idSucursal])?"--": ( empty($this->result["guests"][$v->idSucursal][4])?"--":number_format($this->result["guests"][$v->idSucursal][1]/$this->result["guests"][$v->idSucursal][4],2,".",","));
            $html.="<tr><td>".$v->sucursal."</td><td class='right'>".$venta."</td><td class='right ".($LY>100?"greenWords":($LY<100?"redWords":"blackWords"))."'>".$LY."%</td><td class='right ".($budget>100?"greenWords":($budget<100?"redWords":"blackWords"))."'>".$budget."%</td><td width='10px'> </td>
            <td class='right'>".$visitas."</td>
            <td class='right ".($visitasLY == "--"?"blackWords": ($visitasLY>0?"greenWords":($visitasLY<0?"redWords":"blackWords")))."'>".$visitasLY."%</td>
            <td width='10px'> </td>
            <td class='right'>".$cheque."</td>
            <td class='right ".($chequeLY == "--"?"blackWords": ($chequeLY>0?"greenWords":($chequeLY<0?"redWords":"blackWords")))."'>".$chequeLY."%</td></tr>";
        }

        $budget = (empty($this->result["totalesVenta"][0]["Budget"])? 0: number_format($this->result["totalesVenta"][0]["CurrentNetSales"]/$this->result["totalesVenta"][0]["Budget"] * 100,0,"",""));
        $LY = (empty($this->result["totalesVenta"][0]["LYNetSales"])?"--":number_format($this->result["totalesVenta"][0]["CurrentNetSales"] /$this->result["totalesVenta"][0]["LYNetSales"] *100,0,"",""));
        $visitasLY= empty($this->result["totalesVenta"][0]["Visitas"])?"--": ( empty($this->result["totalesVenta"][0]["VisitasLY"])?"--":number_format($this->result["totalesVenta"][0]["Visitas"]/$this->result["totalesVenta"][0]["VisitasLY"],2,".",","));
        $chequeLY= empty($this->result["totalesVenta"][0]["Cheque"])?"--": ( empty($this->result["totalesVenta"][0]["ChequeLY"])?"--":number_format($this->result["totalesVenta"][0]["Cheque"]/$this->result["totalesVenta"][0]["ChequeLY"],2,".",","));
        $html .= "<tr><td class='rowborder rowbordertop tcenter'>Total</td><td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][0]["CurrentNetSales"],0,"",",")."</td><td class='rowborder rowbordertop right ".($LY>100?"greenWords":($LY<100?"redWords":"blackWords"))."'>".$LY."%</td><td class='rowborder rowbordertop right ".($budget>100?"greenWords":($budget<100?"redWords":"blackWords"))."'>".$budget."%</td><td width='10px'> </td>
        <td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][0]["Visitas"],0,"",",")."</td>
        <td class='rowborder rowbordertop right'>".$visitasLY."%</td>
        <td width='10px'> </td>
        <td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][0]["Cheque"],0,"","")."</td>
        <td class='rowborder rowbordertop right'>".$chequeLY."</td></tr>";
        $budget = (empty($this->result["totalesVenta"][1]["Budget"])? 0: number_format($this->result["totalesVenta"][1]["CurrentNetSales"]/$this->result["totalesVenta"][1]["Budget"] * 100,0,"",""));
        $LY = (empty($this->result["totalesVenta"][1]["LYNetSales"])?"--":number_format($this->result["totalesVenta"][1]["CurrentNetSales"] /$this->result["totalesVenta"][1]["LYNetSales"] *100,0,"",""));
        $visitasLY= empty($this->result["totalesVenta"][1]["Visitas"])?"--": ( empty($this->result["totalesVenta"][1]["VisitasLY"])?"--":number_format($this->result["totalesVenta"][1]["Visitas"]/$this->result["totalesVenta"][1]["VisitasLY"],2,".",","));
        $chequeLY= empty($this->result["totalesVenta"][1]["Cheque"])?"--": ( empty($this->result["totalesVenta"][1]["ChequeLY"])?"--":number_format($this->result["totalesVenta"][1]["Cheque"]/$this->result["totalesVenta"][1]["ChequeLY"],2,".",","));
        $html .= "<tr><td class='rowborder rowbordertop tcenter'>Mismas Tiendas</td><td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][1]["CurrentNetSales"],0,"",",")."</td><td class='rowborder rowbordertop right ".($LY>100?"greenWords":($LY<100?"redWords":"blackWords"))."'>".$LY."%</td><td class='rowborder rowbordertop right ".($budget>100?"greenWords":($budget<100?"redWords":"blackWords"))."'>".$budget."%</td><td width='10px'> </td><td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][1]["Visitas"],0,"",",")."</td><td class='rowborder rowbordertop right'>".$visitasLY."%</td><td width='10px'> </td>
        <td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][1]["Cheque"],0,"","")."</td><td class='rowborder rowbordertop right'>".$chequeLY."</td></tr>";
        $budget = (empty($this->result["totalesVenta"][2]["Budget"])? 0: number_format($this->result["totalesVenta"][2]["CurrentNetSales"]/$this->result["totalesVenta"][2]["Budget"] * 100,0,"",""));
        $LY = (empty($this->result["totalesVenta"][2]["LYNetSales"])?"--":number_format($this->result["totalesVenta"][2]["CurrentNetSales"] /$this->result["totalesVenta"][2]["LYNetSales"] *100,0,"",""));
        $visitasLY= empty($this->result["totalesVenta"][2]["Visitas"])?"--": ( empty($this->result["totalesVenta"][2]["VisitasLY"])?"--":number_format($this->result["totalesVenta"][2]["Visitas"]/$this->result["totalesVenta"][2]["VisitasLY"],2,".",","));
        $chequeLY= empty($this->result["totalesVenta"][2]["Cheque"])?"--": ( empty($this->result["totalesVenta"][2]["ChequeLY"])?"--":number_format($this->result["totalesVenta"][2]["Cheque"]/$this->result["totalesVenta"][2]["ChequeLY"],2,".",","));
        $html .= "<tr><td class='rowborder rowbordertop tcenter'>Otras Tiendas</td><td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][2]["CurrentNetSales"],0,"",",")."</td><td class='rowborder rowbordertop right ".($LY>100?"greenWords":($LY<100?"redWords":"blackWords"))."'>".$LY."%</td><td class='rowborder rowbordertop right ".($budget>100?"greenWords":($budget<100?"redWords":"blackWords"))."'>".$budget."%</td><td width='10px'> </td><td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][2]["Visitas"],0,"",",")."</td><td class='rowborder rowbordertop right'>".$visitasLY."%</td><td width='10px'> </td>
        <td class='rowborder rowbordertop right'>".number_format($this->result["totalesVenta"][2]["Cheque"],0,"","")."</td><td class='rowborder rowbordertop right'>".$chequeLY."</td></tr>";
        $html .= "</table>";
        $html .= '<div class="page-break"></div>';
        $html .= '<div style="width: 100%;height: 20px;margin-bottom:30px;padding: 0px;" ><div style="float:left;"><H2>P&L MES '.(date("m Y", strtotime($this->initDate))).'</H2></div><div style="float:right; padding: 20px;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/mk_logo_nuevo_200_11.png" /></div></div>';
        $html .="<table cellspacing='0px'>";
        $html .= "<tr><td class='rowborder tcenter'>Sucursal</td><td class='rowborder tcenter'>Ingreso</td><td class='rowborder tcenter'>C. Venta</td><td class='rowborder tcenter'>M. Obra</td><td class='rowborder tcenter'>O. Gasto</td><td class='rowborder tcenter'>G. Prod</td><td class='rowborder tcenter'>EBITDA</td><td width='10px'> </td><td class='rowborder tcenter'>Costo</td><td class='rowborder tcenter'>Gasto</td><td class='rowborder tcenter'>EBITDA</td><td width='10px'> </td><td class='rowborder tcenter'>Costo</td><td class='rowborder tcenter'>Gasto</td><td class='rowborder tcenter'>EBITDA</td><td class='rowborder tcenter'>Costo</td><td class='rowborder tcenter'>Gasto</td><td class='rowborder tcenter'>EBITDA</td></tr>";
        
        foreach($this->result["venta"] as $v)
        {
            $costo = empty($this->result["pal"][$v->idSucursal][6])?"--":number_format($this->result["pal"][$v->idSucursal][6]*100,0,"","");
            $gasto = empty($this->result["pal"][$v->idSucursal][7])?"--":number_format($this->result["pal"][$v->idSucursal][7]*100,0,"","");
            $ebitda = empty($this->result["pal"][$v->idSucursal][8])?"--":number_format($this->result["pal"][$v->idSucursal][8]*100,0,"","");
            $acosto = empty($this->result["pal"][$v->idSucursal][9])?"--":number_format($this->result["pal"][$v->idSucursal][9]*100,0,"","");
            $agasto = empty($this->result["pal"][$v->idSucursal][10])?"--":number_format($this->result["pal"][$v->idSucursal][10]*100,0,"","");
            $aebitda = empty($this->result["pal"][$v->idSucursal][11])?"--":number_format($this->result["pal"][$v->idSucursal][11]*100,0,"","");
            $dcosto = empty($this->result["pal"][$v->idSucursal][12])?"--":number_format($this->result["pal"][$v->idSucursal][12]*100,0,"","");
            $dgasto = empty($this->result["pal"][$v->idSucursal][13])?"--":number_format($this->result["pal"][$v->idSucursal][13]*100,0,"","");
            $debitda = empty($this->result["pal"][$v->idSucursal][14])?"--":number_format($this->result["pal"][$v->idSucursal][14]*100,0,"","");
            if(!empty($this->result["pal"][$v->idSucursal][0]))
                $html.="<tr><td>".$v->sucursal."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][0],0,"",",")."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][1],0,"",",")."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][2],0,"",",")."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][3],0,"",",")."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][4],0,"",",")."</td><td class='right'>".number_format($this->result["pal"][$v->idSucursal][5],0,"",",")."</td><td width='10px'> </td><td class='right'>".$costo."%</td><td class='right'>".$gasto."%</td><td class='right'>".$ebitda."%</td><td width='10px'> </td><td class='right'>".$acosto."%</td><td class='right'>".$agasto."%</td><td class='right'>".$aebitda."%</td><td class='right ".($dcosto == '--'? "blackWords":($dcosto>0?"redWords":"greenWords"))."'>".$dcosto."%</td><td class='right ".($dgasto == '--'? "blackWords":($dgasto>0?"redWords":"greenWords"))."'>".$dgasto."%</td><td class='right ".($debitda == '--'? "blackWords":($debitda>0?"greenWords":"redWords"))."'>".$debitda."%</td></tr>";
        }

        //$html .= "<tr><td class='rowborder rowbordertop tcenter'>Global</td><td class='rowborder rowbordertop right'>COGS</td><td class='rowborder rowbordertop right'>Margin</td><td class='rowborder rowbordertop right'>Expenses</td><td class='rowborder rowbordertop right'>EBITDA</td><td class='rowborder rowbordertop right'>LABOR</td></tr>";
        $html .= "</table>";
        $html .= '<div class="page-break"></div>';
        $html .= '<div style="width: 100%;height: 20px;margin-bottom:30px;padding: 0px;" ><div style="float:left;"><H2>VENTA SEMANAL MES '.(date("m Y", strtotime($this->initDate))).'</H2></div><div style="float:right; padding: 20px;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/mk_logo_nuevo_200_11.png" /></div></div>';
        $html .="<table cellspacing='0px'>";
        $html .= "<tr><td class='rowborder tcenter'>Promedio Sem</td><td class='rowborder tcenter'>Semana</td><td class='rowborder tcenter'>Total</td><td class='rowborder tcenter'>%LY</td>";
        for ($i = 0; $i < count($this->result["rvcnames"]); $i++) {
            $html.= "<td width='10px'> </td><td class='rowborder tcenter'>".$this->result["rvcnames"][$i]->rvc."</td><td class='rowborder tcenter'>%LY</td>";
        }
        $html.="</tr>";
      
        foreach($this->result["week"] as $semana =>$v)
        {
            
            //$html.= "<tr><td>".$v->sucursal."</td><td class='right'>".$cogs."</td><td class='right'>".$gmargin."</td><td class='right'>".$opsexpenses."</td><td class='right'>".$ebitda."</td><td class='right'>".$labor."</td></tr>";
            if(!empty($v[0]))
            {
                $html.= "<tr><td>".number_format($v[0]["actual"][0]/7,0,".",",")."</td><td>Sem ".$semana."</td><td class='right'>".number_format($v[0]["actual"][0],0,".",",")."</td><td class='right ".($v[-1]>=1?"greenWords":"redWords")."'>".number_format($v[-1]*100,0,".",",")."%</td>";
                //dd($this->result["week"][$semana]);           
                foreach( $this->result["week"][$semana] AS $rvcId => $vd) {
                    //dd($vd);
                    if($rvcId>0)
                    {
                        $tmpPer = $vd[3]*100;
                        
                        if (is_numeric($tmpPer))
                        {
                            $tmpPer = number_format($tmpPer,0,".",",");
                            $html.= "<td width='10px'> </td><td class='right '>".number_format($vd[0]["actual"],0,".",",")."</td>";
                            //dd($vd[3]);
                            
                            $html.= "<td class='right ".($vd[3]>=1?"greenWords":"redWords")."'>".$tmpPer."%</td>";
                        }
                    }
                }
            }

        }

        $html .="</table>";
        $html .= '<div class="page-break"></div>';
        $html .= '<div style="width: 100%;height: 20px;margin-bottom:30px;padding: 0px;" ><div style="float:left;"><H2>EVALUACION MES '.(date("m Y", strtotime($this->initDate))).'</H2></div><div style="float:right; padding: 20px;"><img src="'.$_SERVER["DOCUMENT_ROOT"].'/mk_logo_nuevo_200_11.png" /></div></div>';
        $html .="<table cellspacing='0px'>";
        $html .= "<tr><td class='rowborder tcenter'>Sucursal</td><td class='rowborder tcenter'>Vitrina</td><td class='rowborder tcenter'>Cocina</td><td class='rowborder tcenter'>Panadería</td><td class='rowborder tcenter'>Barra</td><td class='rowborder tcenter'>Salón</td><td class='rowborder tcenter'>Mkt</td><td class='rowborder tcenter'>Legal</td><td class='rowborder tcenter'>Prods Temp</td><td class='rowborder tcenter'>APP Delivery</td><td class='rowborder tcenter'>General</td><td class='rowborder tcenter'>Mes Anterior</td><td class='rowborder tcenter'>Anual</td></tr>";
        //$cont=0;
        foreach($this->result["evaluacion"] as $evl)
        {
            //$cont++;
            $html.="<tr><td>".$evl->nombre."</td><td class='right ".($evl->clVit>=80?"greenWords":($evl->clVit<=60?"redWords":"yellowWords"))."'>".(empty($evl->clVit)? "--":number_format($evl->clVit,0))."</td><td class='right ".($evl->clCoc>=80?"greenWords":($evl->clCoc<=60?"redWords":"yellowWords"))."'>".(empty($evl->clCoc)? "--":number_format($evl->clCoc,0))."</td><td class='right ".($evl->clPan>=80?"greenWords":($evl->clPan<=60?"redWords":"yellowWords"))."'>".(empty($evl->clPan)? "--":number_format($evl->clPan,0))."</td><td class='right ".($evl->clBar>=80?"greenWords":($evl->clBar<=60?"redWords":"yellowWords"))."'>".(empty($evl->clBar)? "--":number_format($evl->clBar,0))."</td><td class='right ".($evl->clSal>=80?"greenWords":($evl->clSal<=60?"redWords":"yellowWords"))."'>".(empty($evl->clSal)? "--":number_format($evl->clSal,0))."</td><td class='right ".($evl->clMkt>=80?"greenWords":($evl->clMkt<=60?"redWords":"yellowWords"))."'>".(empty($evl->clMkt)? "--":number_format($evl->clMkt,0))."</td><td class='right ".($evl->clLeg>=80?"greenWords":($evl->clLeg<=60?"redWords":"yellowWords"))."'>".(empty($evl->clLeg)? "--":number_format($evl->clLeg,0))."</td><td class='right ".($evl->clProds>=80?"greenWords":($evl->clProds<=60?"redWords":"yellowWords"))."'>".(empty($evl->clProds)? "--":number_format($evl->clProds,0))."</td><td class='right ".($evl->clApp>100?"greenWords":($evl->clApp<100?"redWords":"blackWords"))."'>".(empty($evl->clApp)? "--":number_format($evl->clApp,0))."</td><td class='right ".($evl->Actual>=80 ?"greenWords":($evl->Actual<=60?"redWords":"yellowWords"))."'>".(empty($evl->Actual)? "--":number_format($evl->Actual,0))."</td><td class='right ".(empty($evl->Anterior) || empty($evl->Actual)?"blackWords":($evl->Actual-$evl->Anterior<0?"redWords":"greenWords"))."'>".(empty($evl->Anterior) || empty($evl->Actual)? "--":number_format($evl->Actual-$evl->Anterior,0))."</td><td class='right ".(empty($evl->Anual) || empty($evl->Actual)?"blackWords":($evl->Actual-$evl->Anual<0?"redWords":"greenWords"))."'>".(empty($evl->Anual) || empty($evl->Actual)? "--":number_format($evl->Actual-$evl->Anual,0))."</td></tr>";
            /*if($cont>=25)
                {
                    $html .="</table>";
                    $html .= '<div class="page-break"></div>';
                    $html .= '<H3">Evaluación (Continuación)</H3>';
                    $html .="<table cellspacing='0px'>";
                    $html .= "<tr><td class='rowborder tcenter'>Sucursal</td><td class='rowborder tcenter'>Vitrina</td><td class='rowborder tcenter'>Cocina</td><td class='rowborder tcenter'>Panadería</td><td class='rowborder tcenter'>Barra</td><td class='rowborder tcenter'>Salón</td><td class='rowborder tcenter'>Mkt</td><td class='rowborder tcenter'>Legal</td><td class='rowborder tcenter'>Prods Temp</td><td class='rowborder tcenter'>APP Delivery</td><td class='rowborder tcenter'>General</td><td class='rowborder tcenter'>Mes Anterior</td><td class='rowborder tcenter'>Anual</td></tr>";
                    $cont=0;

                }
            */
        }
        //echo $html;
        $html .="</table>";
        $html .= '<div class="page-break"></div>';
        $html .= "<div>";

        $chart = new GoogChart();
        $weekSales = $this->result['week'];
        $path = '/var/www/html/Laravel/public/storage';
        $size = [850, 250];

        $data = array();

        $axisRange = [0, 0, 0];

        foreach ($weekSales as $key => $value) {
            if ($key != '52') {
                for ($i = 0; $i < 3; $i++) {
                    $actual = $value[0]['actual'][$i];
                    $anterior = $value[0]['anterior'][$i];
                    $tanios = $value[0]['tanios'][$i];

                    if ($actual > $axisRange[$i]) {
                        $axisRange[$i] = $actual;
                    }
                    if ($anterior > $axisRange[$i]) {
                        $axisRange[$i] = $anterior;
                    }
                    if ($tanios > $axisRange[$i]) {
                        $axisRange[$i] = $tanios;
                    }
                }
            }
        }

        foreach ($axisRange as $key => $value) {
            if (abs($axisRange[$key]) > 999999) {
                $axisRange[$key] = (ceil($axisRange[$key] / 1000000) + 1) * 1000000;
            } else if (abs($axisRange[$key]) > 999) {
                $axisRange[$key] = (ceil($axisRange[$key] / 1000) + 1) * 1000;
            } else {
                $axisRange[$key] = (ceil($axisRange[$key] / 100)) * 100;
            }
        }

        foreach ($weekSales as $sem => $value) {
            if ($sem != '52') {
                $data[0]['Actual']["$sem"] = ($value[0]['actual'][0] * 100) / $axisRange[0];
                $data[0]['Anterior']["$sem"] = ($value[0]['anterior'][0] * 100) / $axisRange[0];
                $data[0]['2 Años']["$sem"] = ($value[0]['tanios'][0] * 100) / $axisRange[0];
                $data[1]['Actual']["$sem"] = ($value[0]['actual'][1] * 100) / $axisRange[1];
                $data[1]['Anterior']["$sem"] = ($value[0]['anterior'][1] * 100) / $axisRange[1];
                $data[1]['2 Años']["$sem"] = ($value[0]['tanios'][1] * 100) / $axisRange[1];
                $data[2]['Actual']["$sem"] = ($value[0]['actual'][2] * 100) / $axisRange[2];
                $data[2]['Anterior']["$sem"] = ($value[0]['anterior'][2] * 100) / $axisRange[2];
                $data[2]['2 Años']["$sem"] = ($value[0]['tanios'][2] * 100) / $axisRange[2];
            }
        }


        // Set graph colors
        $color = array(
            '#99C754',
            '#54C7C5',
            '#999999',
        );

        $labelsPositionY = [
            0.12,
            0.25,
            0.37,
            0.50,
            0.62,
            0.75,
            0.87,
            1.0,
        ];

        $customPosition = array();
        $customAxisY = array();
        foreach ($axisRange as $keyRange => $range) {
            foreach ($labelsPositionY as $key => $value) {
                $position = $range * $value;
                $customPosition[$keyRange][] = $position;
                if (abs($position) > 999999) {
                    $custom = ceil($position / 1000000) . "M";
                } else if (abs($position) > 999) {
                    $custom = ceil($position / 1000) . "K";
                } else {
                    $custom = ceil($position);
                }
                $customAxisY[$keyRange][] = $custom;
            }
        }


        foreach ($customAxisY as $key => $value) {
            $customPosition[$key] = implode(",", $customPosition[$key]);
            $customAxisY[$key] = implode("|", $customAxisY[$key]);
        }

        $chart->setChartAttrs(array(
            'type' => 'line',
            'title' => 'Venta vs Año anterior',
            'data' => $data[0],
            'size' => array(1000, 300),
            'color' => $color,
            'labelsXY' => true,
            'AxisRange' => $axisRange[0],
            'LabelsAxis' => "1:|Semanas|2:|$customAxisY[0]",
            'LabelsPosition' => "1,50|2,$customPosition[0]",
            'GridValues' => true,
            'titleStyle' => '000000,20,c',
        ));

        $content = file_get_contents($chart);
        file_put_contents("$path/chart1.png", $content);
        $html .= sprintf('<img src="%s" style="width:%spx;height:%spx;" />', "$path/chart1.png", $size[0], $size[1]);

        $chart->setChartAttrs(array(
            'type' => 'line',
            'title' => 'Visitas vs Año anterior',
            'data' => $data[1],
            'size' => array(1000, 300),
            'color' => $color,
            'labelsXY' => true,
            'AxisRange' => $axisRange[1],
            'LabelsAxis' => "1:|Semanas|2:|$customAxisY[1]",
            'LabelsPosition' => "1,50|2,$customPosition[1]",
            'GridValues' => true,
            'titleStyle' => '000000,20,c',
        ));


        $content = file_get_contents($chart);
        file_put_contents("$path/chart2.png", $content);
        $html .= sprintf('<img src="%s" style="width:%spx;height:%spx;" />', "$path/chart2.png", $size[0], $size[1]);

        $chart->setChartAttrs(array(
            'type' => 'line',
            'title' => 'Cheque Promedio vs Año anterior',
            'data' => $data[2],
            'size' => array(1000,300),
            'color' => $color,
            'labelsXY' => true,
            'AxisRange' => $axisRange[2],
            'LabelsAxis' => "1:|Semanas|2:|$customAxisY[2]",
            'LabelsPosition' => "1,50|2,$customPosition[2]",
            'GridValues' => true,
            'titleStyle' => '000000,20,c'
        ));


        $content = file_get_contents($chart);
        file_put_contents("$path/chart3.png", $content);
        $html .= sprintf('<img src="%s" style="width:%spx;height:%spx;" />', "$path/chart3.png", $size[0], $size[1]);
        $html .= "</div>";

        $pdf = PDF::loadHTML($html)->setPaper('letter', 'landscape')->setOptions(['dpi' => 80, 'defaultFont' => 'sans-serif']);;
        // unlink("$path/chart1.png");
        // unlink("$path/chart2.png");
        // unlink("$path/chart3.png");
        return $pdf->download('reporte.pdf');
        
        //return $html;
        //dd($this->result);
    }

    public function widget()
    {
    }

    public function getResult($type)
    {
        if ($type == "xlsx") {
            return $this->exportReport();
        } else {
            $parser = new ReportParser($type);
            return $parser->parse($this->result);
        }
    }

}