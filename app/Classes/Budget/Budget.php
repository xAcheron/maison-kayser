<?php

namespace App\Classes\Budget;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Budget
{
    private $idLocation;
    private $fecha;

    public function __construct($idLocation, $fecha) {
        $this->idLocation = $idLocation;
        $this->fecha = $fecha;
    }

    public function setLocation($idLocation){
        $this->idLocation = $idLocation;
    }

    public function getMonth()
    {
        $sql = "SELECT * FROM budget_mes_sucursal A WHERE A.idSucursal=? AND A.fecha=?;";
        $budget = DB::select($sql, [$this->idLocation, $this->fecha]);
        echo "$this->idLocation, $this->fecha";
        return $budget[0];
    }

    public function getDaily()
    {
        $sql = "SELECT * FROM budget_dia_sucursal B WHERE B.idSucursal=? AND MONTH(B.fecha)=MONTH(?) AND YEAR(B.fecha)=YEAR(?);";
        $budget = DB::select($sql, [$this->idLocation, $this->fecha, $this->fecha]);
    }
    public function redistributeBuget()
    {
        $evalDate = date("Y-m-d");
        $evalDate = date("Y-m-d", strtotime($evalDate." -".(date("N")-1)." day"));
        echo "<h1>".$this->idLocation."</h1>";
        echo "<h2>".$evalDate."</h2>";
        $arrayDate = explode("-",$evalDate);
        $days = cal_days_in_month(CAL_GREGORIAN, date("m",strtotime($evalDate)), date("Y",strtotime($evalDate)));
        $percent = $this->getDailyPercent();

        $currentDay = date("d",strtotime($evalDate));
        $pendingDays = $days-$currentDay+1;

        $perDay = array();
        $perDay[1] = $percent->LPer;
        $perDay[2] = $percent->MPer;
        $perDay[3] = $percent->MrPer;
        $perDay[4] = $percent->JPer;
        $perDay[5] = $percent->VPer;
        $perDay[6] = $percent->SPer;
        $perDay[7] = $percent->DPer;

        if($pendingDays < 7 )
        {
            echo "Menos dias en esta semana"."<br>";
            $pendingDays;
            $pendingPer=0;
            for($i=1;$i<=$pendingDays;$i++){
                $pendingPer += $perDay[$i];
            }
            echo $days ."<br>";
            echo $pendingDays . "=" . $days."-".$currentDay ."<br>";
            echo $pendingPer."<br>";

            for($i=1;$i<=7;$i++){
                if($i<=$pendingDays)
                    if($pendingPer>0)
                        $perDay[$i] = $perDay[$i]/$pendingPer;
                    else
                        $perDay[$i]=0;
                else
                    $perDay[$i]=0;
            }
            
        }
       
        $numDay = array();
        $numDay[1] = 0;
        $numDay[2] = 0;
        $numDay[3] = 0;
        $numDay[4] = 0;
        $numDay[5] = 0;
        $numDay[6] = 0;
        $numDay[7] = 0;
        
        $refDay = date( "d",strtotime($evalDate));
        $refDay = intval($refDay);
        for($i=$refDay;$i<=$days;$i++){
            $currentDay = ($i<10?"0":"").$i;
            $cTDay = date("N",strtotime($arrayDate[0]."-".$arrayDate[1]."-".$currentDay));
            $numDay[$cTDay] += 1;
        }

        $realPerDay = array();
        $realPerDay[1] = !empty($numDay[1])? $perDay[1]/ $numDay[1]: 0;
        $realPerDay[2] = !empty($numDay[2])? $perDay[2]/ $numDay[2]: 0;
        $realPerDay[3] = !empty($numDay[3])? $perDay[3]/ $numDay[3]: 0;
        $realPerDay[4] = !empty($numDay[4]) ? $perDay[4] / $numDay[4]: 0;
        $realPerDay[5] = !empty($numDay[5])? $perDay[5]/ $numDay[5]: 0;
        $realPerDay[6] = !empty($numDay[6])? $perDay[6]/ $numDay[6]: 0;
        $realPerDay[7] = !empty($numDay[7])? $perDay[7]/$numDay[7]: 0;

        echo "% por día";
        echo "<br>";
        echo "Lunes: ".$perDay[1]."<br>";
        echo "Martes: ".$perDay[2]."<br>";
        echo "Miercoles: ".$perDay[3]."<br>";
        echo "Jueves: ".$perDay[4]."<br>";
        echo "Viernes: ".$perDay[5]."<br>";
        echo "Sabado: ".$perDay[6]."<br>";
        echo "Domingo: ".$perDay[7]."<br>";

        echo "numero de dias";
        echo "<br>";
        echo "Lunes: ".$numDay[1]."<br>";
        echo "Martes: ".$numDay[2]."<br>";
        echo "Miercoles: ".$numDay[3]."<br>";
        echo "Jueves: ".$numDay[4]."<br>";
        echo "Viernes: ".$numDay[5]."<br>";
        echo "Sabado: ".$numDay[6]."<br>";
        echo "Domingo: ".$numDay[7]."<br>";

        echo "% Real ajustado por día";
        echo "<br>";
        echo "Lunes: ".$realPerDay[1]."<br>";
        echo "Martes: ".$realPerDay[2]."<br>";
        echo "Miercoles: ".$realPerDay[3]."<br>";
        echo "Jueves: ".$realPerDay[4]."<br>";
        echo "Viernes: ".$realPerDay[5]."<br>";
        echo "Sabado: ".$realPerDay[6]."<br>";
        echo "Domingo: ".$realPerDay[7]."<br>";

        /*$sql = "SELECT * FROM         
        (SELECT idSucursal, SUM(budget) AS budget FROM budget_dia_sucursal_o WHERE idSucursal = ? AND fecha< ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?) GROUP BY idSucursal) AS budget
        LEFT JOIN 
        (SELECT idSucursal, SUM(netSales)/1.16 AS venta FROM venta_diaria_sucursal WHERE idSucursal = ? AND fecha< ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?) GROUP BY idSucursal) AS venta
        ON venta.idSucursal= budget.idSucursal";*/


        $sql = "SELECT * FROM         
        (SELECT idSucursal, SUM(budget) AS budget , SUM(IF(fecha < ?,budget,0)) cbudget FROM budget_dia_sucursal_o WHERE idSucursal = ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?) GROUP BY idSucursal) AS budget
        LEFT JOIN 
        (SELECT idSucursal, SUM(netSales)/1.16 AS venta FROM venta_diaria_sucursal WHERE idSucursal = ? AND fecha< ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?) GROUP BY idSucursal) AS venta
        ON venta.idSucursal= budget.idSucursal";


        $results = DB::select($sql, [$evalDate,$this->idLocation,$evalDate,$evalDate,$this->idLocation,$evalDate,$evalDate,$evalDate]);
        if(!empty($results[0])){
            echo "".$results[0]->cbudget ." > ".$results[0]->venta."<BR>";
            
            if($results[0]->cbudget > $results[0]->venta)
            {

                $budget = $results[0];

                echo "Budget $ ".$budget->budget." <BR>";

                $budget = $budget->budget - $results[0]->venta;

                echo "Budget restante $ ".$budget." <BR>";

                //echo "<table><tr><td>Día</td><td>Budget</td></tr>";
                $budgetSql="";
                $sqlInsertBudget = "REPLACE INTO budget_dia_sucursal VALUES ";
                echo "Reference day: ".$refDay." <BR>";
                $budgetRemainTotal = $results[0]->budget - $results[0]->venta;
                echo "Budget Remain Total: ".$budgetRemainTotal." <BR>";
                $budgetSql="";
                $sqlInsertBudget = "REPLACE INTO budget_dia_sucursal VALUES ";
                for($i=$refDay;$i<=$days;$i++){
                    $currentDay = ($i<10?"0":"").$i;	
                    $cTDay = date("N",strtotime($arrayDate[0]."-".$arrayDate[1]."-".$currentDay));
                    if(!empty($budgetSql))
                        $budgetSql.= ", ";
                    $budgetSql .="(".$this->idLocation.",'".$arrayDate[0]."-".$arrayDate[1]."-".$currentDay."',".($budgetRemainTotal*$realPerDay[$cTDay]).",'".date("Y-m-d H:i:s")."')";
                }
                echo $sqlInsertBudget . $budgetSql;
                echo DB::insert($sqlInsertBudget . $budgetSql);

                /*
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=2 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                echo ($budgetRemainTotal*$realPerDay[1])."-".$budgetSql."<BR>";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[1],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=3 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[2],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[2])."-".$budgetSql."<BR>";
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=4 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[3],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[3])."-".$budgetSql."<BR>";
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=5 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[4],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[4])."-".$budgetSql."<BR>";
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=6 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[5],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[5])."-".$budgetSql."<BR>";
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=7 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[6],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[6])."-".$budgetSql."<BR>";
                $budgetSql = "UPDATE budget_dia_sucursal SET budget = budget + ?, setDate = ? WHERE idSucursal = ".$this->idLocation." AND DAYOFWEEK(fecha)=1 AND fecha >= ? AND MONTH(fecha) = MONTH(?) AND YEAR(fecha) = YEAR(?)";
                DB::update($budgetSql,[$budgetRemainTotal*$realPerDay[7],date("Y-m-d"),$evalDate,$evalDate,$evalDate]);
                echo ($budgetRemainTotal*$realPerDay[7])."-".$budgetSql."<BR>";
                */  

            }
            else
            {
                echo "Nothing to do";
            }
        }
        else
        {
            echo "Nothing to do, we doesnt have information about budget and sales";
        }

    }

    public function distributeBuget()
    {
        $evalDate = $this->fecha;
        $arrayDate = explode("-",$evalDate);
        $days = cal_days_in_month(CAL_GREGORIAN, date("m",strtotime($evalDate)), date("Y",strtotime($evalDate)));
        $percent = $this->getDailyPercent();
        
        $perDay = array();
        $perDay[1] = $percent->LPer;
        $perDay[2] = $percent->MPer;
        $perDay[3] = $percent->MrPer;
        $perDay[4] = $percent->JPer;
        $perDay[5] = $percent->VPer;
        $perDay[6] = $percent->SPer;
        $perDay[7] = $percent->DPer;

        $numDay = array();
        $numDay[1] = 0;
        $numDay[2] = 0;
        $numDay[3] = 0;
        $numDay[4] = 0;
        $numDay[5] = 0;
        $numDay[6] = 0;
        $numDay[7] = 0;

        for($i=1;$i<=$days;$i++){
            $currentDay = ($i<10?"0":"").$i;
            $cTDay = date("N",strtotime($arrayDate[0]."-".$arrayDate[1]."-".$currentDay));
            $numDay[$cTDay] += 1;
        }

        $realPerDay = array();
        $realPerDay[1] = $perDay[1] / $numDay[1];
        $realPerDay[2] = $perDay[2] / $numDay[2];
        $realPerDay[3] = $perDay[3] / $numDay[3];
        $realPerDay[4] = $perDay[4] / $numDay[4];
        $realPerDay[5] = $perDay[5] / $numDay[5];
        $realPerDay[6] = $perDay[6] / $numDay[6];
        $realPerDay[7] = $perDay[7] /$numDay[7];

        echo "% Real por día Agosto";
        echo "<br>";
        echo "Lunes: ".$realPerDay[1]."<br>";
        echo "Martes: ".$realPerDay[2]."<br>";
        echo "Miercoles: ".$realPerDay[3]."<br>";
        echo "Jueves: ".$realPerDay[4]."<br>";
        echo "Viernes: ".$realPerDay[5]."<br>";
        echo "Sabado: ".$realPerDay[6]."<br>";
        echo "Domingo: ".$realPerDay[7]."<br>";

        $budget = $this->getMonth();

        $budget = $budget->monto;

        echo "Budget Agosto $ ".$budget." <BR>";

        //echo "<table><tr><td>Día</td><td>Budget</td></tr>";
        $budgetSql="";
        $sqlInsertBudget = "REPLACE INTO budget_dia_sucursal VALUES ";
        for($i=1;$i<=$days;$i++){
            $currentDay = ($i<10?"0":"").$i;	
            $cTDay = date("N",strtotime($arrayDate[0]."-".$arrayDate[1]."-".$currentDay));
            if(!empty($budgetSql))
                $budgetSql.= ", ";
            $budgetSql .="(".$this->idLocation.",'".$arrayDate[0]."-".$arrayDate[1]."-".$currentDay."',".($budget*$realPerDay[$cTDay]).",'".date("Y-m-d H:i:s")."')";
            //echo "<tr><td>".$arrayDate[0]."-".$arrayDate[1]."-".$currentDay."</td><td>".($budget*$realPerDay[$cTDay])."</td></tr>";
        }
        $sqlInsertBudget = $sqlInsertBudget.$budgetSql.";";
        echo $sqlInsertBudget;
        echo DB::insert($sqlInsertBudget);
        $sqlInsertBudget = "REPLACE INTO budget_dia_sucursal_o VALUES ";
        $sqlInsertBudget = $sqlInsertBudget.$budgetSql.";";
        echo $sqlInsertBudget;
        echo DB::insert($sqlInsertBudget);
        //echo "</table>";
    }

    private function getDailyPercent()
    {
        $sql = "SELECT VTS.netSales, VTS.L, VTS.M, VTS.Mr, VTS.J, VTS.V, VTS.S, VTS.D, 
            VTS.L/VTS.netSales AS LPer,VTS.M/VTS.netSales AS MPer,VTS.Mr/VTS.netSales AS MrPer,VTS.J/VTS.netSales AS JPer,VTS.V/VTS.netSales AS VPer,VTS.S/VTS.netSales AS SPer,VTS.D/VTS.netSales AS DPer
            FROM (SELECT SUM(S.netSales) netSales, SUM(IF(DAYOFWEEK(fecha)=2,S.netSales,0))AS L, SUM(IF(DAYOFWEEK(fecha)=3,S.netSales,0))AS M, SUM(IF(DAYOFWEEK(fecha)=4,S.netSales,0))AS Mr, SUM(IF(DAYOFWEEK(fecha)=5,S.netSales,0))AS J, SUM(IF(DAYOFWEEK(fecha)=6,S.netSales,0))AS V, SUM(IF(DAYOFWEEK(fecha)=7,S.netSales,0))AS S, SUM(IF(DAYOFWEEK(fecha)=1,S.netSales,0))AS D FROM venta_diaria_sucursal S INNER JOIN sucursales SL ON S.idSucursal = SL.id WHERE SL.estado = 1 AND S.idSucursal = ? AND (fecha BETWEEN ? AND ? ) GROUP BY YEAR(fecha)) VTS;";

        $currentDate = $this->fecha;
        $endDate = date("Y-m-d", strtotime( $currentDate." -1 day" ));
        $startDate = date("Y-m-d", strtotime( $currentDate." -2 month" ));
        $percent = DB::select($sql,[$this->idLocation, $startDate, $endDate]);
        return $percent[0];
    }
    
}