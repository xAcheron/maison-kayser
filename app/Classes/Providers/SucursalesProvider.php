<?php

namespace App\Classes\Providers;

use Illuminate\Support\Facades\DB;

class SucursalesProvider
{   
    public $sucursales;

    public function __construct($idCompany=[], $status=0){
        if(!empty($idCompany) && $status!=0){
            $this->getAll($idCompany, $status);
        }
    }

    public function getAll($companies=[1], $status=1, $franquicia=0) {
        //TODO: Implements idTipo Column
        $idCompany = implode(',',$companies);
        echo $idCompany;
        $sql = "SELECT id, idSap, idMicros, idEMC, idEmpresa, nombre FROM sucursales WHERE idEmpresa IN ($idCompany) AND estado = ? AND franquicia = ? AND NOT(idTipo = 0) ORDER BY id ASC";
        $this->sucursales = DB::select($sql,[$status,$franquicia]);        
    }

}