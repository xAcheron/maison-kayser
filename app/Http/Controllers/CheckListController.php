<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use App\User;
use Exception;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use PDO;

class CheckListController extends Controller
{

    private $NDB = null;


    public function __construct()
    {
        $this->middleware('auth', ['except' => ['endpoint', 'guardarItemsCheckSomosHGPrueba', 'getTipoValor', 'buscarSucursal', 'sendEmail', 'getDataRangeCal', 'getDataImageToCheck', 'getSucursalesByChecks', 'getExtrasCheck', 'getItemsCheckBySub', 'getDataCheck', 'getDataChecksBySuc', 'guardarItemsCheck', 'guardarExtCheck', 'guardarCheckInfo', 'getDataCheckById', 'getDataCheckListSecIds', 'getDataCheckListSec', 'getItemsCheck', 'getDataChecksList', 'getChecksByUser', 'getSucForIdUser', 'getGerentes', 'getExtras', 'getSucursales', 'getResponsable', 'getItemsVitrinaProExt', 'getItemsVitrinaProInt', 'getPuntajeTotalVp', 'guardarCheckVp', 'getAllCheckVp', 'getDetailsVtInt', 'getDetailsVtExt', 'getInfoCheck', 'getFinallyIdCheck', 'saveExtrasCheck', 'getDataCheckListSecWhitName', 'guardarItemsCheckSomosHG', 'guardarPuntajeFinal', 'guardarChecklist', 'guardarItemsCheckSomosHGPrueba2', 'getListEvaluaciones', 'reporteCheck', 'reporteChecklist', 'checkCount', 'visualizarChecklist']]);
        $this->NDB = DB::connection('mysql_new');
    }

    public function buscarSucursal(Request $request)
    {
        if ($request->input('api') == 1) {
            $idUsuario = Auth::id();
        } else {
            $idUsuario = $request->input('idUsuario');
        }
        $usuario = User::find($idUsuario);
        $idEmpresa = $usuario->idEmpresa;
        $sql = "SELECT idRole FROM config_app_access WHERE idUsuario = ? AND idAplicacion = 18;";
        $config = DB::select($sql, [$idUsuario]);
        if ($config[0]->idRole == 1) {
            $sql = "SELECT s.id idSucursal, s.nombre Sucursal FROM sucursales s WHERE s.estado=1 AND s.idEmpresa =$idEmpresa";
            $sucursales = DB::select($sql);
        } else {
            $sql = "SELECT psu.idSucursal, s.nombre Sucursal FROM checklist_sucursal_usuario psu INNER JOIN sucursales s ON s.id=psu.idSucursal WHERE idUsuario = ? AND s.estado=1 AND s.idEmpresa =$idEmpresa GROUP BY idUsuario,psu.idSucursal, s.nombre;";
            $sucursales = DB::select($sql, [$idUsuario]);
        }

        if (!empty($sucursales)) {
            return $sucursales;
        } else {
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => "La busqueda no arrojo resultados"
            ]);
        }
    }

    public function getDataRangeCal(Request $request)
    {
        $idTypeCheck = $request->input('idTypeCheck');
        if (!empty($idTypeCheck)) {
            $dataRange = DB::select("SELECT cal_m, cal_b FROM checklist_info WHERE idCheckList =", [$idTypeCheck]);
            return response()->json($dataRange);
        }
    }

    public function getTipoValor(Request $request)
    {
        $tipoValor = DB::select("SELECT * FROM checklist_tipo_valor");
        return response()->json($tipoValor);
    }

    public function getItemsCheck(Request $request)
    {
        $idSub = $request->input('idSub');
        $user = $request->input('idUsuario');

        $itemsVitriProExt = DB::select("SELECT T2.idSubseccion,T2.idItem, T2.idSb,T2.accion,T2.critico, T2.idTipo, T2.puntaje, T2.critico, T2.comentario as reqCom, T2.edo, T2.images as reqImg
            FROM  checklist_items AS T2
            WHERE T2.idSubseccion = ? AND estado = 1 ORDER BY orden ASC", [$idSub]);

        return response()->json($itemsVitriProExt);
    }

    public function getDataCheckListSec(Request $request)
    {
        $idTypeCheck = $request->input('idTypeCheck');
        if (!empty($idTypeCheck)) {
            $queryGetData = 'SELECT nombre FROM checklist_sub_info WHERE idSub = ? AND status = 1';
            $data = DB::select($queryGetData, [$idTypeCheck]);
            return $data;
        }
    }

    public function getDataCheckListSecIds(Request $request)
    {
        $idTypeCheck = $request->input('idTypeCheck');
        if (!empty($idTypeCheck)) {
            $queryGetData = 'SELECT idSub FROM checklist_sub_info WHERE idCheck = ? AND status = 1';
            $data = DB::select($queryGetData, [$idTypeCheck]);
            return $data;
        }
    }
    // TODO: Funcion contemplada en pedidos_app
    public function getDataCheckListSecWhitName(Request $request)
    {

        $idTypeCheck = $request->input('idTypeCheck');
        $idTypeCheckNum = $request->input('idTypeCheckNum') ?? 0;
        $idUsuario = $request->input('idUsuario');
        if (!empty($idTypeCheck)) {
            // $queryGetData = 'SELECT idSubseccion,idSub, nombre FROM checklist_sub_info WHERE idCheck = ? OR idCheckNum = ?';
            // $data = DB::select($queryGetData, [$idTypeCheck, $idTypeCheckNum]);


            // if($idUsuario == 1){

            $queryGetData = 'SELECT idSubseccion,idSubseccion as idSub, nombre FROM checklist_sub_info WHERE (idCheck = ? OR idCheckNum = ?) AND status = 1';
            $data = DB::select($queryGetData, [$idTypeCheck, $idTypeCheckNum]);

            foreach ($data as $sub) {
                $sub->idSub = strval($sub->idSub);
            }

            // }
            return $data;
        }
    }

    public function getDataChecksList(Request $request)
    {
        $idUser = $request->input('idUser');
        $_token = $request->input('_token');

        if (!empty($idUser) && !empty($_token)) {
            $data = DB::table('checklist_asignacion AS T1')
                ->join('checklist_info AS T2', 'T1.idCheck', '=', 'T2.idCheckList')
                ->select('T2.id', 'T2.idCheckList', 'T2.nombre', 'T2.puntaje_total')
                ->where('T1.idUser', '=', $idUser)
                ->where('T2.status', 1)
                ->get();
        }
        return $data;
    }

    public function getDataCheckById(Request $request)
    {
        $idTypeCheck = $request->input('idTypeCheck');
        if (!empty($idTypeCheck)) {
            $queryGetData = 'SELECT nombre, puntaje_total,cal_m, cal_b FROM checklist_info WHERE idCheckList = ?';
            $data = DB::select($queryGetData, [$idTypeCheck]);
            return $data;
        }
    }

    public function getFinallyIdCheck()
    {
        $idFinally = DB::select('SELECT MAX(idCheckList) AS idCheckList from checklist_generados');
        return $idFinally[0]->idCheckList;
    }

    public function saveExtrasCheck(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idCheck = $json_strings->idCheckList;
        $comentarios = $json_strings->comentarios;
        $imagenes = $json_strings->nombres_imagenes;

        DB::insert('INSERT INTO checklist_generados_extras (idCheckList, comentarios, nombres_imagenes) values (?, ?, ?)', [$idCheck, $comentarios, $imagenes]);
    }

    public function getExtras(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idChecK = $json_strings->idCheck;

        $extrasAll = DB::select("SELECT idCheckList, comentarios, nombres_imagenes FROM checklist_generados_extras WHERE idCheckList = ?", [$idChecK]);
        $itemsjson = json_encode($extrasAll, JSON_PRETTY_PRINT);

        return $itemsjson;
    }

    public function getDetailsVtInt(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idChecK = $json_strings->idCheck;

        $itemsAll = DB::select("SELECT idPregunta,estado,accion,critico FROM checklist_generados_detalles INNER JOIN checklist_items WHERE (checklist_generados_detalles.idPregunta = checklist_items.idItem) AND (checklist_generados_detalles.idTipoCheck = ?) AND (checklist_generados_detalles.idCheckList= ?)", ["VT-Int", $idChecK]);
        $itemsjson = json_encode($itemsAll, JSON_PRETTY_PRINT);

        return $itemsjson;
    }

    public function getDetailsVtExt(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idChecK = $json_strings->idCheck;

        $itemsAll = DB::select("SELECT idPregunta,estado,accion,critico FROM checklist_generados_detalles INNER JOIN checklist_items WHERE (checklist_generados_detalles.idPregunta = checklist_items.idItem) AND (checklist_generados_detalles.idTipoCheck = ?)  AND (checklist_generados_detalles.idCheckList= ?)", ["VT-Ext", $idChecK]);
        $itemsjson = json_encode($itemsAll, JSON_PRETTY_PRINT);

        return $itemsjson;
    }

    public function getAllCheckVp()
    {
        $check = DB::select("SELECT idCheckList, responsable, fechaIngresada, puntajeFinal, puntajeCritico, puntajeNormal, nombre FROM checklist_generados INNER JOIN sucursales WHERE checklist_generados.idSuc = sucursales.id ORDER BY fechaIngresada DESC");
        $vljson = json_encode($check, JSON_PRETTY_PRINT);

        return $vljson;
    }

    public function getChecksByUser(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $namesSuc = $json_strings->nameSuc;
        $checks = DB::select("SELECT idCheckList, responsable, fechaIngresada, puntajeFinal, puntajeCritico, puntajeNormal, nombre FROM checklist_generados INNER JOIN sucursales WHERE checklist_generados.idSuc = sucursales.id AND nombre IN($namesSuc) ORDER BY fechaIngresada DESC");
        $vljson = json_encode($checks, JSON_PRETTY_PRINT);
        return $vljson;
    }

    public function getInfoCheck(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idChecK = $json_strings->idCheck;

        $check = DB::select("SELECT idCheckList, responsable, fechaIngresada, puntajeFinal, puntajeCritico, puntajeNormal, nombre FROM checklist_generados INNER JOIN sucursales WHERE checklist_generados.idSuc = sucursales.id AND (idCheckList = ?)", [$idChecK]);
        $vljson = json_encode($check, JSON_PRETTY_PRINT);
        return $vljson;
    }

    public function getItemsCheckBySub(Request $request)
    {
        $idSubCheck = $request->input('idSubCheck');
        $idChecK = $request->input('idCheck');
        $numidCheck = (int) $idChecK;

        $queryItems = "SELECT T1.idPregunta,T2.accion, T1.estado, T2.critico, T2.idTipo, T2.puntaje, T3.comentario as comentarios, T2.comentario as reqCom, T2.images as reqImg, T4.idCheck, T1.idTipoCheck, T1.idCheckList, T1.idDetalle
        FROM checklist_generados_detalles AS T1
        INNER JOIN checklist_items AS T2
        ON T1.idPregunta = T2.idItem
        LEFT JOIN checklist_item_comentario T3
        ON T3.idItem = T1.idDetalle
        INNER JOIN checklist_sub_info T4
        ON T2.idSubseccion = T4.idSubseccion
        WHERE T1.idSubseccion = ? AND T1.idCheckList = ? ORDER BY T2.orden ASC";

        $dataItems = DB::select($queryItems, [$idSubCheck, $numidCheck]);


        $imgUrl = [];

        foreach ($dataItems as $key => $pregunta) {
            $imgUrl = [];

            $path =  storage_path() . "/app/public/check/{$pregunta->idCheck}/{$pregunta->idTipoCheck}/{$pregunta->idCheckList}/{$pregunta->idDetalle}/";
            $imgPath = glob($path . "*.jpg");
            foreach ($imgPath as $img) {
                $path = 'https://intranet.prigo.com.mx/' . substr($img, 14);

                array_push($imgUrl, $path);
            }
            $pregunta->img = $imgUrl;
        }



        if (!empty($dataItems)) {
            return response()->json($dataItems, 200);
        } else {
            return response()->json('error_no_data', 500);
        }
    }

    public function getExtrasCheck(Request $request)
    {
        $idSubCheck = $request->input('idSubCheck');
        $idChecK = $request->input('idCheck');
        $numidCheck = (int) $idChecK;

        $queryItems = "SELECT comentarios, nombres_imagenes FROM checklist_generados_extras
        WHERE idCheckList = ? AND idSub = ?";

        $dataItems = DB::select($queryItems, [$numidCheck, $idSubCheck]);

        if (!empty($dataItems)) {
            return response()->json($dataItems, 200);
        } else {
            return response()->json([
                (object)[
                    'comentarios' => 'Sin comentarios añadidos',
                    'nombres_imagenes' => 'image_no_required'
                ]
            ], 500);
        }
    }

    public function getResponsable(Request $request)
    {
        $idSuc = $request->input('id');

        $responsableSucu = DB::table('rh_empleado as A')
            ->join('rh_puesto as B', 'A.idPuesto', 'B.idPuesto')
            ->select('A.nombre')
            ->where('A.idSucursal', '=', $idSuc)
            ->where('A.estado', '=', 1)
            ->whereIn('B.idPuesto', [17, 48, 10, 69, 40, 14, 1, 16, 28])
            ->get();

        $isempty = $responsableSucu->isEmpty();

        if ($isempty == true) {

            $responsableGerenteSucu = DB::table('rh_empleado')
                ->select('nombre')
                ->where('idSucursal', '=', $idSuc)
                ->where('estado', '=', 1)
                ->where('puesto', '=', 'Gerente')
                ->get();

            $isemptyGerente = $responsableGerenteSucu->isEmpty();
            if ($isempty == true && $isemptyGerente == true) {
                $info = array(
                    array(
                        'nombre' => 'RESPONSABLE NO ASIGNADO'
                    ),
                );

                return $info;
            } else {

                $responsableGerenteSucu->push(['nombre' => 'RESPONSABLE NO ASIGNADO']);
                return $responsableGerenteSucu;
            }
        } else {
            $responsableSucu->push(['nombre' => 'RESPONSABLE NO ASIGNADO']);
            return $responsableSucu;
        }
    }

    public function getSucursalesByChecks(Request $request)
    {
        $accessTokenUser = $request->input('access_token');
        if (!empty($accessTokenUser)) {
            $idUser = $request->input('idUser');
            $idTypeCheck = $request->input('idTypeCheck');
            $numId = (int) $idUser;
            $roleUserQuery = "SELECT idRole FROM config_app_access WHERE idAplicacion = 1 AND idUsuario = ?;";
            $roleUser = DB::select($roleUserQuery, [$numId]);

            switch ($roleUser[0]->idRole) {
                case 1:
                    $sucursalesQuery = "SELECT T1.id, T1.nombre AS nombre, totalCheck
                    FROM sucursales AS T1
                    INNER JOIN (SELECT idSuc, COUNT(idSuc) AS totalCheck
                    FROM checklist_generados WHERE idTipoCheckList = ? GROUP BY idSuc ORDER BY fechaGenerada DESC) AS T2
                    ON T1.id = T2.idSuc
                    WHERE NOT(nombre = 'GIOTTO') AND NOT(nombre = 'GIOTTO COCINA')";
                    $datasuc = DB::select($sucursalesQuery, [$idTypeCheck]);
                    return response()->json($datasuc, 200);
                    break;
                case 2:
                    $idCorp = "SELECT idEmpresa FROM users WHERE id = ?";
                    $dataCorp = DB::select($idCorp, [$numId]);
                    $dataEmpresa = $dataCorp[0]->idEmpresa;
                    $sucursalesQuery = "SELECT T1.id, T1.nombre AS nombre, totalCheck
                    FROM sucursales AS T1
                    INNER JOIN (SELECT idSuc, COUNT(idSuc) AS totalCheck
                    FROM checklist_generados  WHERE idTipoCheckList = ? GROUP BY idSuc  ORDER BY fechaGenerada DESC) AS T2
                    ON T1.id = T2.idSuc
                    WHERE NOT(nombre = 'GIOTTO') AND NOT(nombre = 'GIOTTO COCINA') AND T1.idEmpresa = ?";
                    $datasuc = DB::select($sucursalesQuery, [$idTypeCheck, $dataEmpresa]);
                    return response()->json($datasuc, 200);
                    break;
                case 3:
                    $sqlSucAsig = "SELECT T1.idSucursal AS id, T2.nombre AS nombre,totalCheck
                    FROM dashboard_sucursal_usuario AS T1 
                    INNER JOIN sucursales AS T2 
                    ON T1.idSucursal = T2.id 
                    INNER JOIN (SELECT idSuc, COUNT(idSuc) AS totalCheck
                    FROM checklist_generados  WHERE idTipoCheckList = ? GROUP BY idSuc  ORDER BY fechaGenerada DESC) as T3
                    ON T1.idSucursal = T3.idSuc
                    WHERE T1.idUsuario = ?";
                    $dataSucAsig = DB::select($sqlSucAsig, [$idTypeCheck, $numId]);
                    return response()->json($dataSucAsig, 200);
                    break;
                case 4:
                    $sqlSucAsig = "SELECT T1.idSucursal AS id, T2.nombre AS nombre,totalCheck
                    FROM dashboard_sucursal_usuario AS T1 
                    INNER JOIN sucursales AS T2 
                    ON T1.idSucursal = T2.id 
                    INNER JOIN (SELECT idSuc, COUNT(idSuc) AS totalCheck
                    FROM checklist_generados  WHERE idTipoCheckList = ? GROUP BY idSuc  ORDER BY fechaGenerada DESC) as T3
                    ON T1.idSucursal = T3.idSuc
                    WHERE T1.idUsuario = ?";
                    $dataSucAsig = DB::select($sqlSucAsig, [$idTypeCheck, $numId]);
                    return response()->json($dataSucAsig, 200);
                    break;
            }
        } else {
            return response()->json('unauthenticated', 500);
        }
    }

    public function getSucursales(Request $request)
    {
        $accessTokenUser = $request->input('access_token');
        if (!empty($accessTokenUser)) {
            $idUser = $request->input('idUser');
            $numId = (int) $idUser;
            $roleUserQuery = "SELECT idRole FROM config_app_access WHERE idAplicacion = 1 AND idUsuario = ?;";
            $roleUser = DB::select($roleUserQuery, [$numId]);

            switch ($roleUser[0]->idRole) {
                case 1:
                    $sucursalesQuery = "SELECT id,nombre FROM sucursales WHERE NOT(nombre = 'GIOTTO') AND NOT(nombre = 'GIOTTO COCINA')";
                    $datasuc = DB::select($sucursalesQuery);
                    return response()->json($datasuc, 200);
                    break;
                case 2:
                    $idCorp = "SELECT idEmpresa FROM users WHERE id = ?";
                    $dataCorp = DB::select($idCorp, [$numId]);
                    $dataEmpresa = $dataCorp[0]->idEmpresa;
                    $sucursalesQuery = "SELECT id,nombre FROM sucursales WHERE idEmpresa = ? AND NOT(nombre = 'GIOTTO') AND NOT(nombre = 'GIOTTO COCINA')";
                    $datasuc = DB::select($sucursalesQuery, [$dataEmpresa]);
                    return response()->json($datasuc, 200);
                    break;
                case 3:
                    $sqlSucAsig = "SELECT T1.idSucursal AS id, T2.nombre AS nombre FROM dashboard_sucursal_usuario AS T1 INNER JOIN sucursales AS T2 ON T1.idSucursal = T2.id WHERE T1.idUsuario = ?";
                    $dataSucAsig = DB::select($sqlSucAsig, [$numId]);
                    return response()->json($dataSucAsig, 200);
                    break;
                case 4:
                    $sqlSucAsig = "SELECT T1.idSucursal AS id, T2.nombre AS nombre FROM dashboard_sucursal_usuario AS T1 INNER JOIN sucursales AS T2 ON T1.idSucursal = T2.id WHERE T1.idUsuario = ?";
                    $dataSucAsig = DB::select($sqlSucAsig, [$numId]);
                    return response()->json($dataSucAsig, 200);
                    break;
            }
        } else {
            return response()->json('unauthenticated', 500);
        }
    }

    public function getSucForIdUser(Request $request)
    {
        $json_response = response()->json($request);
        $json_strings = (json_decode($json_response->getContent()));
        $idUser = $json_strings->idUsuario;

        $sucursalesForId = DB::select('SELECT idSucursal AS idSuc ,idMicros AS nombreSuc FROM sucursales AS T1 INNER JOIN dashboard_sucursal_usuario AS T2 ON T1.id = T2.idSucursal WHERE T2.idUsuario = ?', [$idUser]);
        $vljson = json_encode($sucursalesForId, JSON_PRETTY_PRINT);
        return $vljson;
    }

    public function getGerentes()
    {
        $gerentes = DB::select("SELECT nombre FROM rh_empleado WHERE(puesto = ?) AND (estado = ?)", ["Gerente", 1]);
        foreach ($gerentes as $key => $gerente) {
            $data['gerentes'][$key] = [
                'nombre' => $gerente->nombre
            ];
        }
        return $data;
    }

    public function guardarCheckInfo(Request $request)
    {
        $idTipoCheckList = $request->input('idTipoCheckList');
        $responsable = $request->input('responsable');
        $idSuc = $request->input('idSuc');
        $dateG = $request->input('fechaGenerada');
        $puntajaFinal = $request->input('puntajeFinal');
        $puntajeF = $puntajaFinal;
        $fechaG = date_create_from_format('Y-d-m H:i', $dateG);
        $datefinallyG =  date_format($fechaG, 'Y-m-d H:i');
        $dateI = $request->input('fechaIngresada');
        $fechaI = date_create_from_format('Y-d-m H:i', $dateI);
        $datefinallyI =  date_format($fechaI, 'Y-m-d H:i');
        $idUsuario = $request->input('idUsuario');

        $idChecK = DB::table('checklist_generados')->insertGetId([
            'idTipoCheckList' => $idTipoCheckList,
            'responsable' => $responsable,
            'idSuc' => $idSuc,
            'fechaGenerada' => $datefinallyG,
            'fechaIngresada' => $datefinallyI,
            'puntajeFinal' => $puntajeF,
            'idUsuario' => $idUsuario
        ]);

        return $idChecK;
    }

    public function guardarItemsCheck(Request $request)
    {
        $idCheckList = $request->input('idCheckList');
        $idSectionCheck = $request->input('idSectionCheck');
        $comentsCheck = $request->input('comentariosCheck');
        $nombreImagen = $request->input('nombreImagen');
        $items = $request->input('items');

        if ($items != 'no_data') {
            $respItems = json_decode($items, JSON_UNESCAPED_SLASHES);
            $itemsFinall = json_decode($respItems, true);

            foreach ($itemsFinall  as $item) {
                try {
                    $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado) values (?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $item->idItem, $item->puntajeActual]);
                } catch (\Illuminate\Database\QueryException $ex) {
                    var_dump($ex->getMessage());
                }
            }
            // foreach ($itemsFinall  as $item) {
            //     try {
            //         $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado) values (?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $item->idItem, $item->estado]);
            //     } catch (\Illuminate\Database\QueryException $ex) {
            //         var_dump($ex->getMessage());
            //     }
            // }

            $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $nombreImagen]);

            if ($items == true && $insert == true) {
                return "success";
            } else {
                return "error";
            }
        } else {

            $dataItems = DB::table('checklist_items')
                ->select('idItem')
                ->where('idSb', '=', $idSectionCheck)
                ->get()->toarray();

            foreach ($dataItems as $key => $value) {
                $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado) values (?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $value->idItem, 0]);
            }

            $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $nombreImagen]);

            if ($items == true && $insert == true) {
                return "success";
            } else {
                return "error";
            }
        }
    }

    public function guardarChecklist(Request $request)
    {
        $json = $request->input('json');
        $body = json_decode($json);
        $idTipoCheckNum = $body->idTipoCheckNum;
        $idTipoCheckList = $body->idTipoCheckList;
        $responsable = $body->responsable ?? 'RESPONSABLE NO ASIGNADO';
        $idSuc = $body->idSuc;
        $fechaG = $body->fechaGenerada;
        $fechaI = $body->fechaIngresada;
        $puntajeFinal = $body->puntajeFinal;
        $idUsuario = $body->idUsuario;
        $itemsChecks = $body->itemsChecks;
        $imgs = $request->file('img');
        $imgsPreguntas = $request->file('imgPreguntas');


        if (!empty($itemsChecks)) {
            $idChecK = DB::table('checklist_generados')->insertGetId([
                'idTipoCheck' => $idTipoCheckNum,
                'idTipoCheckList' => $idTipoCheckList,
                'responsable' => $responsable ?? 'RESPONSABLE NO ASIGNADO',
                'idSuc' => $idSuc,
                'fechaGenerada' => date("Y-m-d H:i"),
                'fechaIngresada' => date("Y-m-d H:i"),
                'puntajeFinal' => $puntajeFinal,
                'idUsuario' => $idUsuario
            ]);

            foreach ($itemsChecks as $item) {
                // if (!empty($imgsPreguntas)) {
                $respuesta = $this->guardarItemsCheckSomosHGPrueba($item, $idTipoCheckList, $idChecK, $imgs, $imgsPreguntas);
                // } else {
                //     $respuesta = $this->guardarItemsCheckSomosHG($item, $idTipoCheckList, $idChecK, $imgs);
                // }
            }

            $this->guardarPuntajeFinal($idChecK);

            if ($respuesta == true) {
                if ($idTipoCheckNum != 15) {
                    $cliente = new Client();
                    $cliente->request('GET', "https://intranet.prigo.com.mx/reports/CheckList/sendEmailCheck/$idChecK");
                }
                return "success";
            } else {
                return "error";
            }
        }
    }

    public function guardarItemsCheckSomosHG($item, $idTypeCheck, $idCheckList, $imgs)
    {

        $items = $item->items;
        $idSectionCheckNum = $item->idSectionNum;
        $idSectionCheck = $item->idSectionCheck;
        $comentsCheck = $item->comentariosCheck;
        $nombreImagen = $item->nombreImagen;

        if ($items != 'no_data') {
            $respItems = json_decode($items, JSON_UNESCAPED_SLASHES);

            foreach ($respItems  as $item) {
                $itemsFinall = json_decode($item, true);
                try {
                    $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado, idSubseccion) values (?, ?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $itemsFinall['idPregunta'], $itemsFinall['puntajeActual'], $idSectionCheckNum]);
                    $lid = DB::getPdo()->lastInsertId();
                    if (!empty($itemsFinall['comentarios'])) {
                        DB::insert('insert into checklist_item_comentario (idItem, comentario) values (?, ?)', [$lid, $itemsFinall['comentarios']]);
                    }
                } catch (Exception $e) {
                    dd($e);
                }
            }
            $i = 0;

            if ($nombreImagen != null && $comentsCheck != null) {
                foreach ($nombreImagen as $imgName) {
                    $filename = $idCheckList . '-' . $idTypeCheck . '-' . $idSectionCheck . $i . '.jpg';
                    $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $filename]);
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $img) {
                        if ($img->getClientOriginalName() == $imgName) {
                            $i++;
                            $img->storeAs("check/$idTypeCheck/$idSectionCheck/$idCheckList", $filename, 'public');
                        }
                    }
                }
            }
            if ($comentsCheck != null) {
                $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, "image_no_required"]);
            }

            try {
                $sql = "SELECT sum(estado) as total FROM checklist_generados_detalles WHERE idCheckList = ? AND idTipoCheck = ? GROUP BY idSubseccion";
                $puntajeTotal = DB::select($sql, [$idCheckList, $idSectionCheck]);
                $sql = "SELECT sum(B.puntaje) as maximo FROM checklist_generados_detalles A INNER JOIN checklist_items B ON A.idPregunta = B.idItem WHERE A.idCheckList = ? AND A.idTipoCheck = ? AND A.estado IS NOT null GROUP BY B.idSubseccion";
                $puntajeMaximo = DB::select($sql, [$idCheckList, $idSectionCheck]);

                $calificacion = empty($puntajeMaximo) ? 0 : ($puntajeTotal[0]->total / $puntajeMaximo[0]->maximo) * 100;
            } catch (\Throwable $th) {
                Log::info("Checklist Error:", [$th]);
            }

            try {
                DB::insert("INSERT INTO checklist_generados_sub (idSub, idChecklist, total, maximo, calificacion) VALUE (?,?,?,?,?)", [$idSectionCheckNum, $idCheckList, $puntajeTotal[0]->total == null ? 0 : $puntajeTotal[0]->total, empty($puntajeMaximo) ? 0 : $puntajeMaximo[0]->maximo, $calificacion]);
            } catch (\Throwable $th) {
                dd($th);
            }

            return $items;
        } else {

            // $dataItems = DB::table('checklist_items')
            //     ->select('idItem')
            //     ->where('idSb', '=', $idSectionCheck)
            //     ->get()->toarray();

            // foreach ($dataItems as $key => $value) {
            //     $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado) values (?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $value->idItem, 0]);
            // }

            // $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $nombreImagen]);

            // if ($items == true && $insert == true) {
            //     return "success";
            // } else {
            //     return "error";
            // }
            return 'nodata';
        }
    }

    public function guardarItemsCheckSomosHGPrueba($item, $idTypeCheck, $idCheckList, $imgs, $imgsPreguntas)
    {

        $items = $item->items;
        $idSectionCheckNum = $item->idSectionNum;
        $idSectionCheck = $item->idSectionCheck;
        $comentsCheck = $item->comentariosCheck;
        $nombreImagen = $item->nombreImagen;

        if ($items != 'no_data') {
            $respItems = json_decode($items, JSON_UNESCAPED_SLASHES);

            foreach ($respItems  as $key => $item) {
                $itemsFinall = json_decode($item, true);
                try {
                    $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado, idSubseccion) values (?, ?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $itemsFinall['idPregunta'], $itemsFinall['puntajeActual'], $idSectionCheckNum]);
                    $lid = DB::getPdo()->lastInsertId();
                    if (!empty($itemsFinall['comentarios'])) {
                        DB::insert('insert into checklist_item_comentario (idItem, comentario) values (?, ?)', [$lid, $itemsFinall['comentarios']]);
                    }

                    if (!empty($itemsFinall['img'])) {
                        foreach ($imgsPreguntas as $img) {
                            foreach ($itemsFinall['img'] as $key => $imgName) {
                                if ($img->getClientOriginalName() == $imgName) {
                                    $false = $img->storeAs("check/$idTypeCheck/$idSectionCheck/$idCheckList/$lid", $imgName, 'public');
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    dd($e);
                }
            }
            $i = 0;

            if ($nombreImagen != null && $comentsCheck != null) {
                foreach ($nombreImagen as $imgName) {
                    $filename = $idCheckList . '-' . $idTypeCheck . '-' . $idSectionCheck . $i . '.jpg';
                    $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $filename]);
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $img) {
                        if ($img->getClientOriginalName() == $imgName) {
                            $i++;
                            $img->storeAs("check/$idTypeCheck/$idSectionCheck/$idCheckList", $filename, 'public');
                        }
                    }
                }
            }
            if ($comentsCheck != null) {
                $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, "image_no_required"]);
            }

            $sql = "SELECT sum(estado) as total FROM checklist_generados_detalles WHERE idCheckList = ? AND idTipoCheck = ? GROUP BY idSubseccion";
            $puntajeTotal = DB::select($sql, [$idCheckList, $idSectionCheck]);
            $sql = "SELECT sum(B.puntaje) as maximo FROM checklist_generados_detalles A INNER JOIN checklist_items B ON A.idPregunta = B.idItem WHERE A.idCheckList = ? AND A.idTipoCheck = ? AND A.estado IS NOT null GROUP BY B.idSubseccion";
            $puntajeMaximo = DB::select($sql, [$idCheckList, $idSectionCheck]);

            $calificacion = empty($puntajeMaximo) || $puntajeMaximo[0]->maximo == 0 ? 0 : ($puntajeTotal[0]->total / $puntajeMaximo[0]->maximo) * 100;
            try {
                DB::insert("INSERT INTO checklist_generados_sub (idSub, idChecklist, total, maximo, calificacion) VALUE (?,?,?,?,?)", [$idSectionCheckNum, $idCheckList, !empty($puntajaFinal) ? ($puntajeTotal[0]->total == null ? 0 : $puntajeTotal[0]->total) : 0, empty($puntajeMaximo) ? 0 : $puntajeMaximo[0]->maximo, $calificacion]);
            } catch (\Throwable $th) {
                Log::info("Checklist Error:", [$th]);
            }

            return $items;
        } else {

            // $dataItems = DB::table('checklist_items')
            //     ->select('idItem')
            //     ->where('idSb', '=', $idSectionCheck)
            //     ->get()->toarray();

            // foreach ($dataItems as $key => $value) {
            //     $items = DB::insert('insert into checklist_generados_detalles (idCheckList, idTipoCheck, idPregunta, estado) values (?, ?, ?, ?)', [$idCheckList, $idSectionCheck, $value->idItem, 0]);
            // }

            // $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, idSub, comentarios, nombres_imagenes) values (?, ?, ?, ?)', [$idCheckList,  $idSectionCheck, $comentsCheck, $nombreImagen]);

            // if ($items == true && $insert == true) {
            //     return "success";
            // } else {
            //     return "error";
            // }
            return 'nodata';
        }
    }


    public function guardarPuntajeFinal($idCheckList)
    {
        try {
            $sql = "SELECT cast(SUM(B.puntaje) AS DECIMAL(10,2)) as puntaje FROM checklist_generados_detalles A INNER JOIN checklist_items B ON B.idItem = A.idPregunta WHERE A.idCheckList = ? AND A.estado IS NOT null";
            $puntaje = DB::select($sql, [$idCheckList]);

            $update = DB::update('UPDATE checklist_generados SET puntajeMaximo = ? WHERE idCheckList = ?', [$puntaje[0]->puntaje, $idCheckList]);
        } catch (Exception $e) {
            Log::info("Checklist Error:", [$e]);
        }

        return $update;
    }


    public function guardarExtCheck(Request $request)
    {
        $idCheckList = $request->input('idCheckList');
        $comentsCheck = $request->input('comentariosCheck');
        $nombreImagen = $request->input('nombreImagen');

        $insert = DB::insert('INSERT INTO checklist_generados_extras (idCheckList, comentarios, nombres_imagenes) values (?, ?, ?)', [$idCheckList, $comentsCheck, $nombreImagen]);

        return $insert;
    }

    public function getDataChecksBySuc(Request $request)
    {
        $idCheckList = $request->input('idCheckList') ?? "%%";
        $idTipoCheck = $request->input('idTipoCheck') ?? "%%";
        $idEvaluacion = $request->input('idEvaluacion') ?? "%%";
        $idSuc = $request->input('idSuc');
        $date = strtotime('-1 month', strtotime(date("Y-m-d")));
        $dateNow = date('Y-m-30');
        $newDate = date("Y-m-00", $date);
        $querySelectChecks = "SELECT T1.idCheckList, T1.idTipoCheck, T1.idTipoCheckList,T1.responsable, T1.fechaGenerada, T1.puntajeFinal, T1.puntajeMaximo AS puntajeDeseado, T3.nombre as sucursal
        FROM checklist_generados AS T1
        INNER JOIN checklist_info AS T2
        ON T1.idTipoCheckList = T2.idCheckList
        INNER JOIN sucursales AS T3
        ON T1.idSuc = T3.id
        WHERE (T1.idTipoCheckList LIKE ? OR T1.idTipoCheck LIKE ?) AND T2.idEvaluacion LIKE ? AND T1.idSuc = ? ORDER BY T1.fechaGenerada DESC";
        // $checksData = DB::select($querySelectChecks, [$idCheckList, $idSuc]);

        if (!empty($request->input('idSuc'))) {
            $checksData = DB::select($querySelectChecks, [$idCheckList, $idTipoCheck, $idEvaluacion, $idSuc]);
        } else {
            $sucursales = $this->buscarSucursal($request);
            $sucursalesA = [];
            foreach ($sucursales as $sucursal) {
                array_push($sucursalesA, $sucursal->idSucursal);
            }
            $checksData = [];
            $checksData = DB::table('checklist_generados AS T1')
                ->join('checklist_info AS T2', 'T1.idTipoCheckList', '=', 'T2.idCheckList')
                ->join('sucursales AS T3', 'T1.idSuc', '=', 'T3.id')
                ->select('T1.idCheckList', 'T1.idTipoCheck', 'T1.idTipoCheckList', 'T1.responsable', 'T1.fechaGenerada', 'T1.puntajeFinal', 'T1.puntajeMaximo AS puntajeDeseado', 'T3.nombre as sucursal')
                ->where(
                    function ($query) use ($idCheckList, $idTipoCheck) {
                        return $query
                            ->where('T1.idTipoCheckList', "LIKE", $idCheckList)
                            ->orWhere('T1.idTipoCheck', "LIKE", $idTipoCheck);
                    }
                )
                ->where('T2.idEvaluacion', "LIKE", $idEvaluacion)
                ->whereBetween('T1.fechaGenerada', [date('Y-m-00', strtotime('-6 months')) . " 00:00:00", date('Y-m-31') . " 00:00:00"])
                ->whereIn('T1.idSuc', $sucursalesA)
                ->orderBy('T1.fechaGenerada', 'DESC')
                ->get();
        }

        foreach ($checksData as $check) {
            $check->puntajeDeseado = strval($check->puntajeDeseado);
        }


        if (!empty($checksData)) {
            return response()->json($checksData, 200);
        } else {
            return response()->json([], 200);
        }
    }

    public function getDataImageToCheck(Request $request)
    {
        $idTypeCheck = $request->input('idTypeCheck');
        $idUsuario = $request->input('idUsuario');


        $dataImageCheck = DB::table('checklist_sub_info')
            ->select('fotografiaEst', 'espeFotografia', 'totalFotografias')
            ->where('idSubseccion', '=', $idTypeCheck)
            ->get()->toarray();

        if (sizeof($dataImageCheck) != 0) {
            switch ($dataImageCheck[0]->fotografiaEst) {
                case 2:
                    $var = [
                        'status' => 2,
                        'descrp' => $dataImageCheck[0]->espeFotografia,
                        'total' => $dataImageCheck[0]->totalFotografias
                    ];
                    return response()->json($var, 200);
                    break;
                case 1:
                    $var = [
                        'status' => 1,
                        'descrp' => $dataImageCheck[0]->espeFotografia,
                        'total' => $dataImageCheck[0]->totalFotografias
                    ];
                    return response()->json($var, 200);
                    break;
                case 0:
                    $var = [
                        'status' => 0,
                        'descrp' => 'image_not_required'
                    ];
                    return response()->json($var, 200);
                    break;
            }
        } else {
            $var = [
                'status' => 3,
                'descrp' => 'empty_records'
            ];
            return response()->json($var, 200);
        }
    }

    public function getDataCheck(Request $request)
    {
        $idCheckList = $request->input('idCheckList');
        $numId = (int) $idCheckList;

        $querySelectChecks = "SELECT T1.idCheckList,T3.nombre AS nombreSuc, T1.responsable, T1.fechaGenerada, T1.puntajeFinal, T2.puntaje_total AS puntajeDeseado 
        FROM checklist_generados AS T1
        INNER JOIN checklist_info AS T2
        ON T1.idTipoCheckList = T2.idCheckList
        LEFT JOIN sucursales AS T3
        ON T1.idSuc = T3.id
        WHERE T1.idCheckList = ?";

        $checkData = DB::select($querySelectChecks, [$numId]);

        if (!empty($checkData)) {
            return response()->json($checkData, 200);
        } else {
            return response()->json('error/not_data', 500);
        }
    }

    public function getListEvaluaciones(Request $request)
    {
        $idUsuario = $request->input('idUsuario');
        $user = User::find($idUsuario);
        $area = $user->idArea;
        $empresa = $user->idEmpresa;
        $evaluaciones = $this->getEvaluaciones($idUsuario, $empresa, $area);
        return $evaluaciones;
    }


    public function getListEvaluacionesWeb(Request $request)
    {
        $user = Auth::user();
        $idUsuario = $user->idUsuario;
        $type = $user->type;
        $area = $user->idArea;
        if (!empty($type)) {
            if ($type == 'E') {
                $empresa = $request->input('idSucursal');
            } else if ($type == 'C') {
                $sql = "SELECT idEmpresa FROM sucursales_tier WHERE idMicros = ?";
                $empresa = DB::select($sql, [$request->input('idSucursal')]);
                $empresa = $empresa[0]->idEmpresa;
            } else if ($type == 'S') {
                $sql = "SELECT idEmpresa FROM sucursales WHERE id = ?";
                $empresa = DB::select($sql, [$request->input('idSucursal')]);
                $empresa = $empresa[0]->idEmpresa;
            }
        } else {
            if (!is_numeric($request->input('idSucursal'))) {
                $sql = "SELECT idEmpresa FROM sucursales WHERE idMicros = ?";
                $empresa = DB::select($sql, [$request->input('idSucursal')]);
                $empresa = $empresa[0]->idEmpresa;
            } else {
                $empresa = $request->input('idSucursal');
            }
        }
        $evaluaciones = $this->getEvaluaciones($idUsuario, $empresa, $area);
        return $evaluaciones;
    }

    public function getEvaluaciones($idUsuario, $empresa, $area)
    {
        $evaluaciones = DB::table('checklist_evaluacion AS A')
            ->select('A.nombre', 'A.idEvaluacion')
            ->join('checklist_info AS B', 'A.idEvaluacion', '=', 'B.idEvaluacion')
            ->where('B.idArea', $area)
            ->where('B.idEmpresa', $empresa)
            ->groupBy('A.idEvaluacion', 'A.nombre')
            ->get();

        return $evaluaciones;
    }

    public function reporteChecklist(Request $request)
    {
        $json = $request->all();

        if (!empty($json)) {

            $sucursal = [];

            if (!empty($json['idSucursal'])) {
                $sucursal = [$json['idSucursal']] ?? '%%';
            } else {
                $sucursalesObj = $this->buscarSucursal($request);
                foreach ($sucursalesObj as $suc) {
                    array_push($sucursal, $suc->idSucursal);
                }
            }


            $mensual = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as mensual'))
                ->where('A.fechaGenerada', 'LIKE', '%-' . date('m') . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();


            $semestral = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as semestral'))
                ->whereBetween('A.fechaGenerada', [date('Y-m-00', strtotime('-6 months')) . " 00:00:00", date('Y-m-31') . " 00:00:00"])
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();

            $anual = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as anual'))
                ->where('fechaGenerada', 'LIKE', date('Y') . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->get();

            $ultima = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as ultimo'))
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->orderBy('A.idCheckList', 'desc')
                ->groupBy('A.idCheckList')
                ->first();

            $registros = DB::table('checklist_generados AS A')
                ->select('A.*')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->orderBy('A.idCheckList', 'desc')
                ->groupBy('A.idCheckList', 'A.idTipoCheckList', 'responsable', 'idSuc', 'fechaGenerada', 'fechaIngresada', 'puntajeFinal', 'enviado', 'idUsuario', 'puntajeMaximo', 'A.idTipoCheck', 'B.id', 'B.idCheckList', 'B.idEmpresa', 'B.idEvaluacion', 'B.idArea', 'B.idCrea', 'B.fechaCrea', 'B.nombre', 'B.puntaje_total', 'B.cal_m', 'B.cal_b', 'B.status')
                ->limit(5)
                ->get();

            $meses = [];

            for ($i = 1; $i <= date('m'); $i++) {
                if ($i < 10) {
                    $e = "0" . strval($i);
                } else {
                    $e = $i;
                }
                $mes = DB::table('checklist_generados AS A')
                    ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                    ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as avg'))
                    ->where('fechaGenerada', 'LIKE', '%-' . $e . '-%')
                    ->where('B.idEvaluacion', $json['idEvaluacion'])
                    ->whereIn('idSuc', $sucursal)
                    ->get();
                $mes[0]->mes = $i - 1;
                if ($mes[0]->avg == null) {
                    $mes[0]->avg = "0";
                }
                array_push($meses, $mes[0]);
            }

            $puntajeMaxMin = DB::table('checklist_info')
                ->select('cal_m', 'cal_b')
                ->where('idEvaluacion', $json['idEvaluacion'])
                ->get();


            return response()->json([
                'success' => true,
                'mensual' => $mensual[0]->mensual,
                'anual' => $anual[0]->anual,
                'semestral' => $semestral[0]->semestral,
                'ultimo' => $ultima->ultimo ?? "0",
                'registros' => $registros,
                'meses' => $meses,
                'puntajeMaxMin' => $puntajeMaxMin,
            ]);
        }
        return response()->json([
            'success' => false,
            'data' => 'No data'
        ]);
    }

    public function reporteChecklistWeb(Request $request)
    {
        $json = $request->all();

        if (!empty($json)) {

            $sucursal = [];
            $mes = $request->input('mes');
            $anio = $request->input('anio');

            if (is_numeric($json['idSucursal'])) {
                $sucursalesObj = DB::table('sucursales as A')
                    ->select('id')
                    ->join('checklist_sucursal_usuario as B', 'A.id', '=', 'B.idSucursal')
                    ->where('idEmpresa', $json['idSucursal'])
                    ->where('B.idUsuario', '=', Auth::id())
                    ->where('estado', '!=', 0)
                    ->get()->toArray();
                foreach ($sucursalesObj as $suc) {
                    array_push($sucursal, $suc->id);
                }
            } else {
                $sql = "SELECT id FROM sucursales WHERE idMicros = ? AND estado != 0";
                $sucursal = DB::select($sql, [$json['idSucursal']]);
                $sucursal = [$sucursal[0]->id];
            }

            if ($mes > 6) {
                $mesIni = "07";
                $mesFin = "12";
                $mesIniAnt = "01";
                $mesFinAnt = "06";
            } else {
                $mesIni = "01";
                $mesFin = "06";
                $mesIniAnt = "07";
                $mesFinAnt = "12";
                $anioAnt = $anio - 1;
            }

            $mesAnt = intval($mes) - 1;
            if ($mesAnt < 10) {
                $mesAnt = "0" . strval(intval($mes) - 1);
            }


            $mensual = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as mensual'))
                ->where('A.fechaGenerada', 'LIKE', '%-' . $mes . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();

            $mensualAnt = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as mensual'))
                ->where('A.fechaGenerada', 'LIKE', '%-' . $mesAnt . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();

            $semestral = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as semestral'))
                ->whereBetween('A.fechaGenerada', [date("$anio-$mesIni-00") . " 00:00:00", date("$anio-$mesFin-31") . " 00:00:00"])
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();

            $semestralAnt = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(A.puntajeFinal/A.puntajeMaximo)*100,0) as semestral'))
                ->whereBetween('A.fechaGenerada', [date($anioAnt ?? $anio . "-$mesIniAnt-00") . " 00:00:00", date($anioAnt ?? $anio . "-$mesFinAnt-31") . " 00:00:00"])
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('A.idSuc', $sucursal)
                ->get();

            $anual = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as anual'))
                ->where('fechaGenerada', 'LIKE', $anio . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->get();

            $anualAnt = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as anual'))
                ->where('fechaGenerada', 'LIKE', intval($anio) - 1 . '-%')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->get();

            $ultima = DB::table('checklist_generados AS A')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as ultimo'))
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                // ->where('A.fechaGenerada', 'LIKE', '%-' . $mesAnt . '-%')
                ->orderBy('A.idCheckList', 'desc')
                ->groupBy('A.idCheckList')
                ->limit(2)
                ->get();

            $registros = DB::table('checklist_generados AS A')
                ->select('A.*')
                ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                ->where('B.idEvaluacion', $json['idEvaluacion'])
                ->whereIn('idSuc', $sucursal)
                ->orderBy('A.idCheckList', 'desc')
                ->groupBy('A.idCheckList', 'A.idTipoCheckList', 'responsable', 'idSuc', 'fechaGenerada', 'fechaIngresada', 'puntajeFinal', 'enviado', 'idUsuario', 'puntajeMaximo', 'A.idTipoCheck', 'B.id', 'B.idCheckList', 'B.idEmpresa', 'B.idEvaluacion', 'B.idArea', 'B.idCrea', 'B.fechaCrea', 'B.nombre', 'B.puntaje_total', 'B.cal_m', 'B.cal_b', 'B.status')
                ->limit(5)
                ->get();

            $meses = [];

            for ($i = 1; $i <= date('m'); $i++) {
                if ($i < 10) {
                    $e = "0" . strval($i);
                } else {
                    $e = $i;
                }
                $mesA = DB::table('checklist_generados AS A')
                    ->join('checklist_info AS B', 'A.idTipoCheck', '=', 'B.id')
                    ->select(DB::raw('round(AVG(puntajeFinal/puntajeMaximo)*100,0) as avg'))
                    ->where('fechaGenerada', 'LIKE', '%-' . $e . '-%')
                    ->where('B.idEvaluacion', $json['idEvaluacion'])
                    ->where('puntajeFinal', '!=', null)
                    ->whereIn('idSuc', $sucursal)
                    ->get();
                $mesA[0]->mes = $i - 1;
                if ($mesA[0]->avg != null) {
                    array_push($meses, $mesA[0]);
                }
            }

            $puntajeMaxMin = DB::table('checklist_info')
                ->select('cal_m', 'cal_b')
                ->where('idEvaluacion', $json['idEvaluacion'])
                ->limit(1)
                ->get();

            $datos = (object)[
                'mensual' => $mensual[0]->mensual,
                'mensualAnt' => $mensualAnt[0]->mensual,
                'anual' => $anual[0]->anual,
                'anualAnt' => $anualAnt[0]->anual,
                'semestral' => $semestral[0]->semestral,
                'semestralAnt' => $semestralAnt[0]->semestral,
                'ultimo' => $ultima,
                'registros' => $registros,
                'meses' => $meses,
                'puntajeMaxMin' => $puntajeMaxMin[0],
            ];


            return response()->json([
                'success' => true,
                'data' => (array)[$datos]
            ]);
        }
        return response()->json([
            'success' => false,
            'data' => 'No data'
        ]);
    }

    public function checkCount(Request $request)
    {
        $idUsuario = $request->input('idUsuario');
        $count = DB::select('SELECT count(idCheck) as cheks FROM checklist_asignacion WHERE idUser = ?', [$idUsuario]);
        return $count;
    }

    public function visualizarChecklist(Request $request, $id)
    {
        $sql = "SELECT T1.idPregunta,T2.accion, T1.estado, T2.critico, T2.idTipo, T2.puntaje, T3.comentario as comentarios, T2.comentario as reqCom, T1.idSubseccion, T4.idCheckNum
        FROM checklist_generados_detalles AS T1
        INNER JOIN checklist_items AS T2
        ON T1.idPregunta = T2.idItem
        LEFT JOIN checklist_item_comentario T3
        ON T3.idItem = T1.idDetalle
        INNER JOIN checklist_sub_info T4
        ON T4.idSubseccion = T1.idTipoCheck
        WHERE T1.idCheckList = ? ORDER BY T2.orden ASC";

        $item = DB::select($sql, [$id]);

        $sql = "SELECT * FROM checklist_sub_info WHERE idCheckNum = ?";
        $subSecciones = DB::select($sql, [$item[0]->idCheckNum]);

        $preguntas = array();
        $anteriorId = 0;

        foreach ($subSecciones as $subSeccion) {
            foreach ($item as $value) {
                if ($subSeccion->idCheckNum == $value->idCheckNum && $anteriorId != $subSeccion->idSubseccion) {
                    $preguntas[] = (object)array("idTipo" => 0, "accion" => $subSeccion->nombre, "estado" => 0);
                }
                if ($value->idSubseccion == $subSeccion->idSubseccion) {
                    array_push($preguntas, $value);
                }
                $anteriorId = $subSeccion->idSubseccion;
            }
        }
        $menu = $this->menuReports();
        return view('checklistoperativo.CheckListVisualizar', ['menu' => $menu, 'items' => $preguntas, 'page' => 1]);
    }

    public function preVizualizarCheck($id)
    {
        $item = DB::table('checklist_info as A')
            ->join('checklist_sub_info as B', 'A.id', 'B.idCheckNum')
            ->join('checklist_items as C', 'C.idSubseccion', 'B.idSubseccion')
            ->select('C.idItem', 'C.accion', 'C.idTipo', DB::raw('null as estado'), 'C.puntaje', 'C.comentario', 'C.images', 'B.idCheckNum', 'C.idSubseccion')
            ->where('A.id', $id)
            ->get();

        $sql = "SELECT * FROM checklist_sub_info WHERE idCheckNum = ? AND status = 1";
        $subSecciones = DB::select($sql, [$id]);

        $preguntas = array();
        $anteriorId = 0;
        foreach ($subSecciones as $subSeccion) {
            foreach ($item as $value) {
                if ($subSeccion->idCheckNum == $value->idCheckNum && $anteriorId != $subSeccion->idSubseccion) {
                    $preguntas[] = (object)array("idTipo" => 0, "accion" => $subSeccion->nombre, "estado" => 0, "comentario" => 0, 'images' => 0);
                }
                if ($value->idSubseccion == $subSeccion->idSubseccion) {
                    array_push($preguntas, $value);
                }
                $anteriorId = $subSeccion->idSubseccion;
            }
        }
        return view('checklistoperativo.ChecklistPrevisualizacion', ['menu' => [], 'items' => $preguntas, 'page' => 2]);
    }

    public function menuReports()
    {
        $sql = "SELECT * FROM config_reportes_categoria";
        $cat = DB::select($sql, []);
        $sql = "SELECT cru.idReporte, cr.nombre, cr.ruta, cr.idCategoria FROM config_reportes_usuario cru INNER JOIN config_reportes cr ON cr.idReporte = cru.idReporte WHERE cru.idUsuario = ?";
        $items = DB::select($sql, [Auth::id()]);

        foreach ($cat as $key => $value) {
            $value->menu = [];
            foreach ($items as $item) {
                if ($value->idCategoria == $item->idCategoria) {
                    array_push($value->menu, $item);
                }
            }
            if (empty($value->menu)) {
                unset($cat[$key]);
                // $value->menu = [
                //     (object)[
                //         'nombre' => 'Proximamente',
                //         'ruta' => '',
                //         'idCategoria' => $value->idCategoria
                //     ]
                // ];
                // unset($cat[$key]);
            }
        }

        return $cat;
    }

    public function sendEmail($idCheckList)
    {
        $sql = "SELECT B.nombre ,B.email, A.idCheckList, A.fechaGenerada FROM checklist_generados A INNER JOIN sucursales B ON A.idSuc = B.id WHERE A.idCheckList = ?";
        $checks = DB::select($sql, [$idCheckList]);

        foreach ($checks as $value) {
            $url = route('visualizarChecklist', [$value->idCheckList]);
            Mail::send('checklistoperativo.mailChecklist', ['idCheckList' => $value->idCheckList, 'nombreSuc' => $value->nombre, 'fecha' => $value->fechaGenerada, 'url' => $url], function ($message) use ($value) {
                $message->from('reportes@prigo.com.mx', 'Checklist Creado');
                // $message->to('javiles@prigo.com.mx');
                $message->to($value->email);
                $message->bcc('test@prigo.com.mx');
                $message->subject("Se genero un nuevo checklist");
            });
        }
    }
}
