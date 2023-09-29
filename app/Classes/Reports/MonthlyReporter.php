<?php

namespace App\Classes\Reports;

use Illuminate\Support\Facades\DB;
use App\Classes\Reports\IReporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Auth;

class MonthlyReporter implements iReporter
{
    private $initDate;
    private $endDate;
    private $location;
    private $locationID;
    private $result;
    private $company;

    public function setParams($params)
    {
        $this->initDate = date("Y") . "-01-01";
        $this->endDate = date("Y-m-d");
        $tmpLocationInfo = $this->getLocationsByUser(Auth::id());
        $this->company = $tmpLocationInfo[2];
        $this->location = $tmpLocationInfo[0];
        $this->locationID = $tmpLocationInfo[1];
        $this->meses = array(
            1 => "Ene",
            2 => "Feb",
            3 => "Mar",
            4 => "Abr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Ago",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dic"
        );
    }

    public function getLocationsByUser($idUser)
    {

        $sql = "SELECT idRole FROM config_app_access WHERE idUsuario = ? AND idAplicacion=23;";
        $roles = DB::select($sql, [$idUser]);

        if (!empty($roles[0]) && !empty($roles[0]->idRole)) {
            if ($roles[0]->idRole == 1) {
                $sql = "SELECT GROUP_CONCAT(A.id) AS id, MIN(A.idEmpresa) AS idEmpresa FROM sucursales A INNER JOIN empresa_usuario B ON A.idEmpresa = B.idEmpresa WHERE B.idUsuario = ? GROUP BY B.idUsuario;";
                $locations = DB::select($sql, [$idUser]);
            } else if ($roles[0]->idRole > 1) {
                $sql = "SELECT GROUP_CONCAT(B.idSucursal) AS id, MIN( A.idEmpresa )AS idEmpresa FROM dashboard_sucursal_usuario B INNER JOIN sucursales A ON A.id = B.idSucursal WHERE B.idUsuario = ? GROUP BY B.idUsuario;";
                $locations = DB::select($sql, [$idUser]);
            }
            
            return array("", $locations[0]->id, $locations[0]->idEmpresa);
        }

        return array("", "0", 1);
    }

    public function runReport()
    {
        $sql = 'SELECT MONTH(RVC.fecha) mes, SUM(RVC.netSales) total, SUM(IF( RVC.rvc IN ("Salon","RVC 1", "Restaurant") , RVC.netSales , 0)) AS Salon, SUM(IF( RVC.rvc IN ("Vitrina","RVC 2") , RVC.netSales , 0)) AS Vitrina, SUM(IF( RVC.rvc IN ("Institucional","RVC 3") , RVC.netSales , 0)) AS Catering,  SUM(IF( RVC.rvc IN ("Servicio Domicilio","RVC 4","Servicio a Domicilio") , RVC.netSales , 0)) AS Delivery  FROM vds_rvc RVC INNER JOIN sucursales S ON RVC.idSucursal = S.id WHERE S.idEmpresa = ' . $this->company . ' AND S.id IN(' . $this->locationID . ') AND RVC.netSales >0 AND fecha BETWEEN ? AND ? GROUP BY MONTH(RVC.fecha);';
        $result = DB::select($sql, [$this->initDate, $this->endDate]);
        
        $lyinit = date("Y", strtotime($this->initDate . " -3 YEAR")) . "-01-01";
        $lyend = date("Y-m-t", strtotime(date("Y-m-d") . " -3 YEAR"));

        $sql = 'SELECT MONTH(RVC.fecha) mes, SUM(RVC.netSales) AS total FROM vds_rvc RVC INNER JOIN sucursales S ON RVC.idSucursal = S.id WHERE S.idEmpresa = ' . $this->company . ' AND S.id IN(' . $this->locationID . ') AND RVC.netSales >0 AND fecha BETWEEN ? AND ? GROUP BY MONTH(RVC.fecha);';
        $ly = DB::select($sql, [$lyinit, $lyend]);
        $lyresult = array();

        $sql = 'SELECT MONTH(BGT.fecha) mes, SUM(BGT.monto*1.16) AS total FROM budget_mes_sucursal BGT INNER JOIN sucursales S ON BGT.idSucursal = S.id WHERE S.idEmpresa = ' . $this->company . ' AND S.id IN(' . $this->locationID . ')  AND BGT.fecha BETWEEN ? AND ? GROUP BY MONTH(BGT.fecha);';
        $budget = DB::select($sql, [$this->initDate, $this->endDate]);
        $bgtresult = array();
        foreach ($budget as $bgt) {
            $bgtresult[$bgt->mes] = $bgt->total;
        }

        foreach ($ly as $venta) {
            $lyresult[$venta->mes] = $venta->total;
        }

        $this->result = array("Current" => $result, "LY" => $lyresult, "Budget" => $bgtresult, "lyend" => $lyend, "lyinit" => $lyinit);
    }

    public function getResult($type)
    {
        if ($type == "xlsx") {
            $this->exportReport();
        } else {

            $parser = new ReportParser($type);

            $tmpArray = array();            
            
            foreach ($this->result["Current"] as $data) {
                
                if(empty($this->result["Budget"][$data->mes]))
                    $this->result["Budget"][$data->mes] = 0;
                if(empty($this->result["LY"][$data->mes]))
                    $this->result["LY"][$data->mes] = 0;

                $tmpArray[]  = array("Month" => $this->meses[$data->mes],  "Budget" => ceil($this->result["Budget"][$data->mes]), "total" => $data->total, "LY" => ceil($this->result["LY"][$data->mes]), "Sales" => array("Salon" => ceil($data->Salon), "Vitrina" => ceil($data->Vitrina), "Catering" => ceil($data->Catering), "Delivery" => ceil($data->Delivery)));
            }

            return $parser->parse($tmpArray);

        }
    }

    public function exportReport()
    {
    }
}