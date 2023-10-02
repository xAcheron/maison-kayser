<?php

namespace App\Classes\Reports\utils;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserLocation
{
    private $parentLocation;
    public $locationName;
    public $locationID;
    public $locationSap;
    public $locationNombres;
    public $company;

    public function get($location = 0, $type = 0, $idUsuario = 1)
    {

        $this->parentLocation = $location;

        if (is_numeric($this->parentLocation) || $this->parentLocation == "All" || is_array($this->parentLocation)) {
            if (is_array($this->parentLocation)) {
                $locations = $this->getLocationsArray($this->parentLocation, empty($type) ? 1 : 0, $type, $idUsuario);
                $this->company = $locations[2];
            } else {
                $locations = $this->getLocations($this->parentLocation, $this->parentLocation == "All" ? 1 : 0, $idUsuario);
                $this->company = $this->parentLocation;
            }

            $this->locationName = $locations[0];
            $this->locationID = $locations[1];
            $this->locationSap = $locations[3];
            $this->locationNombres = $locations[4];
        } else {
            $tmpLocationInfo = $this->getLocation($this->parentLocation);
            $this->company = $tmpLocationInfo[2];
            $this->locationName = $tmpLocationInfo[0];
            $this->locationID = $tmpLocationInfo[1];
        }
    }

    public function getLocation($idLocation)
    {
        $sql = "SELECT * FROM sucursales WHERE idMicros = ?;";
        $locations = DB::select($sql, [$idLocation]);
        return array("'" . $locations[0]->idMicros . "'", $locations[0]->id,  $locations[0]->idEmpresa);
    }

    public function getLocations($idEmpresa, $all = 0, $idUsuario)
    {
        if ($all == 1)
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales INNER JOIN dashboard_empresa_usuario ON sucursales.idEmpresa = dashboard_empresa_usuario.idEmpresa  INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE sucursales.estado = 1 AND NOT(idCategoria IN (10)) AND sucursales.idTipo>0 AND idUsuario = ?;";
        else if (is_array($idEmpresa))
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales WHERE NOT(idCategoria IN (10)) AND sucursales.idTipo>0 AND nombre IN ('" . implode("','", $idEmpresa) . "');";
        else
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales WHERE idEmpresa = ? AND NOT(idCategoria IN (10)) AND sucursales.idTipo>0;";

        $locations = DB::select($sql, [is_array($idEmpresa) ? null : ($all == 1 ? (empty($idUsuario) ? Auth::id() : $idUsuario) : $idEmpresa)]);
        $locationArr = array();
        $locationIDArr = array();
        $locationSapArr = array();
        $locationNameArr = array();

        foreach ($locations as $location) {
            $locationArr[] = "'" . $location->idMicros . "'";
            $locationIDArr[] = $location->id;
            $locationSapArr[] = $location->idSap;
            $locationNameArr[] = $location->nombre;
        }
        return array(implode(",", $locationArr), implode(",", $locationIDArr), !empty($locations[0]) ? $locations[0]->idEmpresa : '', implode(",", $locationSapArr), $locationNameArr);
    }

    public function getLocationsArray($idEmpresa, $all = 0, $type, $idUsuario)
    {
        $data = implode("','", $idEmpresa);
        $sql = $type == 'E' ? "idEmpresa IN ('$data')" : ($type == 'C' ? "idTier IN ('$data')" : ($type == 'S' ? "id IN ('$data')" : ""));



        if ($all == 1)
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales INNER JOIN dashboard_empresa_usuario ON sucursales.idEmpresa = dashboard_empresa_usuario.idEmpresa  INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE sucursales.estado = 1 AND NOT(idCategoria IN (10)) AND sucursales.idTipo>0 AND idUsuario = ?;";
        else if (is_array($idEmpresa))
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales WHERE NOT(idCategoria IN (10)) AND sucursales.idTipo>0 AND estado != 0 AND $sql;";
        else
            $sql = "SELECT sucursales.id, sucursales.nombre, sucursales.idMicros, sucursales.idSap, sucursales.idEmpresa FROM sucursales WHERE idEmpresa = ? AND NOT(idCategoria IN (10)) AND sucursales.idTipo>0;";

        $locations = DB::select($sql, [$all == 1 ?  (empty($idUsuario) ? Auth::id() : $idUsuario) : null]);
        $locationArr = array();
        $locationIDArr = array();
        $locationSapArr = array();
        $locationNameArr = array();
        $locationEmpArr = array();

        foreach ($locations as $location) {
            $locationArr[] = "'" . $location->idMicros . "'";
            $locationIDArr[] = $location->id;
            $locationSapArr[] = $location->idSap;
            $locationNameArr[] = $location->nombre;
            if (!in_array($idEmpresa, $locationEmpArr)) {
                $locationEmpArr[] = $location->idEmpresa;
            }
        }
        return array(implode(",", $locationArr), implode(",", $locationIDArr),  implode(',', $locationEmpArr), implode(",", $locationSapArr), $locationNameArr);
    }

    public function getHierachy($onlyCompanies = 0, $idUsuario = 0)
    {
        $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";
        $empresas = DB::select($sql, [$idUsuario == 0 ? Auth::id() : $idUsuario]);
        $hierachy = array();

        if ($onlyCompanies == 0) {
            foreach ($empresas as $empresa) {
                if (session('RepRole') == 1)
                    $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1);
                if (session('RepRole') > 1)
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
                else
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
                $sucursales = DB::select($sql, [$empresa->idEmpresa]);

                foreach ($sucursales as $sucursal) {
                    $hierachy[] = array("id" => $sucursal->idMicros, "nombre" => $sucursal->nombre, "tipo" => 2);
                }
            }
        } else {
            foreach ($empresas as $empresa)
                $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1);
        }
        
        return $hierachy;
    }

    public function getHierachy2($onlyCompanies = 0)
    {
        $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";
        //$empresas = DB::select($sql, [Auth::id()]);
        $empresas = DB::select($sql, [1]);
        $hierachy = array();

        if ($onlyCompanies == 0) {
            foreach ($empresas as $empresa) {
                if (session('RepRole') == 1)
                    $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1, 'clas' => 'E');
                $sql = "SELECT  * FROM sucursales_tier WHERE idEmpresa = $empresa->idEmpresa AND estado = 1;";
                $tiers = DB::select($sql);
                foreach ($tiers as $key => $tier) {
                    $hierachy[] = array("id" => $tier->idTier, "nombre" => $tier->tier, "tipo" => 1, 'clas' => 'C');
                    if (session('RepRole') > 1)
                        $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                    else
                        $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                    $sucursales = DB::select($sql, [$empresa->idEmpresa, $tier->idTier]);

                    foreach ($sucursales as $sucursal) {
                        $hierachy[] = array("id" => $sucursal->id, "nombre" => $sucursal->nombre, "tipo" => 2, 'clas' => 'S');
                    }
                }



                if (session('RepRole') > 1)
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                else
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                $sucursales = DB::select($sql, [$empresa->idEmpresa, 0]);

                if (!empty($sucursales)) {
                    $hierachy[] = array("id" => 0, "nombre" => 'Sin Categoria', "tipo" => 1, 'clas' => 'SC');
                }

                foreach ($sucursales as $sucursal) {
                    $hierachy[] = array("id" => $sucursal->id, "nombre" => $sucursal->nombre, "tipo" => 2, 'clas' => 'S');
                }
            }
        } else {
            foreach ($empresas as $empresa)
                $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1, 'clas' => 'E   ');
        }
        //dd($hierachy);
        return $hierachy;
    }

    public function getHierachy3($onlyCompanies = 0)
    {
        $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";
        //$empresas = DB::select($sql, [Auth::id()]);
        $empresas = DB::select($sql, [1]);
        $hierachy = array();

        if ($onlyCompanies == 0) {
            foreach ($empresas as $empresa) {
                if (session('RepRole') == 1)
                    $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1, 'clas' => 'E');
                $sql = "SELECT  * FROM sucursales_tier WHERE idEmpresa = $empresa->idEmpresa AND estado = 1;";
                $tiers = DB::select($sql);
                foreach ($tiers as $key => $tier) {                    
                    $hierachy[] = array("id" => $tier->idTier, "nombre" => $tier->tier, "tipo" => 1, 'clas' => 'C');
                    //dd($hierachy, 'aver');
                    if (session('RepRole') > 1)
                        $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                    else
                        $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                    $sucursales = DB::select($sql, [$empresa->idEmpresa, $tier->idTier]);

                    foreach ($sucursales as $sucursal) {
                        $hierachy[] = array("id" => $sucursal->id, "nombre" => $sucursal->nombre, "tipo" => 2, 'clas' => 'S');
                    }
                }



                if (session('RepRole') > 1)
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                else
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 AND idTier = ? ORDER BY nombre;";
                $sucursales = DB::select($sql, [$empresa->idEmpresa, 0]);

                if (!empty($sucursales)) {
                    $hierachy[] = array("id" => 0, "nombre" => 'Sin Categoria', "tipo" => 1, 'clas' => 'SC');
                }

                foreach ($sucursales as $sucursal) {
                    $hierachy[] = array("id" => $sucursal->id, "nombre" => $sucursal->nombre, "tipo" => 2, 'clas' => 'S');
                }
            }
        } else {
            foreach ($empresas as $empresa)
                $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1, 'clas' => 'E   ');
                dd($hierachy, 'caso 2');
        }
        //dd($hierachy);
        return $hierachy;
    }
}
