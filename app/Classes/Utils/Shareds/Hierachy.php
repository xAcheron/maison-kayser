<?php

namespace App\Classes\Utils\Shareds;

use Illuminate\Support\Facades\DB;

class Hierachy
{
    public static function getHierachy($id)
    {

        if (!empty($id)) {
            $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";
            $empresas = DB::select($sql, [$id]);
            $hierachy = array();

            foreach ($empresas as $empresa) {
                $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1);
                if (session('RepRole') == 3)
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
                else
                    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
                $sucursales = DB::select($sql, [$empresa->idEmpresa]);

                foreach ($sucursales as $sucursal) {
                    $hierachy[] = array("id" => $sucursal->idMicros, "nombre" => $sucursal->nombre, "tipo" => 2);
                }
            }
            return $hierachy;
        } else {
            return "No se encontraron resultados";
        }
    }
}