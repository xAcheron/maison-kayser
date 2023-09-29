<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use App\Classes\Reports\utils\UserLocation;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class CheckListReporter implements iReporter
{

    private $initDate;
    private $endDate;
    private $month;
    private $months = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    private $location;
    private $result;
    private $locationID;
    private $lastWeek;
    const resultView = "reports.results.evaluacion";

    public function setParams($params)
    {

        $this->initDate = $params["daterange"];
        $this->lastWeek = date("Y-m-d", strtotime(date("Y-m-d") . " -1 WEEK"));
        $this->month = $this->months[date("n", strtotime($this->initDate))];
        $location = new UserLocation;
        $location->get($params["location"]);
        $this->location = implode(',', $location->locationNombres) ?? $location->locationName;
        $this->locationID = $location->locationID;
    }


    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql, [$idLocation]);
        return array("'" . $locations[0]->idMicros . "'", $locations[0]->id);
    }

    public function getLocations($idEmpresa)
    {
        $sql = "SELECT * FROM sucursales WHERE " . (empty($this->tier) || $this->tier == "null" ? "" : " idTier = " . $this->tier . " AND ") . " idEmpresa = ?;";
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
        $ResultadoDistrital = array();

        $mes = date("m", strtotime($this->initDate));
        $anio = date("Y", strtotime($this->initDate));

        $sql =
            "
        SELECT gen.*, det.*, sup.idUsuario, users.name AS distrital FROM 
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
        (SELECT Z.idSuc,SUM(IF(SB.idSub= 'VISUPVITRINA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPVITRINA',1,0)),1) clVit, (SUM(IF(SB.idSub= 'VISUPVITRINA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPVITRINA',SB.valorEvaluacion,0)))/100 punVit,SUM(IF(SB.idSub= 'VISUPCOCINA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPCOCINA',1,0)),1) clCoc, (SUM(IF(SB.idSub= 'VISUPCOCINA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPCOCINA',SB.valorEvaluacion,0)))/100 punCoc,SUM(IF(SB.idSub= 'VISUPPAN',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPPAN',1,0)),1) clPan, (SUM(IF(SB.idSub= 'VISUPPAN',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPPAN',SB.valorEvaluacion,0)))/100 punPan,SUM(IF(SB.idSub= 'VISUPBARRA',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPBARRA',1,0)),1) clBar, (SUM(IF(SB.idSub= 'VISUPBARRA',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPBARRA',SB.valorEvaluacion,0)))/100 punBar,SUM(IF(SB.idSub= 'VISUPSALON',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPSALON',1,0)),1) clSal, (SUM(IF(SB.idSub= 'VISUPSALON',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPSALON',SB.valorEvaluacion,0)))/100 punSal,SUM(IF(SB.idSub= 'VISUPMKT',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPMKT',1,0)),1) clMkt, (SUM(IF(SB.idSub= 'VISUPMKT',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPMKT',SB.valorEvaluacion,0)))/100 punMkt,SUM(IF(SB.idSub= 'VISUPLEGAL',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPLEGAL',1,0)),1) clLeg, (SUM(IF(SB.idSub= 'VISUPLEGAL',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPLEGAL',SB.valorEvaluacion,0)))/100 punLeg,SUM(IF(SB.idSub= 'VISUPTEMP',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPTEMP',1,0)),1) clProds, (SUM(IF(SB.idSub= 'VISUPTEMP',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPTEMP',SB.valorEvaluacion,0)))/100 punProds,SUM(IF(SB.idSub= 'VISUPAPP',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPAPP',1,0)),1) clApp, (SUM(IF(SB.idSub= 'VISUPAPP',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPAPP',SB.valorEvaluacion,0)))/100 punApp, SUM(IF(SB.idSub= 'VISUPGRL',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPGRL',1,0)),1) clGrl, (SUM(IF(SB.idSub= 'VISUPGRL',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPGRL',SB.valorEvaluacion,0)))/100 punGrl, SUM(IF(SB.idSub= 'VISUPREP',Y.calificacion,0))/COALESCE(SUM(IF(SB.idSub= 'VISUPREP',1,0)),1) clRep, (SUM(IF(SB.idSub= 'VISUPREP',Y.calificacion,0))*MAX(IF(SB.idSub = 'VISUPREP',SB.valorEvaluacion,0)))/100 punRep FROM checklist_sub_info SB INNER JOIN checklist_generados_sub Y ON SB.idSubSeccion = Y.idSub INNER JOIN checklist_sub_info W ON Y.idSub = W.idSubseccion INNER JOIN checklist_generados Z ON Z.idCheckList = Y.idCheckList INNER JOIN checklist_info AS WW ON WW.id = Z.idTipoCheck WHERE WW.idEvaluacion =1 AND Z.idSuc IN (" . $this->locationID . ") AND MONTH(Z.fechaGenerada)=? AND YEAR(Z.fechaGenerada)=? GROUP BY Z.idSuc) det ON gen.id = det.idSuc 
        LEFT JOIN supervisor_sucursal sup ON sup.idSucursal = gen.id LEFT JOIN users ON users.id = sup.idUsuario ORDER BY gen.Actual";

        $result = DB::select($sql, [$mes, $anio, $mes - 1, $anio, $anio, $mes, $anio]);


        foreach ($result as $suc) {
            $ResultadoDistrital[$suc->idUsuario] =
                (object)[
                    "nombre" => $suc->distrital,
                    "clVit" => (empty($ResultadoDistrital[$suc->idUsuario]->clVit) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clVit) + $suc->clVit,
                    "punVit" => (empty($ResultadoDistrital[$suc->idUsuario]->punVit) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punVit) + $suc->punVit,
                    "clCoc" => (empty($ResultadoDistrital[$suc->idUsuario]->clCoc) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clCoc) + $suc->clCoc,
                    "punCoc" => (empty($ResultadoDistrital[$suc->idUsuario]->punCoc) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punCoc) + $suc->punCoc,
                    "clPan" => (empty($ResultadoDistrital[$suc->idUsuario]->clPan) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clPan) + $suc->clPan,
                    "punPan" => (empty($ResultadoDistrital[$suc->idUsuario]->punPan) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punPan) + $suc->punPan,
                    "clBar" => (empty($ResultadoDistrital[$suc->idUsuario]->clBar) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clBar) + $suc->clBar,
                    "punBar" => (empty($ResultadoDistrital[$suc->idUsuario]->punBar) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punBar) + $suc->punBar,
                    "clSal" => (empty($ResultadoDistrital[$suc->idUsuario]->clSal) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clSal) + $suc->clSal,
                    "punSal" => (empty($ResultadoDistrital[$suc->idUsuario]->punSal) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punSal) + $suc->punSal,
                    "clMkt" => (empty($ResultadoDistrital[$suc->idUsuario]->clMkt) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clMkt) + $suc->clMkt,
                    "punMkt" => (empty($ResultadoDistrital[$suc->idUsuario]->punMkt) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punMkt) + $suc->punMkt,
                    "clLeg" => (empty($ResultadoDistrital[$suc->idUsuario]->clLeg) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clLeg) + $suc->clLeg,
                    "punLeg" => (empty($ResultadoDistrital[$suc->idUsuario]->punLeg) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punLeg) + $suc->punLeg,
                    "clProds" => (empty($ResultadoDistrital[$suc->idUsuario]->clProds) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clProds) + $suc->clProds,
                    "punProds" => (empty($ResultadoDistrital[$suc->idUsuario]->punProds) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punProds) + $suc->punProds,
                    "clApp" => (empty($ResultadoDistrital[$suc->idUsuario]->clApp) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clApp) + $suc->clApp,
                    "punApp" => (empty($ResultadoDistrital[$suc->idUsuario]->punApp) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punApp) + $suc->punApp,
                    "clGrl" => (empty($ResultadoDistrital[$suc->idUsuario]->clGrl) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clGrl) + $suc->clGrl,
                    "punGrl" => (empty($ResultadoDistrital[$suc->idUsuario]->punGrl) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punGrl) + $suc->punGrl,
                    "clRep" => (empty($ResultadoDistrital[$suc->idUsuario]->clRep) ? 0 : $ResultadoDistrital[$suc->idUsuario]->clRep) + $suc->clRep,
                    "punRep" => (empty($ResultadoDistrital[$suc->idUsuario]->punRep) ? 0 : $ResultadoDistrital[$suc->idUsuario]->punRep) + $suc->punRep,
                    "Actual" => (empty($ResultadoDistrital[$suc->idUsuario]->Actual) ? 0 : $ResultadoDistrital[$suc->idUsuario]->Actual) + $suc->Actual,
                    "noEle" => (empty($ResultadoDistrital[$suc->idUsuario]->noEle) ? 0 : $ResultadoDistrital[$suc->idUsuario]->noEle) + 1,
                    "Anterior" => (empty($ResultadoDistrital[$suc->idUsuario]->Anterior) ? 0 : $ResultadoDistrital[$suc->idUsuario]->Anterior) + $suc->Anterior,
                    "Anual" => (empty($ResultadoDistrital[$suc->idUsuario]->Anual) ? 0 : $ResultadoDistrital[$suc->idUsuario]->Anual) + $suc->Anual,
                ];
        }

        $sortArrDist = array();
        foreach ($ResultadoDistrital as $idDis => $dis) {
            $sortArrDist[$idDis] = $dis->Actual / $dis->noEle;
        }

        asort($sortArrDist);

        $ResultadoOrdDistrital = array();
        foreach ($sortArrDist as $idDis => $dis) {
            $ResultadoOrdDistrital[] = $ResultadoDistrital[$idDis];
        }

        foreach ($ResultadoOrdDistrital as $dis) {
            $dis->clVit = $dis->clVit / $dis->noEle;
            $dis->punVit = $dis->punVit / $dis->noEle;
            $dis->clCoc = $dis->clCoc / $dis->noEle;
            $dis->punCoc = $dis->punCoc / $dis->noEle;
            $dis->clPan = $dis->clPan / $dis->noEle;
            $dis->punPan = $dis->punPan / $dis->noEle;
            $dis->clBar = $dis->clBar / $dis->noEle;
            $dis->punBar = $dis->punBar / $dis->noEle;
            $dis->clSal = $dis->clSal / $dis->noEle;
            $dis->punSal = $dis->punSal / $dis->noEle;
            $dis->clMkt = $dis->clMkt / $dis->noEle;
            $dis->punMkt = $dis->punMkt / $dis->noEle;
            $dis->clLeg = $dis->clLeg / $dis->noEle;
            $dis->punLeg = $dis->punLeg / $dis->noEle;
            $dis->clProds = $dis->clProds / $dis->noEle;
            $dis->punProds = $dis->punProds / $dis->noEle;
            $dis->clApp = $dis->clApp / $dis->noEle;
            $dis->punApp = $dis->punApp / $dis->noEle;
            $dis->clGrl = $dis->clGrl / $dis->noEle;
            $dis->punGrl = $dis->punGrl / $dis->noEle;
            $dis->clRep = $dis->clRep / $dis->noEle;
            $dis->punRep = $dis->punRep / $dis->noEle;
            $dis->Actual = $dis->Actual / $dis->noEle;
            $dis->Anterior = $dis->Anterior / $dis->noEle;
            $dis->Anual = $dis->Anual / $dis->noEle;
        }

        $this->result = (object)[
            "sucursales" => $result,
            "distritales" => $ResultadoOrdDistrital
        ];
    }
    public function getResult($type)
    {
        if ($type == "xlsx") {
            $this->exportXLSX();
        } elseif ($type == "email") {
            $this->exportEmail();
        } else {
            $parser = new ReportParser($type);

            if ($type == "html")
                return $parser->parse($this->result, json_decode(json_encode(array("view" => SELF::resultView))));

            return $parser->parse($this->result);
        }
    }

    private function exportReport()
    {
        $verde = "ff3b851a";
        $amarillo = "ffDabf02";
        $rojo = "ffCe110b";
        $ResultadoDistrital = array();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $currentSheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(12);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(12);

        $spreadsheet->getActiveSheet()->getStyle('A1:Q80')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffffffff');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $spreadsheet->getActiveSheet()->getStyle('A2:A5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $spreadsheet->getActiveSheet()->getStyle('A2:D5')->getBorders()->applyFromArray(['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);

        $currentSheet->mergeCells('B2:D2');
        $currentSheet->mergeCells('B3:D3');
        $currentSheet->mergeCells('B4:D4');
        $currentSheet->mergeCells('B5:D5');

        $currentSheet->setCellValue('A2', 'Reporte');
        $currentSheet->setCellValue('A3', 'Fechas');
        $currentSheet->setCellValue('A4', 'Sucursales');
        $currentSheet->setCellValue('A5', 'Fecha reporte');

        $currentSheet->setCellValue('B2', 'Evaluación');
        $currentSheet->setCellValue('B3', $this->initDate);
        $currentSheet->setCellValue('B4', strtoupper($this->location));
        $currentSheet->setCellValue('B5', date("Y-m-d"));

        $row = 7;

        $currentSheet->setCellValue('A' . $row, 'Distrital');
        $currentSheet->setCellValue('B' . $row, 'Vitrina');
        $currentSheet->setCellValue('C' . $row, 'Cocina');
        $currentSheet->setCellValue('D' . $row, 'Panadería');
        $currentSheet->setCellValue('E' . $row, 'Barra');
        $currentSheet->setCellValue('F' . $row, 'Salón');
        $currentSheet->setCellValue('G' . $row, 'Marketing');
        $currentSheet->setCellValue('H' . $row, 'Legal');
        $currentSheet->setCellValue('I' . $row, 'Prods Temp');
        $currentSheet->setCellValue('J' . $row, 'APP/Delivery');
        $currentSheet->setCellValue('K' . $row, 'General');
        $currentSheet->setCellValue('L' . $row, 'Reportes');
        $currentSheet->setCellValue('M' . $row, 'Total');
        $currentSheet->setCellValue('N' . $row, 'Mes Anterior');
        $currentSheet->setCellValue('O' . $row, 'Anual');

        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $row++;
        $initRow = $row;

        // foreach ($this->result as $suc) {
        //     $ResultadoDistrital[$suc->idUsuario][0] = $suc->distrital;
        //     $ResultadoDistrital[$suc->idUsuario][1] = (empty($ResultadoDistrital[$suc->idUsuario][1]) ? 0 : $ResultadoDistrital[$suc->idUsuario][1]) + $suc->clVit;
        //     $ResultadoDistrital[$suc->idUsuario][2] = (empty($ResultadoDistrital[$suc->idUsuario][2]) ? 0 : $ResultadoDistrital[$suc->idUsuario][2]) + $suc->clCoc;
        //     $ResultadoDistrital[$suc->idUsuario][3] = (empty($ResultadoDistrital[$suc->idUsuario][3]) ? 0 : $ResultadoDistrital[$suc->idUsuario][3]) + $suc->clPan;
        //     $ResultadoDistrital[$suc->idUsuario][4] = (empty($ResultadoDistrital[$suc->idUsuario][4]) ? 0 : $ResultadoDistrital[$suc->idUsuario][4]) + $suc->clBar;
        //     $ResultadoDistrital[$suc->idUsuario][5] = (empty($ResultadoDistrital[$suc->idUsuario][5]) ? 0 : $ResultadoDistrital[$suc->idUsuario][5]) + $suc->clSal;
        //     $ResultadoDistrital[$suc->idUsuario][6] = (empty($ResultadoDistrital[$suc->idUsuario][6]) ? 0 : $ResultadoDistrital[$suc->idUsuario][6]) + $suc->clMkt;
        //     $ResultadoDistrital[$suc->idUsuario][7] = (empty($ResultadoDistrital[$suc->idUsuario][7]) ? 0 : $ResultadoDistrital[$suc->idUsuario][7]) + $suc->clLeg;
        //     $ResultadoDistrital[$suc->idUsuario][8] = (empty($ResultadoDistrital[$suc->idUsuario][8]) ? 0 : $ResultadoDistrital[$suc->idUsuario][8]) + $suc->clProds;
        //     $ResultadoDistrital[$suc->idUsuario][9] = (empty($ResultadoDistrital[$suc->idUsuario][9]) ? 0 : $ResultadoDistrital[$suc->idUsuario][9]) + $suc->clApp;
        //     $ResultadoDistrital[$suc->idUsuario][10] = (empty($ResultadoDistrital[$suc->idUsuario][10]) ? 0 : $ResultadoDistrital[$suc->idUsuario][10]) + $suc->Actual;
        //     $ResultadoDistrital[$suc->idUsuario][11] = (empty($ResultadoDistrital[$suc->idUsuario][11]) ? 0 : $ResultadoDistrital[$suc->idUsuario][11]) + 1;
        //     $ResultadoDistrital[$suc->idUsuario][12] = (empty($ResultadoDistrital[$suc->idUsuario][12]) ? 0 : $ResultadoDistrital[$suc->idUsuario][12]) + $suc->Anterior;
        //     $ResultadoDistrital[$suc->idUsuario][13] = (empty($ResultadoDistrital[$suc->idUsuario][13]) ? 0 : $ResultadoDistrital[$suc->idUsuario][13]) + $suc->Anual;
        // }
        // $sortArrDist = array();
        // foreach ($ResultadoDistrital as $idDis => $dis) {
        //     $sortArrDist[$idDis] = $dis[10] / $dis[11];
        // }

        // asort($sortArrDist);

        // $ResultadoOrdDistrital = array();
        // foreach ($sortArrDist as $idDis => $dis) {
        //     $ResultadoOrdDistrital[] = $ResultadoDistrital[$idDis];
        // }

        $ResultadoOrdDistrital = $this->result->distritales;

        foreach ($ResultadoOrdDistrital as $dis) {
            // $dis[1] = $dis[1] / $dis[11];
            // $dis[2] = $dis[2] / $dis[11];
            // $dis[3] = $dis[3] / $dis[11];
            // $dis[4] = $dis[4] / $dis[11];
            // $dis[5] = $dis[5] / $dis[11];
            // $dis[6] = $dis[6] / $dis[11];
            // $dis[7] = $dis[7] / $dis[11];
            // $dis[8] = $dis[8] / $dis[11];
            // $dis[9] = $dis[9] / $dis[11];
            // $dis[10] = $dis[10] / $dis[11];
            // $dis[12] = $dis[12] / $dis[11];
            // $dis[13] = $dis[13] / $dis[11];

            $currentSheet->setCellValue('A' . $row, $dis->nombre);
            $currentSheet->setCellValue('B' . $row, round($dis->clVit));
            $currentSheet->getStyle('B' . $row)
                ->getFont()->getColor()->setARGB($dis->clVit >= 60 ? ($dis->clVit >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('C' . $row, round($dis->clCoc));
            $currentSheet->getStyle('C' . $row)
                ->getFont()->getColor()->setARGB($dis->clCoc >= 60 ? ($dis->clCoc >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('D' . $row, round($dis->clPan));
            $currentSheet->getStyle('D' . $row)
                ->getFont()->getColor()->setARGB($dis->clPan >= 60 ? ($dis->clPan >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('E' . $row, round($dis->clBar));
            $currentSheet->getStyle('E' . $row)
                ->getFont()->getColor()->setARGB($dis->clBar >= 60 ? ($dis->clBar >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('F' . $row, round($dis->clSal));
            $currentSheet->getStyle('F' . $row)
                ->getFont()->getColor()->setARGB($dis->clSal >= 60 ? ($dis->clSal >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('G' . $row, round($dis->clMkt));
            $currentSheet->getStyle('G' . $row)
                ->getFont()->getColor()->setARGB($dis->clMkt >= 60 ? ($dis->clMkt >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('H' . $row, round($dis->clLeg));
            $currentSheet->getStyle('H' . $row)
                ->getFont()->getColor()->setARGB($dis->clLeg >= 60 ? ($dis->clLeg >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('I' . $row, round($dis->clProds));
            $currentSheet->getStyle('I' . $row)
                ->getFont()->getColor()->setARGB($dis->clProds >= 60 ? ($dis->clProds >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('J' . $row, round($dis->clApp));
            $currentSheet->getStyle('J' . $row)
                ->getFont()->getColor()->setARGB($dis->clApp >= 60 ? ($dis->clApp >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('K' . $row, round($dis->clGrl));
            $currentSheet->getStyle('K' . $row)
                ->getFont()->getColor()->setARGB($dis->clGrl >= 60 ? ($dis->clGrl >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('L' . $row, round($dis->clRep));
            $currentSheet->getStyle('L' . $row)
                ->getFont()->getColor()->setARGB($dis->clRep >= 60 ? ($dis->clRep >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('M' . $row, round(round($dis->Actual)));
            $currentSheet->getStyle('M' . $row)
                ->getFont()->getColor()->setARGB($dis->Actual >= 60 ? ($dis->Actual >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('N' . $row, round(round($dis->Anterior)));
            $currentSheet->getStyle('N' . $row)
                ->getFont()->getColor()->setARGB($dis->Anterior >= 60 ? ($dis->Anterior >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('O' . $row, round(round($dis->Anual)));
            $currentSheet->getStyle('O' . $row)
                ->getFont()->getColor()->setARGB($dis->Anual >= 60 ? ($dis->Anual >= 80 ? $verde : $amarillo) : $rojo);

            $row++;
        }
        $endRow = $row;
        for ($i = $initRow; $i <= $endRow; $i++) {
            $currentSheet->getStyle('A' . $i . ':O' . $i)->getBorders()->applyFromArray(['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        }

        $row += 2;

        $currentSheet->setCellValue('A' . $row, 'Sucursal');
        $currentSheet->setCellValue('B' . $row, 'Vitrina');
        $currentSheet->setCellValue('C' . $row, 'Cocina');
        $currentSheet->setCellValue('D' . $row, 'Panadería');
        $currentSheet->setCellValue('E' . $row, 'Barra');
        $currentSheet->setCellValue('F' . $row, 'Salón');
        $currentSheet->setCellValue('G' . $row, 'Marketing');
        $currentSheet->setCellValue('H' . $row, 'Legal');
        $currentSheet->setCellValue('I' . $row, 'Prods Temp');
        $currentSheet->setCellValue('J' . $row, 'APP/Delivery');
        $currentSheet->setCellValue('K' . $row, 'General');
        $currentSheet->setCellValue('L' . $row, 'Reportes');
        $currentSheet->setCellValue('M' . $row, 'Total');
        $currentSheet->setCellValue('N' . $row, 'Mes Anterior');
        $currentSheet->setCellValue('O' . $row, 'Anual');

        $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':O' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffe3e3e3');

        $row++;
        $initRow = $row;

        foreach ($this->result->sucursales as $suc) {
            $currentSheet->setCellValue('A' . $row, $suc->nombre);
            $currentSheet->setCellValue('B' . $row, $suc->clVit);
            $currentSheet->getStyle('B' . $row)
                ->getFont()->getColor()->setARGB($suc->clVit >= 60 ? ($suc->clVit >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('C' . $row, $suc->clCoc);
            $currentSheet->getStyle('C' . $row)
                ->getFont()->getColor()->setARGB($suc->clCoc >= 60 ? ($suc->clCoc >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('D' . $row, $suc->clPan);
            $currentSheet->getStyle('D' . $row)
                ->getFont()->getColor()->setARGB($suc->clPan >= 60 ? ($suc->clPan >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('E' . $row, $suc->clBar);
            $currentSheet->getStyle('E' . $row)
                ->getFont()->getColor()->setARGB($suc->clBar >= 60 ? ($suc->clBar >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('F' . $row, $suc->clSal);
            $currentSheet->getStyle('F' . $row)
                ->getFont()->getColor()->setARGB($suc->clSal >= 60 ? ($suc->clSal >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('G' . $row, $suc->clMkt);
            $currentSheet->getStyle('G' . $row)
                ->getFont()->getColor()->setARGB($suc->clMkt >= 60 ? ($suc->clMkt >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('H' . $row, $suc->clLeg);
            $currentSheet->getStyle('H' . $row)
                ->getFont()->getColor()->setARGB($suc->clLeg >= 60 ? ($suc->clLeg >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('I' . $row, $suc->clProds);
            $currentSheet->getStyle('I' . $row)
                ->getFont()->getColor()->setARGB($suc->clProds >= 60 ? ($suc->clProds >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('J' . $row, $suc->clApp);
            $currentSheet->getStyle('J' . $row)
                ->getFont()->getColor()->setARGB($suc->clApp >= 60 ? ($suc->clApp >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('K' . $row, round($suc->clGrl));
            $currentSheet->getStyle('K' . $row)
                ->getFont()->getColor()->setARGB($suc->clGrl >= 60 ? ($suc->clGrl >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('L' . $row, round($suc->clRep));
            $currentSheet->getStyle('L' . $row)
                ->getFont()->getColor()->setARGB($suc->clRep >= 60 ? ($suc->clRep >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('M' . $row, round(round($suc->Actual)));
            $currentSheet->getStyle('M' . $row)
                ->getFont()->getColor()->setARGB($suc->Actual >= 60 ? ($suc->Actual >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('N' . $row, round(round($suc->Anterior)));
            $currentSheet->getStyle('N' . $row)
                ->getFont()->getColor()->setARGB($suc->Anterior >= 60 ? ($suc->Anterior >= 80 ? $verde : $amarillo) : $rojo);
            $currentSheet->setCellValue('O' . $row, round(round($suc->Anual)));
            $currentSheet->getStyle('O' . $row)
                ->getFont()->getColor()->setARGB($suc->Anual >= 60 ? ($suc->Anual >= 80 ? $verde : $amarillo) : $rojo);
            $row++;
        }
        $endRow = $row;
        for ($i = $initRow; $i <= $endRow; $i++) {
            $currentSheet->getStyle('A' . $i . ':O' . $i)->getBorders()->applyFromArray(['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]);
        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");


        return $writer;
    }

    public function exportXLSX()
    {
        $writer = $this->exportReport();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Evaluacion_' . date("Ymd") . '.xlsx"');
        $writer->save("php://output");

        Storage::delete("app/public/Evaluacion_" . date("Ymd") . ".xlsx");
    }

    public function exportEmail()
    {

        $writer = $this->exportReport();

        $path = storage_path('app/public') . "/Evaluacion_" . date("Ymd") . ".xlsx";
        $writer->save($path);
        $anio = date("Y", strtotime($this->initDate));
        $mes = $this->month;
        Mail::send('reports.mail.mailEvaluacion', [], function ($message) use ($path, $mes, $anio) {
            $message->from('reportes@prigo.com.mx', 'Reportes PRIGO');
            $message->to(['arata@prigo.com.mx', 'amarch@prigo.com.mx', 'ggb@igobe.mx', 'acolin@maison-kayser.com.mx', 'fernandaflores@maison-kayser.com.mx', 'lgb@maison-kayser.com.mx', 'eromo@prigo.com.mx', 'cvillamar@prigo.com.mx', 'eromo@prigo.com.mx']);
            // $message->to(['javiles@prigo.com.mx']);
            $message->bcc(['rgallardo@maison-kayser.com.mx']);
            $message->subject("Evaluacion " . $mes . ' ' . $anio);
            $message->attach($path);
        });

        Storage::delete("app/public/Evaluacion_" . date("Ymd") . ".xlsx");
        echo 'Eviado';
    }
}
