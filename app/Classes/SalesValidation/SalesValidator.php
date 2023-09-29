<?php

namespace App\Classes\SalesValidation;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\SalesValidation\MailProvider;
use App\Classes\Providers\SucursalesProvider;


class SalesValidator
{

    private $receiver;
    private $criteria;
    private $companies;
    private $path;

    public function __construct($server="",$user="",$password="", $criteria="",$companies=[1]) {
        $fecha = date("j F Y");
        if(!empty($criteria))
            $this->criteria = 'SUBJECT "'.$criteria.'" ON "'.$fecha.'"';
        else
            $this->criteria = 'SUBJECT "Ventas CGH" ON "'.$fecha.'"';
        //$this->criteria = 'SUBJECT "Venta Mes Acumulado EKM x dia" ON "01 Abr 2022"';
        echo $this->criteria;

        $this->companies = $companies;
        $this->receiver = new MailProvider($server,$user,$password,$this->criteria);        
    }

    public function validate()
    {
        $differences = array();
        $messages = $this->receiver->getMails($this->criteria);
        if($messages !== false)
            if(count($messages)>0)
            {
                //echo "Messages found: " . count($messages);
                //echo "<br>";
                $path = $this->receiver->getFileFromMail($messages[0]);

                if(!empty($path))
                {
                    $spreadsheet = IOFactory::load($path);
                    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    echo "<br>";
                    echo "MAX ROWS: <b>".count($sheetData)."</b>";
                    $fechas = explode(" - ",$sheetData[4]["B"]);
                    $nfechas = count($fechas);
                    if($nfechas>0)
                    {
                        $sucursales = new SucursalesProvider($this->companies,1);

                        $fechaTmp = explode("/",$fechas[0]);

                        if($this->companies[0]==4)
                            $fechaIni = $fechaTmp[2]."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1])."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0]);
                        else
                            $fechaIni = $fechaTmp[2]."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0])."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1]);                            

                        $fechaFin ="";

                        if($nfechas == 1)
                        {
                            $fechaFin = $fechaIni;
                        }
                        else
                        {
                            $fechaTmp = explode("/",$fechas[1]);
                            if($this->companies[0]==4)
                                $fechaFin = $fechaTmp[2]."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1])."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0]);
                            else
                                $fechaFin = $fechaTmp[2]."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0])."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1]);
                        }

                        $allLocationsRow = -1;
// || $i<10
                        for($i=1;$allLocationsRow == -1;$i++){
                           if($sheetData[$i]["A"]=="Total")
                                $allLocationsRow = $i;
                            else if($sheetData[$i]["A"]=="Location Name" && $this->companies[0]==4)
                                $allLocationsRow = $i+1;
                            /*if($sheetData[$i]["A"]=="Total")
                                $allLocationsRow = $i;*/
                        }
                        //echo "<br>";
                        //echo $allLocationsRow;
                        $storesInitRow= $allLocationsRow+1;
                        if($allLocationsRow > -1)
                        {
                            $allLocationsTotal = (double)str_replace(',','',$sheetData[$allLocationsRow]["C"]);
                            echo "<br>";
                            echo "<B>TOTAL:</B> ".$allLocationsTotal;
                        }

                        $sql = "SELECT MONTH(vds.fecha), SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idEmpresa IN (".implode(',',$this->companies).") AND vds.fecha BETWEEN ? AND ? GROUP BY MONTH(vds.fecha);";
                        echo "<br>";
                        echo $fechaIni. " - " . $fechaFin;
                        #echo "<br>";
                        #echo $sql;
                        $dbTotals = DB::select($sql , [$fechaIni,$fechaFin]);
                        echo $dbTotals[0]->netSales;
                        echo "<br>";
                        $tmpStore = "";
                        $tmpStoreTotal = 0;

                        if(!empty($dbTotals[0]))
                        {
                            $dbTotal = $dbTotals[0]->netSales;
                            echo "<br>";
                            echo "<b>dbTotal:</b>".$dbTotal;
                            echo "<br>";
                            if($dbTotal != $allLocationsTotal)
                            {
                                //echo "Difference: " . ($allLocationsTotal - $dbTotal);
                                $tmpStore =$sheetData[$storesInitRow]["A"];
                                $rowInitStore = $storesInitRow;
                                for($i=$storesInitRow; !empty($sheetData[$i]["A"]) ;$i++)
                                {
                                    
                                    if(!empty($tmpStore) && $tmpStore != $sheetData[$i]["A"])
                                    {
                                        
                                        $rowEndStore = $i-1;

                                        $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$this->companies).") AND vds.fecha BETWEEN ? AND ? GROUP BY vds.idSucursal;";
                                        $storeDbTotals = DB::select($sql,[$tmpStore,$fechaIni,$fechaFin]);

                                        $dbTotal=0;

                                        if(!empty($storeDbTotals[0]))
                                        {
                                            $dbTotal = $storeDbTotals[0]->netSales;
                                        }
                                        
                                        $storeDifference = $tmpStoreTotal - $dbTotal;
                                        
                                        if($storeDifference > 1 || $storeDifference < -1)
                                        {
                                            for($j=$rowInitStore; !empty($sheetData[$j]) && preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/i', $sheetData[$j]["B"])!==0 && $j<=$rowEndStore;$j++)
                                            {
                                                $fechaTmp = explode("/",$sheetData[$j]["B"]);
                                                if($this->companies[0]==4)
                                                    $fechaEval = $fechaTmp[2]."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1])."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0]);
                                                else                                                
                                                    $fechaEval = $fechaTmp[2]."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0])."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1]);
                                                
                                                $locationDateTotal = (double)str_replace(',','',$sheetData[$j]["C"]);
                                                
                                                $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$this->companies).") AND vds.fecha = ? GROUP BY vds.idSucursal;";
                                                $dateDbTotals = DB::select($sql,[$tmpStore,$fechaEval]);
                                                $dateTotal = 0;
                                                
                                                if(!empty($dateDbTotals[0]))
                                                {
                                                    $dateTotal = $dateDbTotals[0]->netSales;
                                                }
                                                
                                                //echo "<br>";
                                                //echo $fechaEval;

                                                $dateDifference = $locationDateTotal - $dateTotal;

                                                if($dateDifference > 1 || $dateDifference < -1)
                                                {
                                                    //echo "<br>";
                                                    //echo $locationDateTotal;
                                                    //echo "<br>";
                                                    //echo $dateTotal;
                                                    //echo "<br>";
                                                    //echo "Difference: " .$dateDifference;

                                                    $differences[] = array($tmpStore,$fechaEval,$dateDifference);
                                                }

                                            }
                                        }


                                        $tmpStore =$sheetData[$i]["A"];
                                        $tmpStoreTotal = 0;  
                                        $rowInitStore = $i;  
                                    }

                                    $tmpStoreTotal += (double)str_replace(',','',$sheetData[$i]["C"]);

                                    /*
                                    if(preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/i', $sheetData[$i]["A"])==false)
                                    {
                                        $store = $sheetData[$i]["A"];
                                        $locationTotal = (double)str_replace(',','',$sheetData[$i]["C"]);

                                        $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$companies).") AND vds.fecha BETWEEN ? AND ? GROUP BY vds.idSucursal;";
                                        $storeDbTotals = DB::select($sql,[$store,$fechaIni,$fechaFin]);
                                        
                                        $dbTotal=0;

                                        if(!empty($storeDbTotals[0]))
                                        {
                                            $dbTotal = $storeDbTotals[0]->netSales;
                                        }
                                        //echo "<br>";
                                        //echo $store;

                                        $storeDifference = $locationTotal - $dbTotal;

                                        if($storeDifference > 1 || $storeDifference < -1)
                                        {
                                            //echo "<br>";
                                            //echo $locationTotal;
                                            //echo "<br>";
                                            //echo $dbTotal;
                                            //echo "<br>";
                                            //echo "Difference: " .$storeDifference;

                                            for($j=$i+1; !empty($sheetData[$j]) && preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/i', $sheetData[$j]["A"])!==0;$j++)
                                            {
                                                $fechaTmp = explode("/",$sheetData[$j]["A"]);
                                                $fechaEval = $fechaTmp[2]."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0])."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1]);
                                                
                                                $locationDateTotal = (double)str_replace(',','',$sheetData[$j]["B"]);

                                                $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$companies).") AND vds.fecha = ? GROUP BY vds.idSucursal;";
                                                $dateDbTotals = DB::select($sql,[$store,$fechaEval]);
                                                $dateTotal = 0;
                                                
                                                if(!empty($dateDbTotals[0]))
                                                {
                                                    $dateTotal = $dateDbTotals[0]->netSales;
                                                }
                                                
                                                //echo "<br>";
                                                //echo $fechaEval;

                                                $dateDifference = $locationDateTotal - $dateTotal;

                                                if($dateDifference > 1 || $dateDifference < -1)
                                                {
                                                    //echo "<br>";
                                                    //echo $locationDateTotal;
                                                    //echo "<br>";
                                                    //echo $dateTotal;
                                                    //echo "<br>";
                                                    //echo "Difference: " .$dateDifference;

                                                    $differences[] = array($store,$fechaEval,$dateDifference);
                                                }
                                                else
                                                {
                                                    //echo ": Its OK.";
                                                }
                                            }

                                        }
                                        else
                                        {
                                            //echo ": Its OK.";
                                        }
  
                                    }
                                    */
                                }

                                if(!empty($tmpStore))
                                {
                                    $rowEndStore = $i-1;
                                    

                                    $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$this->companies).") AND vds.fecha BETWEEN ? AND ? GROUP BY vds.idSucursal;";
                                    $storeDbTotals = DB::select($sql,[$tmpStore,$fechaIni,$fechaFin]);

                                    $dbTotal=0;

                                    if(!empty($storeDbTotals[0]))
                                    {
                                        $dbTotal = $storeDbTotals[0]->netSales;
                                    }
                                    
                                    $storeDifference = $tmpStoreTotal - $dbTotal;
                                    
                                    if($storeDifference > 1 || $storeDifference < -1)
                                    {
                                        for($j=$rowInitStore; !empty($sheetData[$j]) && preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/i', $sheetData[$j]["B"])!==0 && $j<=$rowEndStore;$j++)
                                        {
                                            $fechaTmp = explode("/",$sheetData[$j]["B"]);
                                            $fechaEval = $fechaTmp[2]."-".($fechaTmp[0]<9?"0".$fechaTmp[0]:$fechaTmp[0])."-".($fechaTmp[1]<9?"0".$fechaTmp[1]:$fechaTmp[1]);
                                            
                                            $locationDateTotal = (double)str_replace(',','',$sheetData[$j]["C"]);
                                            
                                            $sql = "SELECT vds.idSucursal, SUM(vds.netSales) netSales FROM venta_diaria_sucursal vds INNER JOIN sucursales s ON s.id = vds.idSucursal WHERE s.idMicros = ? AND s.idEmpresa IN (".implode(',',$this->companies).") AND vds.fecha = ? GROUP BY vds.idSucursal;";
                                            $dateDbTotals = DB::select($sql,[$tmpStore,$fechaEval]);
                                            $dateTotal = 0;
                                            
                                            if(!empty($dateDbTotals[0]))
                                            {
                                                $dateTotal = $dateDbTotals[0]->netSales;
                                            }
                                            
                                            //echo "<br>";
                                            //echo $fechaEval;

                                            $dateDifference = $locationDateTotal - $dateTotal;

                                            if($dateDifference > 1 || $dateDifference < -1)
                                            {
                                                //echo "<br>";
                                                //echo $locationDateTotal;
                                                //echo "<br>";
                                                //echo $dateTotal;
                                                //echo "<br>";
                                                //echo "Difference: " .$dateDifference;

                                                $differences[] = array($tmpStore,$fechaEval,$dateDifference);
                                            }

                                        }
                                    }


                                    $tmpStore =$sheetData[$storesInitRow]["A"];
                                    $tmpStoreTotal = 0;  
                                    $rowInitStore = $i;  
                                }




                                //dd($differences);
                            }
                            else
                            {
                                //echo "All good!";
                            }
                        }
                        //dd($dbTotals);
                        //dd($sucursales->sucursales);
                    }
                    
                }
                return $differences;
            }
        else
            return [];
            //echo "nothing to do!!";
    }

}