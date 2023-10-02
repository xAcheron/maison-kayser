<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

use Illuminate\Support\Facades\Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Classes\Reports\utils\UserLocation;

#use App;

class SalesController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth', ['except' => ['get', 'getArcos', 'getEke', 'getHA', 'getCYS', 'set', 'setIscam', 'sendXlsCash', 'getXlsCash', 'getXlsSlTxTp', 'sendEKEItemReport']]);
        // $this->middleware(function ($request, $next) {
            //$idUsuario = Auth::id();
            // $idUsuario = 1;
            // $user = Auth::user();
            // $sql = "SELECT * FROM config_app_access WHERE idUsuario = ? AND idAplicacion = 6; ";
            // $accesQuery = DB::select($sql, [$idUsuario]);
            // if (!empty($accesQuery) || $idUsuario == 8) {
            //     if ($idUsuario == 8) {
            //         session(['DASHRole' => 1]);
            //     } else {
            //         session(['DASHRole' => $accesQuery[0]->idRole]);
            //     }

            //     if (!empty($accesQuery[0]) && $accesQuery[0]->idRole != 1 && $accesQuery[0]->idRole != 6 && $idUsuario != 8) {

            //         $sql = "SELECT group_concat( `idSucursal` separator ',') as `sucursales` FROM dashboard_sucursal_usuario INNER JOIN sucursales ON sucursales.id = dashboard_sucursal_usuario.idSucursal  WHERE sucursales.idTipo>0 AND idUsuario = ? GROUP BY idUsuario;";

            //         $sucursales = DB::select($sql, [$idUsuario]);
            //         if (!empty($sucursales)) {
            //             session(['sucursales' => $sucursales[0]->sucursales]);
            //         }
            //     }
            // }

        //     return $next($request);
        // });
    }

    public function getEke(Request $request)
    {

        $taxCodes = array();
        $taxCodes[0] = "R0";
        $taxCodes[4] = "R1";
        $taxCodes[10] = "R2";
        $taxCodes[21] = "R3";

        if (!empty($request->input("token"))) {
            $objs = array();
            $fecha = !empty($request->input("fecha")) ? $request->input("fecha") : date("Y-m-d", strtotime("-1 day"));
            #$fecha = "2023-04-29";
            $sql = "SELECT s.id, s.normaSAP idSap, fecha, (netSales-taxCollected) AS total, taxCollected AS tax, serviceCharges AS tips, s.cxcAcc taxAcc ,s.eurAcc saleAcc,s.diffAcc,s.clienteAcc FROM venta_diaria_sucursal AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE s.idEmpresa = 4 AND fecha = ?";
            $ventas = DB::select($sql, [$fecha]);
            if (count($ventas)) {
                $ventaArr =  array();
                $i = 0;
                foreach ($ventas as $venta) {
                    $sql = "SELECT A.perTax, IF(perTax=4, 'R1', IF(perTax=10, 'R2', 'R3' ))  AS taxCode,SUM(A.ventaDImpuesto) ventaDImpuesto , SUM( A.ventaDImpuesto/(1+(perTax/100))) ventaAImpuesto, SUM( A.ventaDImpuesto*((perTax/100)/(1+(perTax/100)))) taxCollected FROM vds_tax A WHERE A.idSucursal = ? AND fecha =? GROUP BY A.perTax;";
                    $taxes = DB::select($sql, [$venta->id, $fecha]);
                    $totalSalesTax = 0;
                    $totalTax = 0;
                    foreach ($taxes as $tax) {
                        $totalSalesTax += $tax->ventaAImpuesto;
                        $totalTax += $tax->taxCollected;
                    }
                    $totalSalesTax = $venta->total - $totalSalesTax;
                    $ventaArr[$i] = array("id" => $venta->idSap, "fecha" => $venta->fecha, "total" => $venta->total, "tax" => $totalTax, "taxAcc" => $venta->taxAcc, "saleAcc" => $venta->saleAcc, "diffAcc" => $venta->diffAcc, "clienteAcc" => $venta->clienteAcc, "taxes" => [], "tenders" => []);
                    $taxes[] = json_decode(json_encode(array("perTax" => 0, "taxCode" => "R0", "ventaDImpuesto" => $totalSalesTax, "ventaAImpuesto" => $totalSalesTax, "taxCollected" => 0)));
                    $ventaArr[$i]["taxes"] = $taxes;
                    $sql = "SELECT vds.tender, SUM(vds.netSales) netSales , tacc.account FROM vds_tender AS vds INNER JOIN sucursales s ON s.id = vds.idSucursal INNER JOIN tender_account AS tacc ON (tacc.tender = vds.tender AND s.id= tacc.idSucursal ) WHERE fecha = ? AND vds.idSucursal = ? GROUP BY vds.tender,tacc.account;";
                    $tenders = DB::select($sql, [$fecha, $venta->id]);
                    $ventaArr[$i]["tenders"] = $tenders;
                    $i++;
                }

                return response()->json([
                    'total' => count($ventaArr),
                    'Ventas' => json_decode(json_encode($ventaArr))
                ]);
            }
        }
    }

    public function getCYS(Request $request)
    {
        if (!empty($request->input("token"))) {
            $objs = array();
            $fecha = !empty($request->input("fecha")) ? $request->input("fecha") : date("Y-m-d", strtotime("-1 day"));
            #$fecha ="2023-04-29";
            $sql = "SELECT s.id, s.normaSap, fecha, ROUND(netSales-taxCollected, 2) AS total, ROUND(taxCollected,2) AS tax, ROUND(serviceCharges, 2) AS tips, s.amexAcc, s.creditoAcc, s.debitoAcc, s.mxnAcc, s.usdAcc, s.eurAcc,s.diffAcc, s.cxcAcc,s.clienteAcc FROM venta_diaria_sucursal AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE s.idEmpresa = 2 AND  NOT(s.mxnAcc IS null) AND fecha = ?";
            $ventas = DB::select($sql, [$fecha]);
            if (count($ventas)) {
                foreach ($ventas as $venta) {
                    $object = new \stdClass();
                    $object->id = $venta->normaSap;
                    $object->fecha = $venta->fecha;
                    $object->total = $venta->total;
                    $object->tax = $venta->tax;
                    $object->tips = $venta->tips;
                    $object->amexAcc = $venta->amexAcc;
                    $object->creditoAcc = $venta->creditoAcc;
                    $object->debitoAcc = $venta->debitoAcc;
                    $object->mxnAcc = $venta->mxnAcc;
                    $object->usdAcc = $venta->usdAcc;
                    $object->eurAcc = $venta->eurAcc;
                    $object->cxcAcc = $venta->cxcAcc;
                    $object->diffAcc = $venta->diffAcc;
                    $object->clienteAcc = $venta->clienteAcc;

                    $sql = "SELECT vds.tender, SUM(vds.netSales) netSales , tacc.account FROM vds_tender AS vds INNER JOIN sucursales s ON s.id = vds.idSucursal INNER JOIN tender_account AS tacc ON (tacc.tender = vds.tender AND s.id= tacc.idSucursal ) WHERE NOT( vds.tender = 'Propina x Pagar') AND  fecha = ? AND vds.idSucursal = ? GROUP BY vds.tender,tacc.account;";
                    $tenders = DB::select($sql, [$fecha, $venta->id]);
                    $object->tenders = $tenders;
                    $objs[] = $object;
                }
            }
            return response()->json([
                'total' => count($ventas),
                'ventaAcc' => "4101-0001",
                'ivaAcc' => "2107-0102",
                'clienteAcc' => "1116-0100",
                'Ventas' => $objs
            ]);
        } else {
            return response()->json([
                'total' => 0,
                'ventas' => []
            ]);
        }
    }

    public function getArcos(Request $request)
    {
        if (!empty($request->input("token"))) {
            $objs = array();
            #$fecha = !empty($request->input("fecha")) ? $request->input("fecha") : date("Y-m-d", strtotime("-1 day"));
            $sql = "SELECT s.id, s.idSap, fecha, ROUND(vds.ventaNeta, 2) AS total, ROUND(vds.impuesto,2) AS tax, vds.servicio AS tips, s.amexAcc, s.creditoAcc, s.debitoAcc, s.mxnAcc, s.usdAcc, s.eurAcc,s.diffAcc, s.cxcAcc,s.clienteAcc FROM vta_suc_dia AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE s.id = 4 AND vds.fecha BETWEEN '2021-01-01' AND '2022-12-31';";
            $ventas = DB::select($sql, []);
            if (count($ventas)) {
                foreach ($ventas as $venta) {
                    $object = new \stdClass();
                    $object->id = $venta->idSap;
                    $object->fecha = $venta->fecha;
                    $object->total = $venta->total;
                    $object->tax = $venta->tax;
                    $object->tips = $venta->tips;
                    $object->amexAcc = $venta->amexAcc;
                    $object->creditoAcc = $venta->creditoAcc;
                    $object->debitoAcc = $venta->debitoAcc;
                    $object->mxnAcc = $venta->mxnAcc;
                    $object->usdAcc = $venta->usdAcc;
                    $object->eurAcc = $venta->eurAcc;
                    $object->cxcAcc = $venta->cxcAcc;
                    $object->diffAcc = $venta->diffAcc;
                    $object->clienteAcc = $venta->clienteAcc;

                    $sql = "SELECT TOTS.fecha, ROUND(SUM(IF(vds_tender.tender='American Express', TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS amex, ROUND(SUM(IF(vds_tender.tender='Tarjeta Credito', TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS credito, ROUND(SUM(IF(vds_tender.tender='Tarjeta Debito', TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS debito, ROUND(SUM(IF(vds_tender.tender IN ('Efectivo MXN','App Azteca'), TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS mxn, ROUND(SUM(IF(vds_tender.tender='Efectivo USD', TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS usd, ROUND(SUM(IF(vds_tender.tender='Efectivo EUROS', TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS eur, ROUND(SUM(IF( NOT ( vds_tender.tender IN ( 'American Express','Tarjeta Credito','Tarjeta Debito','Efectivo MXN','App Azteca','Efectivo USD','Efectivo EUROS','Propina x Pagar'  ) ) , TOTS.ventaBruta*(vds_tender.netSales/TOTS.totalTender),0)),2) AS cxc FROM (
                        SELECT VTA.idSucursal, VTA.fecha ,VTA.ventaBruta, TND.totalTender FROM 
                        (SELECT * FROM  vta_suc_dia WHERE fecha = ? AND idSucursal = 4) VTA LEFT JOIN 
                        (SELECT idSucursal,fecha, SUM(vds_tender.netSales) totalTender FROM vds_tender WHERE fecha = ? AND idSucursal = 4 AND NOT(tender IN ('Propina x Pagar','UBER', 'UBER PICKUP','Rappi','AppEKM') ) GROUP BY idSucursal, fecha) TND 
                        ON VTA.fecha = TND.fecha) AS TOTS INNER JOIN vds_tender ON (TOTS.fecha = vds_tender.fecha AND vds_tender.idSucursal = 4) WHERE TOTS.fecha = ? AND NOT(vds_tender.tender IN ('Propina x Pagar','UBER', 'UBER PICKUP','Rappi','AppEKM') ) GROUP BY TOTS.idSucursal;";

                    $tenders = DB::select($sql, [$venta->fecha, $venta->fecha, $venta->fecha]);

                    $object->amex = empty($tenders[0]->amex) ? 0 : $tenders[0]->amex;
                    $object->credito = empty($tenders[0]->credito) ? 0 : $tenders[0]->credito;
                    $object->debito = empty($tenders[0]->debito) ? 0 : $tenders[0]->debito;
                    $object->mxn = empty($tenders[0]->mxn) ? 0 : $tenders[0]->mxn;
                    $object->usd = empty($tenders[0]->usd) ? 0 : $tenders[0]->usd;
                    $object->eur = empty($tenders[0]->usd) ? 0 : $tenders[0]->eur;
                    $object->cxc = empty($tenders[0]->cxc) ? 0 : $tenders[0]->cxc;
                    $objs[] = $object;
                }
            }
            return response()->json([
                'total' => count($ventas),
                'ventaAcc' => "4101-0001",
                'ivaAcc' => "2107-0102",
                'clienteAcc' => "1116-0100",
                'Ventas' => $objs
            ]);
        } else {
            return response()->json([
                'total' => 0,
                'ventas' => []
            ]);
        }
    }

    public function get(Request $request)
    {
        if (!empty($request->input("token"))) {
            $objs = array();
            $fecha = !empty($request->input("fecha")) ? $request->input("fecha") : date("Y-m-d", strtotime("-1 day"));
            #$fecha ="2023-04-29";
            $sql = "SELECT s.id, s.idSap, fecha, ROUND(netSales-taxCollected, 2) AS total, ROUND(taxCollected,2) AS tax, ROUND(serviceCharges, 2) AS tips, s.amexAcc, s.creditoAcc, s.debitoAcc, s.mxnAcc, s.usdAcc, s.eurAcc,s.diffAcc, s.cxcAcc,s.clienteAcc FROM venta_diaria_sucursal AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE NOT(s.idSap LIKE 'HA%') AND s.idEmpresa = 1 AND  NOT(s.mxnAcc IS null) AND fecha = ?";
            //$sql = "SELECT s.id, s.idSap, fecha, ROUND(netSales-taxCollected, 2) AS total, ROUND(taxCollected,2) AS tax, ROUND(serviceCharges, 2) AS tips, s.amexAcc, s.creditoAcc, s.debitoAcc, s.mxnAcc, s.usdAcc, s.eurAcc,s.diffAcc, s.cxcAcc,s.clienteAcc FROM venta_diaria_sucursal AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE (s.idSap IN ('KAYSTSFE','LUCCASFE')) AND NOT(s.mxnAcc IS null) AND fecha BETWEEN ? AND ?";
            $ventas = DB::select($sql, [$fecha]);
            //$ventas = DB::select($sql,["2022-03-16","2022-03-19"]);
            if (count($ventas)) {
                foreach ($ventas as $venta) {
                    $object = new \stdClass();
                    $object->id = $venta->idSap;
                    $object->fecha = $venta->fecha;
                    $object->total = $venta->total;
                    $object->tax = $venta->tax;
                    $object->tips = $venta->tips;
                    $object->amexAcc = $venta->amexAcc;
                    $object->creditoAcc = $venta->creditoAcc;
                    $object->debitoAcc = $venta->debitoAcc;
                    $object->mxnAcc = $venta->mxnAcc;
                    $object->usdAcc = $venta->usdAcc;
                    $object->eurAcc = $venta->eurAcc;
                    $object->cxcAcc = $venta->cxcAcc;
                    $object->diffAcc = $venta->diffAcc;
                    $object->clienteAcc = $venta->clienteAcc;

                    $sql = "SELECT SUM(IF(vds.tender='American Express', vds.netSales,0)) AS amex, SUM(IF(vds.tender='Tarjeta Credito', vds.netSales,0)) AS credito, SUM(IF(vds.tender='Tarjeta Debito', vds.netSales,0)) AS debito, SUM(IF(vds.tender IN ('Efectivo MXN','App Azteca'), vds.netSales,0)) AS mxn, SUM(IF(vds.tender='Efectivo USD', vds.netSales,0)) AS usd, SUM(IF(vds.tender='Efectivo EUROS', vds.netSales,0)) AS eur, SUM(IF( NOT ( vds.tender IN ( 'American Express','Tarjeta Credito','Tarjeta Debito','Efectivo MXN','App Azteca','Efectivo USD','Efectivo EUROS','Propina x Pagar'  ) ) , vds.netSales,0)) AS cxc FROM vds_tender AS vds WHERE fecha = ? AND idSucursal = ? GROUP BY vds.idSucursal";
                    $tenders = DB::select($sql, [$fecha, $venta->id]);

                    $object->amex = empty($tenders[0]->amex) ? 0 : $tenders[0]->amex;
                    $object->credito = empty($tenders[0]->credito) ? 0 : $tenders[0]->credito;
                    $object->debito = empty($tenders[0]->debito) ? 0 : $tenders[0]->debito;
                    $object->mxn = empty($tenders[0]->mxn) ? 0 : $tenders[0]->mxn;
                    $object->usd = empty($tenders[0]->usd) ? 0 : $tenders[0]->usd;
                    $object->eur = empty($tenders[0]->usd) ? 0 : $tenders[0]->eur;
                    $object->cxc = empty($tenders[0]->cxc) ? 0 : $tenders[0]->cxc;
                    $objs[] = $object;
                }
            }
            return response()->json([
                'total' => count($ventas),
                'ventaAcc' => "4101-0001",
                'ivaAcc' => "2107-0102",
                'clienteAcc' => "1116-0100",
                'Ventas' => $objs
            ]);
        } else {
            return response()->json([
                'total' => 0,
                'ventas' => []
            ]);
        }
    }

    public function getHA(Request $request)
    {
        if (!empty($request->input("token"))) {
            $objs = array();
            $fecha = !empty($request->input("fecha")) ? $request->input("fecha") : date("Y-m-d", strtotime("-1 day"));
            #$fecha ="2023-04-29";
            $sql = "SELECT s.id, s.idSap, s.normaSap, fecha, ROUND(netSales-taxCollected, 2) AS total, ROUND(taxCollected,2) AS tax, ROUND(serviceCharges, 2) AS tips, s.amexAcc, s.creditoAcc, s.debitoAcc, s.mxnAcc, s.usdAcc, s.eurAcc,s.diffAcc, s.cxcAcc,s.clienteAcc FROM venta_diaria_sucursal AS vds INNER JOIN sucursales AS s ON s.id = vds.idSucursal WHERE s.idSap LIKE 'HA%' AND s.idEmpresa = 1 AND  NOT(s.mxnAcc IS null) AND fecha = ?";
            $ventas = DB::select($sql, [$fecha]);
            if (count($ventas)) {
                foreach ($ventas as $venta) {
                    $object = new \stdClass();
                    $object->id = $venta->normaSap;
                    $object->fecha = $venta->fecha;
                    $object->total = $venta->total;
                    $object->tax = $venta->tax;
                    $object->tips = $venta->tips;
                    $object->amexAcc = $venta->amexAcc;
                    $object->creditoAcc = $venta->creditoAcc;
                    $object->debitoAcc = $venta->debitoAcc;
                    $object->mxnAcc = $venta->mxnAcc;
                    $object->usdAcc = $venta->usdAcc;
                    $object->eurAcc = $venta->eurAcc;
                    $object->cxcAcc = $venta->cxcAcc;
                    $object->diffAcc = $venta->diffAcc;
                    $object->clienteAcc = $venta->clienteAcc;

                    $sql = "SELECT vds.tender, SUM(vds.netSales) netSales , tacc.account FROM vds_tender AS vds INNER JOIN sucursales s ON s.id = vds.idSucursal INNER JOIN tender_account AS tacc ON (tacc.tender = vds.tender AND s.id= tacc.idSucursal ) WHERE NOT( vds.tender = 'Propina x Pagar') AND  fecha = ? AND vds.idSucursal = ? GROUP BY vds.tender,tacc.account;";
                    $tenders = DB::select($sql, [$fecha, $venta->id]);
                    $object->tenders = $tenders;
                    $objs[] = $object;
                }
            }
            return response()->json([
                'total' => count($ventas),
                'ventaAcc' => "4101-0001",
                'ivaAcc' => "2107-0102",
                'clienteAcc' => "1116-0100",
                'Ventas' => $objs
            ]);
        } else {
            return response()->json([
                'total' => 0,
                'ventas' => []
            ]);
        }
    }

    public function set(Request $request)
    {
        $sql = "SELECT * FROM (SELECT CDD.itemNumber, MAX(CDD.fecha) UltimaVenta, SUM(CDD.countLine) cantidad FROM cheque_dia_detalle CDD WHERE CDD.fecha >= ? AND NOT (CDD.idSucursal IN (52,24,50)) GROUP BY CDD.itemNumber) VTA INNER JOIN micros_producto MP ON VTA.itemNumber = MP.idItemMicros;";
        $prods = DB::select($sql, ["2021-01-01"]);
        echo "<table>";
        foreach ($prods as $prod) {
            echo "<tr><td>" . $prod->itemNumber . "</td><td>" . $prod->itemName . "</td><td>" . $prod->UltimaVenta . "</td><td>" . $prod->cantidad . "</td></tr>";
        }
        echo "</table>";
    }

    public function mensual()
    {

        $sucs = DB::select("SELECT sucursales.idSap AS idSucursal, sucursales.nombre FROM sucursales WHERE sucursales.idTipo>0 AND estado = 1 AND NOT(idCategoria IN (10)) ORDER BY idEmpresa, nombre;");

        $location = new UserLocation();
        $hierachy = $location->getHierachy3(); 
        $menu = $this->menuReports();
        //dd($hierachy);
        return view('ventas.index', ['sucursales' => $sucs, 'menu' => $menu, 'hierachy' => $hierachy]);
        //return view('pruebitas.prueba2');
    }

    public function getMensual(Request $request)
    {
        $draw = !empty($request->input('draw')) ? $request->input('draw') : 1;

        $fecha = !empty($request->input('fechaIni')) ? $request->input('fechaIni') : date("Y-m");

        $locations = !empty($request->input('compania')) ? $request->input('compania') : 0;
        $typeLoc = $request->input('typeLoc');

        $tmpMY = explode("-", $fecha);

        $days = cal_days_in_month(CAL_GREGORIAN, $tmpMY[1], $tmpMY[0]);

        $fechaInicio = $fecha . "-01";
        $fechaFin = $fecha . "-" . ($days < 10 ? "0" : "") . $days;

        $location = new UserLocation();
        // if (session('DASHRole') == 1) {
        //     $location->get($locations, $typeLoc);

        //     // $sucs = DB::select("SELECT sucursales.idSap AS id FROM sucursales WHERE estado = 1 AND sucursales.idTipo>0 AND NOT(idCategoria IN (10)) " . (empty($compania) ? "" : " AND idEmpresa = $compania") . ";");
        // } else {
        //     // $sucs = DB::select("SELECT sucursales.idSap AS id FROM sucursales WHERE sucursales.idTipo>0 AND id IN (" . session('sucursales') . ") AND estado = 1 AND NOT(idCategoria IN (10)) " . (empty($compania) ? "" : " AND idEmpresa = $compania") . ";");
        //     $location->get($locations, $typeLoc);
        // }

        $location->get($locations, $typeLoc);
        $sucs = $location->locationSap;
        $sucsId = $location->locationID;
        $compania = $location->company == 'All' ? 0 : $location->company;
        $sucs = explode(',', $sucs);

        $emptySuc = array();

        $ventadet = array();

        $emptySuc["fecha"] = "";

        foreach ($sucs as $suc) {
            // $emptySuc[$suc->id] = 0;
            $emptySuc[$suc] = 0;
        }

        $emptySuc["diario"] = 0;
        $emptySuc["semana"] = 0;

        //$sql = "SELECT fecha, s.idSap, netSales FROM venta_diaria_sucursal dia INNER JOIN sucursales AS s ON s.id = dia.idSucursal  WHERE s.estado = 1 " . (empty($compania) ? "" : " AND s.idEmpresa IN ($compania)") . " AND s.id IN ($sucsId) AND fecha BETWEEN '$fechaInicio' AND '$fechaFin' ORDER BY fecha, idSucursal;";
        $sql = "SELECT fecha, s.idSap, netSales FROM venta_diaria_sucursal dia INNER JOIN sucursales AS s ON s.id = dia.idSucursal  WHERE s.estado = 1 AND fecha BETWEEN '$fechaInicio' AND '$fechaFin' ORDER BY fecha, idSucursal;";


        $venta = DB::select($sql);

        $mes = array();

        $fecha = "";
        $totalDia = 0;
        $totalSemana = 0;

        $diaArr = $emptySuc;
        $TotArr = $emptySuc;
        $TotArrSIVA = $emptySuc;
        $TotArr["fecha"] = "Total";
        $TotArrSIVA["fecha"] = "S IVA";
        $TotArr["diario"] = 0;
        $TotArrSIVA["diario"] = 0;
        $TotArr["semana"] = 0;
        $TotArrSIVA["semana"] = 0;
        foreach ($venta as $dia) {

            if ($fecha == "") {
                $diaArr = $emptySuc;
                $diaArr["fecha"] = $dia->fecha;
                $fecha = $dia->fecha;
            }

            if ($fecha != $dia->fecha) {

                $diaArr["diario"] = $totalDia;

                if (date("w", strtotime($fecha)) == 0) {
                    $diaArr["semana"] = $totalSemana;
                    $totalSemana =  0;
                } else {
                    $diaArr["semana"] = "";
                }

                $ventadet[] = $diaArr;
                $diaArr = $emptySuc;
                $totalDia = 0;

                $diaArr["fecha"] = $dia->fecha;
            }

            $fecha = $dia->fecha;
            $diaArr[$dia->idSap] = $dia->netSales;
            $totalDia += $dia->netSales;
            $totalSemana += $dia->netSales;
            $TotArr[$dia->idSap] = (empty($TotArr[$dia->idSap]) ? 0 : $TotArr[$dia->idSap]) + $dia->netSales;
            $TotArrSIVA[$dia->idSap] = $TotArr[$dia->idSap] / 1.16;
            $TotArr["diario"] += $dia->netSales;
            $TotArr["semana"] += $dia->netSales;

            $TotArrSIVA["diario"] += $dia->netSales / 1.16;
            $TotArrSIVA["semana"] += $dia->netSales / 1.16;
        }

        $diaArr["diario"] = $totalDia;
        $diaArr["semana"] = $totalSemana;
        $totalSemana =  0;
        $totalDia = 0;
        $ventadet[] = $diaArr;

        $diaArr = $emptySuc;

        $ventadet[] = $TotArr;
        $ventadet[] = $TotArrSIVA;
        $TotArr = $emptySuc;
        $TotArrSIVA = $emptySuc;
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => count($ventadet),
            'recordsFiltered' => count($ventadet),
            'data' => $ventadet
        ]);
    }

    public function getMensualXls(Request $request)
    {
        $fecha = !empty($request->input('fechaIni')) ? $request->input('fechaIni') : date("Y-m");

        $tmpMY = explode("-", $fecha);

        $days = cal_days_in_month(CAL_GREGORIAN, $tmpMY[1], $tmpMY[0]);

        $fechaInicio = $fecha . "-01";
        $fechaFin = $fecha . "-" . ($days < 10 ? "0" : "") . $days;

        if (session('DASHRole') == 1) {
            $sucs = DB::select("SELECT sucursales.idSap AS id, nombre FROM sucursales WHERE estado = 1 AND NOT(idCategoria IN (10)) ORDER BY sucursales.nombre;");
        } else {
            $sucs = DB::select("SELECT sucursales.idSap AS id, nombre FROM sucursales WHERE id IN (" . session('sucursales') . ") AND estado = 1 AND NOT(idCategoria IN (10)) ORDER BY sucursales.nombre;");
        }

        $emptySuc = array();

        $ventadet = array();

        $emptySuc["fecha"] = "";

        foreach ($sucs as $suc) {
            $emptySuc[$suc->id] = 0;
        }

        $emptySuc["diario"] = 0;
        $emptySuc["semana"] = 0;

        $sql = "SELECT fecha, s.idSap, netSales FROM venta_diaria_sucursal dia INNER JOIN sucursales AS s ON s.id = dia.idSucursal WHERE s.estado = 1 AND  fecha BETWEEN '$fechaInicio' AND '$fechaFin' ORDER BY fecha, s.nombre;";

        $venta = DB::select($sql);

        $mes = array();

        $fecha = "";
        $totalDia = 0;
        $totalSemana = 0;

        $diaArr = $emptySuc;
        $TotArr = $emptySuc;
        $TotArr["fecha"] = "Total";
        foreach ($venta as $dia) {

            if ($fecha == "") {
                $diaArr = $emptySuc;
                $diaArr["fecha"] = $dia->fecha;
                $fecha = $dia->fecha;
            }

            if ($fecha != $dia->fecha) {

                $diaArr["diario"] = $totalDia;

                if (date("w", strtotime($fecha)) == 0) {
                    $diaArr["semana"] = $totalSemana;
                    $totalSemana =  0;
                } else {
                    $diaArr["semana"] = "";
                }

                $ventadet[] = $diaArr;
                $diaArr = $emptySuc;
                $totalDia = 0;
                $diaArr["fecha"] = $dia->fecha;
            }

            $fecha = $dia->fecha;

            $diaArr[$dia->idSap] = $dia->netSales;
            $TotArr[$dia->idSap] += $dia->netSales;
            $totalDia += $dia->netSales;
            $totalSemana += $dia->netSales;

            $TotArr["diario"] += $dia->netSales;
            $TotArr["semana"] += $dia->netSales;
        }

        $diaArr["diario"] = $totalDia;
        $diaArr["semana"] = $totalSemana;
        $totalSemana =  0;
        $totalDia = 0;

        $ventadet[] = $diaArr;

        $diaArr = $emptySuc;

        $ventadet[] = $TotArr;

        $TotArr = $emptySuc;

        $spreadsheet = new Spreadsheet();
        $sugestedItems = $spreadsheet->getActiveSheet();
        $sugestedItems->setTitle('Concentrado');

        $sugestedItems->setCellValueByColumnAndRow(1, 1, 'Fecha');

        $col = 2;

        foreach ($sucs as $suc) {

            $sugestedItems->setCellValueByColumnAndRow($col, 1, $suc->nombre);

            $col++;
        }

        $sugestedItems->setCellValueByColumnAndRow($col, 1, "Diario");
        $col++;
        $sugestedItems->setCellValueByColumnAndRow($col, 1, "Semanal");
        $col++;

        $col = 1;
        $row = 2;
        foreach ($ventadet as $detalle) {
            $col = 1;
            #$sugestedItems->setCellValueByColumnAndRow($col,$row, $detalle->fecha);
            #$col++;
            foreach ($detalle as $rw) {
                $sugestedItems->setCellValueByColumnAndRow($col, $row, $rw);
                $col++;
            }
            $row++;
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="VENTA_' . $fecha . '.xlsx"');
        $writer->save("php://output");
    }

    public function getLastYearXls(Request $request)
    {

        if (empty($request->input('fecha'))) {
            $Year = date("d");
            $month = date("m");
            $day = (date("d") < 10 ? "0" : "") . (date("d") - 1);
        } else {
            $fecha = explode("-", $request->input('fecha'));
            $Year = $fecha[0];
            $month = $fecha[1];
            $day = $fecha[1];
            /*
            if (date("m") > $month && date("Y") <= $Year) {
                $day = date("d", strtotime($Year . "-" . ($month + 1) . "-01 -1 day"));
            } else {
                $day = (date("d") < 10 ? "0" : "") . (date("d") - 1);
            }
            */
        }

        if (empty($request->input('lastyear'))) {
            $lastYear = 2019;
        } else {
            $lastYear = $request->input('lastyear');
        }

        $empresas = array();
        $empresas[1] = 2019;
        $empresas[2] = 2019;
        $empresas[3] = 2019;
        $empresas[4] = 2020;

        $ventas = array();
        $dini = $Year . "-" . $month . "-" . "01";
        $dfin =  $Year . "-" . $month . "-" . $day;
        $dlyini = $lastYear . "-" . $month . "-" . "01";
        $dlyfin = $lastYear . "-" . $month . "-" . $day;

        $repDate1 = $dini . " to " . $dfin;
        $repDate2 = $dlyfin . " to " . $dlyfin;

        $styleTotal = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        $styleHeader = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];
        $spreadsheet = new Spreadsheet();

        $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $conditional1->addCondition('1');
        $conditional1->getStyle()->getFont()->getColor()->setARGB('FFFF2929');
        //\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED
        //$conditional1->getStyle()->getFont()->setBold(true);

        $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $conditional2->addCondition('1');
        $conditional2->getStyle()->getFont()->getColor()->setARGB('FF02C016');
        //\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN
        //$conditional2->getStyle()->getFont()->setBold(true);

        $sugestedItems = $spreadsheet->getActiveSheet();
        $sugestedItems->setTitle('Last Year');
        $row = 1;
        $sugestedItems->getColumnDimension('A')->setWidth(20);
        $sugestedItems->getColumnDimension('B')->setWidth(12);
        $sugestedItems->getColumnDimension('C')->setWidth(12);
        $sugestedItems->getColumnDimension('D')->setWidth(12);
        $sugestedItems->getColumnDimension('E')->setWidth(12);
        $sugestedItems->getColumnDimension('F')->setWidth(12);
        $sugestedItems->getColumnDimension('G')->setWidth(12);
        $sugestedItems->getColumnDimension('H')->setWidth(12);
        $sugestedItems->getColumnDimension('I')->setWidth(12);
        $sugestedItems->getColumnDimension('J')->setWidth(12);
        $sugestedItems->getColumnDimension('K')->setWidth(12);
        $sugestedItems->getColumnDimension('P')->setWidth(12);

        $idEmpresa = 1;

        $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.netSales AS CurrentNetSales, anterior.netSales AS LYNetSales, actual.netSales/anterior.netSales AS LY, 
        actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
        anterior.Institucional AS InstitucionalLY, actual.Vitrina/anterior.Vitrina AS VLY, actual.Salon/anterior.Salon AS SLY, actual.Delivery/anterior.Delivery AS DLY, 
        actual.Institucional/anterior.Institucional AS ILY FROM 
        (SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa= ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
        LEFT JOIN 
        (SELECT vds_rvc.idSucursal,MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
        ON actual.idSucursal = anterior.idSucursal ORDER BY actual.netSales desc;";

        $row = 1;
        foreach ($empresas as $idEmpresa => $lastYear) {
            $dlyini = $lastYear . "-" . $month . "-" . "01";
            $dlyfin = $lastYear . "-" . $month . "-" . $day;

            if ($idEmpresa > 1)
                $row += 2;

            $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.netSales AS CurrentNetSales, anterior.netSales AS LYNetSales, actual.netSales/anterior.netSales AS LY, 
            actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
            anterior.Institucional AS InstitucionalLY, actual.Vitrina/anterior.Vitrina AS VLY, actual.Salon/anterior.Salon AS SLY, actual.Delivery/anterior.Delivery AS DLY, 
            actual.Institucional/anterior.Institucional AS ILY FROM 
            (SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa= ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
            LEFT JOIN 
            (SELECT vds_rvc.idSucursal,MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
            ON actual.idSucursal = anterior.idSucursal ORDER BY actual.netSales desc;";
            $venta = DB::select($sql, [$idEmpresa, $dini, $dfin, $dlyini, $dlyfin]);

            $sql = "SELECT idSucursal, SUM(budget) monto FROM budget_dia_sucursal_o A INNER JOIN sucursales S ON A.idSucursal = S.id WHERE S.idEmpresa =? AND A.fecha BETWEEN ? AND ? GROUP BY idSucursal;";
            $budget = DB::select($sql, [$idEmpresa, $dini, $dfin]);
            $sucBudget = array();
            foreach ($budget as $b) {
                $sucBudget[$b->idSucursal] = $idEmpresa == 1 || $idEmpresa == 2 ? $b->monto * 1.16 : $b->monto;
            }

            $CurrentNetSales = 0;
            $LYNetSales = 0;
            $Vitrina = 0;
            $VitrinaLY = 0;
            $Salon = 0;
            $SalonLY = 0;
            $BudgetTotal = 0;
            $Delivery = 0;
            $DeliveryLY = 0;
            $Institucional = 0;
            $InstitucionalLY = 0;
            $ventasCerradas = array();

            $sugestedItems->setCellValue('A' . $row, 'Sucursal');
            $sugestedItems->setCellValue('B' . $row, 'Venta');
            $sugestedItems->setCellValue('C' . $row, 'Venta Anterior');
            $sugestedItems->setCellValue('D' . $row, 'LY');
            $sugestedItems->setCellValue('E' . $row, 'Vitrina');
            $sugestedItems->setCellValue('F' . $row, 'Vitrina Anterior');
            $sugestedItems->setCellValue('G' . $row, 'VLY');
            $sugestedItems->setCellValue('H' . $row, 'Salon');
            $sugestedItems->setCellValue('I' . $row, 'Salon Anterior');
            $sugestedItems->setCellValue('J' . $row, 'SLY');
            $sugestedItems->setCellValue('K' . $row, 'Delivery');
            $sugestedItems->setCellValue('L' . $row, 'Delivery Anterior');
            $sugestedItems->setCellValue('M' . $row, 'DLY');
            $sugestedItems->setCellValue('N' . $row, 'Catering');
            $sugestedItems->setCellValue('O' . $row, 'Catering Anterior');
            $sugestedItems->setCellValue('P' . $row, 'CLY');
            $sugestedItems->setCellValue('Q' . $row, 'Budget');
            $sugestedItems->setCellValue('R' . $row, 'Budget %');

            $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':R' . $row)->applyFromArray($styleHeader);

            $inicialRow = $row;
            foreach ($venta as $v) {
                if (empty($v->CurrentNetSales)) {
                    $ventasCerradas[] = ["sucursal" => $v->sucursal, "LYNetSales" =>  $v->LYNetSales];
                } else {

                    $row++;

                    $sugestedItems->setCellValueExplicit('A' . $row, $v->sucursal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sugestedItems->setCellValue('B' . $row, $v->CurrentNetSales);
                    $sugestedItems->setCellValue('C' . $row, $v->LYNetSales);
                    $sugestedItems->setCellValue('D' . $row, $v->LY);
                    $sugestedItems->setCellValue('E' . $row, $v->Vitrina);
                    $sugestedItems->setCellValue('F' . $row, $v->VitrinaLY);
                    $sugestedItems->setCellValue('G' . $row, $v->VLY);
                    $sugestedItems->setCellValue('H' . $row, $v->Salon);
                    $sugestedItems->setCellValue('I' . $row, $v->SalonLY);
                    $sugestedItems->setCellValue('J' . $row, $v->SLY);
                    $sugestedItems->setCellValue('K' . $row, $v->Delivery);
                    $sugestedItems->setCellValue('L' . $row, $v->DeliveryLY);
                    $sugestedItems->setCellValue('M' . $row, $v->DLY);
                    $sugestedItems->setCellValue('N' . $row, $v->Institucional);
                    $sugestedItems->setCellValue('O' . $row, $v->InstitucionalLY);
                    $sugestedItems->setCellValue('P' . $row, $v->ILY);
                    $sugestedItems->setCellValue('Q' . $row, !empty($sucBudget[$v->idSucursal]) ? $sucBudget[$v->idSucursal] : 0);
                    $sugestedItems->setCellValue('R' . $row, !empty($sucBudget[$v->idSucursal]) ? $v->CurrentNetSales / $sucBudget[$v->idSucursal] : 0);

                    $CurrentNetSales += $v->CurrentNetSales;
                    $LYNetSales += $v->LYNetSales;
                    $Vitrina += $v->Vitrina;
                    $VitrinaLY += $v->VitrinaLY;
                    $Salon += $v->Salon;
                    $SalonLY += $v->SalonLY;
                    $Delivery += $v->Delivery;
                    $DeliveryLY += $v->DeliveryLY;
                    $Institucional += $v->Institucional;
                    $InstitucionalLY += $v->InstitucionalLY;
                    $BudgetTotal += !empty($sucBudget[$v->idSucursal]) ? $sucBudget[$v->idSucursal] : 0;
                }
            }
            $row++;
            $sugestedItems->setCellValueExplicit('A' . $row, "Total", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sugestedItems->setCellValue('B' . $row, $CurrentNetSales);
            $sugestedItems->setCellValue('C' . $row, $LYNetSales);
            $sugestedItems->setCellValue('D' . $row, !empty($LYNetSales) ? ($CurrentNetSales / $LYNetSales) : 0);
            $sugestedItems->setCellValue('E' . $row, $Vitrina);
            $sugestedItems->setCellValue('F' . $row, $VitrinaLY);
            $sugestedItems->setCellValue('G' . $row, !empty($VitrinaLY) ? ($Vitrina / $VitrinaLY) : 0);
            $sugestedItems->setCellValue('H' . $row, $Salon);
            $sugestedItems->setCellValue('I' . $row, $SalonLY);
            $sugestedItems->setCellValue('J' . $row, !empty($SalonLY) ? ($Salon / $SalonLY) : 0);
            $sugestedItems->setCellValue('K' . $row, $Delivery);
            $sugestedItems->setCellValue('L' . $row, $DeliveryLY);
            $sugestedItems->setCellValue('M' . $row, !empty($DeliveryLY) ? ($Delivery / $DeliveryLY) : 0);
            $sugestedItems->setCellValue('N' . $row, $Institucional);
            $sugestedItems->setCellValue('O' . $row, $InstitucionalLY);
            $sugestedItems->setCellValue('P' . $row, !empty($InstitucionalLY) ? ($Institucional / $InstitucionalLY) : 0);
            $sugestedItems->setCellValue('Q' . $row, !empty($BudgetTotal) ? $BudgetTotal : 0);
            $sugestedItems->setCellValue('R' . $row, !empty($BudgetTotal) ? $CurrentNetSales / $BudgetTotal : 0);

            $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':R' . $row)->applyFromArray($styleTotal);


            $sugestedItems->getStyle('A' . $inicialRow . ':R' . $row)
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $sugestedItems->getStyle('B' . $inicialRow . ':R' . $row)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('D' . ($inicialRow + 1) . ':D' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('D' . ($inicialRow + 1) . ':D' . $row)->setConditionalStyles($conditionalStyles);
            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('G' . ($inicialRow + 1) . ':G' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('G' . ($inicialRow + 1) . ':G' . $row)->setConditionalStyles($conditionalStyles);
            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('J' . ($inicialRow + 1) . ':J' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('J' . ($inicialRow + 1) . ':J' . $row)->setConditionalStyles($conditionalStyles);
            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('M' . ($inicialRow + 1) . ':M' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('M' . ($inicialRow + 1) . ':M' . $row)->setConditionalStyles($conditionalStyles);
            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('P' . ($inicialRow + 1) . ':P' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('P' . ($inicialRow + 1) . ':P' . $row)->setConditionalStyles($conditionalStyles);
            $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('R' . ($inicialRow + 1) . ':R' . $row)->getConditionalStyles();
            $conditionalStyles[] = $conditional1;
            $conditionalStyles[] = $conditional2;
            $spreadsheet->getActiveSheet()->getStyle('R' . ($inicialRow + 1) . ':R' . $row)->setConditionalStyles($conditionalStyles);

            $spreadsheet->getActiveSheet()->getStyle('B' . $inicialRow . ':R' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $spreadsheet->getActiveSheet()->getStyle('D' . $inicialRow . ':D' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $spreadsheet->getActiveSheet()->getStyle('J' . $inicialRow . ':J' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $spreadsheet->getActiveSheet()->getStyle('G' . $inicialRow . ':G' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $spreadsheet->getActiveSheet()->getStyle('M' . $inicialRow . ':M' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $spreadsheet->getActiveSheet()->getStyle('P' . $inicialRow . ':P' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
            $spreadsheet->getActiveSheet()->getStyle('R' . $inicialRow . ':R' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

            if (!empty($ventasCerradas)) {
                $row += 2;
                $inicialRow = $row;
                $LYNetSales = 0;
                $sugestedItems->setCellValue('A' . $row, 'Sucursal');
                $sugestedItems->setCellValue('B' . $row, 'Venta LY');
                $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':B' . $row)->applyFromArray($styleHeader);
                foreach ($ventasCerradas as $v) {
                    $row++;
                    $sugestedItems->setCellValueExplicit('A' . $row, $v["sucursal"], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sugestedItems->setCellValue('B' . $row, $v["LYNetSales"]);
                    $LYNetSales += $v["LYNetSales"];
                }
                $row++;
                $sugestedItems->setCellValueExplicit('A' . $row, "Total", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sugestedItems->setCellValue('B' . $row, $LYNetSales);
                $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':B' . $row)->applyFromArray($styleTotal);

                $sugestedItems->getStyle('A' . $inicialRow . ':B' . $row)
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

                $spreadsheet->getActiveSheet()->getStyle('B' . $inicialRow . ':B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        /*
        $row+=2;
        $inicialRow=$row;
        $sugestedItems->setCellValue('A'.$row, 'Sucursal');
		$sugestedItems->setCellValue('B'.$row, 'Venta');
		$sugestedItems->setCellValue('C'.$row, 'LY');
		$sugestedItems->setCellValue('D'.$row, 'Vitrina');
		$sugestedItems->setCellValue('E'.$row, 'VLY');
        $sugestedItems->setCellValue('F'.$row, 'Salon');
        $sugestedItems->setCellValue('G'.$row, 'SLY');
        $sugestedItems->setCellValue('H'.$row, 'Delivery');
        $sugestedItems->setCellValue('I'.$row, 'DLY');
        $sugestedItems->setCellValue('J'.$row, 'Catering');
        $sugestedItems->setCellValue('K'.$row, 'CLY');
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($styleHeader);

        $ventasCerradas = array();

        $idEmpresa = 2;
        $venta = DB::select($sql,[$idEmpresa,$dini, $dfin, $dlyini, $dlyfin]);
        $CurrentNetSales = 0;
        $LYNetSales = 0;
        $Vitrina = 0;
        $VitrinaLY = 0;
        $Salon = 0;
        $SalonLY = 0;
        $Delivery = 0;
        $DeliveryLY = 0;
        $Institucional = 0;
        $InstitucionalLY = 0;
        
        foreach($venta AS $v)
        {
            if(empty($v->CurrentNetSales)){
                $ventasCerradas[]= ["sucursal" => $v->sucursal, "LYNetSales" =>  number_format($v->LYNetSales,0,".",",")];
            } else {
                
                $row++;

                $sugestedItems->setCellValueExplicit('A'.$row, $v->sucursal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
                $sugestedItems->setCellValue('B'.$row, $v->CurrentNetSales);
                $sugestedItems->setCellValue('C'.$row, $v->LY);
                $sugestedItems->setCellValue('D'.$row, $v->Vitrina);
                $sugestedItems->setCellValue('E'.$row, $v->VLY);
                $sugestedItems->setCellValue('F'.$row, $v->Salon);
                $sugestedItems->setCellValue('G'.$row, $v->SLY);
                $sugestedItems->setCellValue('H'.$row, $v->Delivery);
                $sugestedItems->setCellValue('I'.$row, $v->DLY);
                $sugestedItems->setCellValue('J'.$row, $v->Institucional);
                $sugestedItems->setCellValue('K'.$row, $v->ILY);

                $CurrentNetSales += $v->CurrentNetSales;
                $LYNetSales += $v->LYNetSales;
                $Vitrina += $v->Vitrina;
                $VitrinaLY += $v->VitrinaLY;
                $Salon += $v->Salon;
                $SalonLY += $v->SalonLY;
                $Delivery += $v->Delivery;
                $DeliveryLY += $v->DeliveryLY;
                $Institucional += $v->Institucional;
                $InstitucionalLY += $v->InstitucionalLY;
            }

        }
        $row++;
        $sugestedItems->setCellValueExplicit('A'.$row, "Total", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
        $sugestedItems->setCellValue('B'.$row, $CurrentNetSales);
        $sugestedItems->setCellValue('C'.$row, !empty($LYNetSales)?number_format(($CurrentNetSales*100/$LYNetSales),0):0);
        $sugestedItems->setCellValue('D'.$row, $Vitrina);
        $sugestedItems->setCellValue('E'.$row, !empty($VitrinaLY)?number_format(($Vitrina*100/$VitrinaLY),0):0);
        $sugestedItems->setCellValue('F'.$row, $Salon);
        $sugestedItems->setCellValue('G'.$row, !empty($SalonLY)?number_format(($Salon*100/$SalonLY),0):0);
        $sugestedItems->setCellValue('H'.$row, $Delivery);
        $sugestedItems->setCellValue('I'.$row, !empty($DeliveryLY)?number_format(($Delivery*100/$DeliveryLY),0):0);
        $sugestedItems->setCellValue('J'.$row, $Institucional);
        $sugestedItems->setCellValue('K'.$row, !empty($InstitucionalLY)?number_format(($Institucional*100/$InstitucionalLY),0):0);
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($styleTotal);

        $sugestedItems->getStyle('A1:K'.$row)
        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sugestedItems->getStyle('B1:K'.$row)
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->setConditionalStyles($conditionalStyles);

        $lastYear = 2020;
        $dlyini = $lastYear . "-". $month. "-" ."01";
        $dlyfin = $lastYear . "-". $month. "-" ."30";

        $row+=2;
        $sugestedItems->setCellValue('A'.$row, 'Sucursal');
		$sugestedItems->setCellValue('B'.$row, 'Venta');
		$sugestedItems->setCellValue('C'.$row, 'LY');
		$sugestedItems->setCellValue('D'.$row, 'Vitrina');
		$sugestedItems->setCellValue('E'.$row, 'VLY');
        $sugestedItems->setCellValue('F'.$row, 'Salon');
        $sugestedItems->setCellValue('G'.$row, 'SLY');
        $sugestedItems->setCellValue('H'.$row, 'Delivery');
        $sugestedItems->setCellValue('I'.$row, 'DLY');
        $sugestedItems->setCellValue('J'.$row, 'Catering');
        $sugestedItems->setCellValue('K'.$row, 'CLY');
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($styleHeader);

        $ventasCerradas = array();
        $idEmpresa = 3;
        $venta = DB::select($sql,[$idEmpresa,$dini, $dfin, $dlyini, $dlyfin]);
        $CurrentNetSales = 0;
        $LYNetSales = 0;
        $Vitrina = 0;
        $VitrinaLY = 0;
        $Salon = 0;
        $SalonLY = 0;
        $Delivery = 0;
        $DeliveryLY = 0;
        $Institucional = 0;
        $InstitucionalLY = 0;

        foreach($venta AS $v)
        {
            if(empty($v->CurrentNetSales)){
                $ventasCerradas[]= ["sucursal" => $v->sucursal, "LYNetSales" =>  number_format($v->LYNetSales,0,".",",")];
            } else {
                
                $row++;

                $sugestedItems->setCellValueExplicit('A'.$row, $v->sucursal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
                $sugestedItems->setCellValue('B'.$row, $v->CurrentNetSales);
                $sugestedItems->setCellValue('C'.$row, $v->LY);
                $sugestedItems->setCellValue('D'.$row, $v->Vitrina);
                $sugestedItems->setCellValue('E'.$row, $v->VLY);
                $sugestedItems->setCellValue('F'.$row, $v->Salon);
                $sugestedItems->setCellValue('G'.$row, $v->SLY);
                $sugestedItems->setCellValue('H'.$row, $v->Delivery);
                $sugestedItems->setCellValue('I'.$row, $v->DLY);
                $sugestedItems->setCellValue('J'.$row, $v->Institucional);
                $sugestedItems->setCellValue('K'.$row, $v->ILY);

                $CurrentNetSales += $v->CurrentNetSales;
                $LYNetSales += $v->LYNetSales;
                $Vitrina += $v->Vitrina;
                $VitrinaLY += $v->VitrinaLY;
                $Salon += $v->Salon;
                $SalonLY += $v->SalonLY;
                $Delivery += $v->Delivery;
                $DeliveryLY += $v->DeliveryLY;
                $Institucional += $v->Institucional;
                $InstitucionalLY += $v->InstitucionalLY;
            }
        }
        $row++;
        $sugestedItems->setCellValueExplicit('A'.$row, "Total", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
        $sugestedItems->setCellValue('B'.$row, $CurrentNetSales);
        $sugestedItems->setCellValue('C'.$row, !empty($LYNetSales)?number_format(($CurrentNetSales*100/$LYNetSales),0):0);
        $sugestedItems->setCellValue('D'.$row, $Vitrina);
        $sugestedItems->setCellValue('E'.$row, !empty($VitrinaLY)?number_format(($Vitrina*100/$VitrinaLY),0):0);
        $sugestedItems->setCellValue('F'.$row, $Salon);
        $sugestedItems->setCellValue('G'.$row, !empty($SalonLY)?number_format(($Salon*100/$SalonLY),0):0);
        $sugestedItems->setCellValue('H'.$row, $Delivery);
        $sugestedItems->setCellValue('I'.$row, !empty($DeliveryLY)?number_format(($Delivery*100/$DeliveryLY),0):0);
        $sugestedItems->setCellValue('J'.$row, $Institucional);
        $sugestedItems->setCellValue('K'.$row, !empty($InstitucionalLY)?number_format(($Institucional*100/$InstitucionalLY),0):0);
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($styleTotal);

        $sugestedItems->getStyle('A1:K'.$row)
        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sugestedItems->getStyle('B1:K'.$row)
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->setConditionalStyles($conditionalStyles);

        $lastYear = 2020;
        $dlyini = $lastYear . "-". $month. "-" ."01";
        $dlyfin = $lastYear . "-". $month. "-" ."30";

        $idEmpresa = 4;
        $venta = DB::select($sql,[$idEmpresa,$dini, $dfin, $dlyini, $dlyfin]);

        foreach($venta AS $v)
        {
            if(empty($v->CurrentNetSales)){
                $ventasCerradas[]= ["sucursal" => $v->sucursal, "LYNetSales" =>  number_format($v->LYNetSales,0,".",",")];
            } else {
                
                $row++;

                $sugestedItems->setCellValueExplicit('A'.$row, $v->sucursal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
                $sugestedItems->setCellValue('B'.$row, $v->CurrentNetSales);
                $sugestedItems->setCellValue('C'.$row, $v->LY);
                $sugestedItems->setCellValue('D'.$row, $v->Vitrina);
                $sugestedItems->setCellValue('E'.$row, $v->VLY);
                $sugestedItems->setCellValue('F'.$row, $v->Salon);
                $sugestedItems->setCellValue('G'.$row, $v->SLY);
                $sugestedItems->setCellValue('H'.$row, $v->Delivery);
                $sugestedItems->setCellValue('I'.$row, $v->DLY);
                $sugestedItems->setCellValue('J'.$row, $v->Institucional);
                $sugestedItems->setCellValue('K'.$row, $v->ILY);

                $CurrentNetSales += $v->CurrentNetSales;
                $LYNetSales += $v->LYNetSales;
                $Vitrina += $v->Vitrina;
                $VitrinaLY += $v->VitrinaLY;
                $Salon += $v->Salon;
                $SalonLY += $v->SalonLY;
                $Delivery += $v->Delivery;
                $DeliveryLY += $v->DeliveryLY;
                $Institucional += $v->Institucional;
                $InstitucionalLY += $v->InstitucionalLY;
            }

        }

        $row++;
        $sugestedItems->setCellValueExplicit('A'.$row, "Total", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
        $sugestedItems->setCellValue('B'.$row, $CurrentNetSales);
        $sugestedItems->setCellValue('C'.$row, !empty($LYNetSales)?number_format(($CurrentNetSales*100/$LYNetSales),0):0);
        $sugestedItems->setCellValue('D'.$row, $Vitrina);
        $sugestedItems->setCellValue('E'.$row, !empty($VitrinaLY)?number_format(($Vitrina*100/$VitrinaLY),0):0);
        $sugestedItems->setCellValue('F'.$row, $Salon);
        $sugestedItems->setCellValue('G'.$row, !empty($SalonLY)?number_format(($Salon*100/$SalonLY),0):0);
        $sugestedItems->setCellValue('H'.$row, $Delivery);
        $sugestedItems->setCellValue('I'.$row, !empty($DeliveryLY)?number_format(($Delivery*100/$DeliveryLY),0):0);
        $sugestedItems->setCellValue('J'.$row, $Institucional);
        $sugestedItems->setCellValue('K'.$row, !empty($InstitucionalLY)?number_format(($Institucional*100/$InstitucionalLY),0):0);
        
        $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($styleTotal);
        $sugestedItems->getStyle('A1:K'.$row)
        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sugestedItems->getStyle('B1:K'.$row)
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('C'.$inicialRow.':C'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('E'.$inicialRow.':E'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('G'.$inicialRow.':G'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('I'.$inicialRow.':I'.$row)->setConditionalStyles($conditionalStyles);
        $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;
        $conditionalStyles[] = $conditional2;
        $spreadsheet->getActiveSheet()->getStyle('K'.$inicialRow.':K'.$row)->setConditionalStyles($conditionalStyles);
        */

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="LY_' . $Year . $month . $day . '.xlsx"');
        $writer->save("php://output");
    }

    public function getLastYear(Request $request)
    {
        if (empty($request->input('fecha'))) {

            $Year = date("Y");
            $month = date("m");
            $day = date("d");
            $day = ($day < 10 ? "0" : "") . ($day - 1);
        } else {
            $fecha = explode("-", $request->input('fecha'));
            $Year = $fecha[0];
            $month = $fecha[1];
            $day = $fecha[2];
        }

        $ventas = array();

        $location = new UserLocation();
        $empresasUsuario = $location->getHierachy3(1);

        $compania = empty($request->input('compania')) ? 0 : $request->input('compania');

        if (empty($request->input('compania'))) {
            $whereEmpresas = "";
            foreach ($empresasUsuario as $empresa) {
                $whereEmpresas .= (!empty($whereEmpresas) ? "," : "") . $empresa["id"];
            }
            $whereEmpresas = " idEmpresa IN (" . $whereEmpresas . ")";
        } else {
            $whereEmpresas = " idEmpresa = ?";
        }

        $sql = "SELECT * FROM empresas WHERE $whereEmpresas AND estado =1;";

        $empresas = DB::select($sql, [$compania]);
        foreach ($empresas as $empresa) {
            $ventas[$empresa->idEmpresa] = null;
            $ventas[$empresa->idEmpresa][0] = $empresa->comun;
            $ventas[$empresa->idEmpresa][1] = null;
            $ventas[$empresa->idEmpresa][2] = null;

            if (empty($request->input('vsa'))) {
                $lastYear = date('Y') - 1;
            } else {
                $lastYear = $request->input('vsa');
            }

            $dini = $Year . "-" . $month . "-" . "01";
            $dfin =  $Year . "-" . $month . "-" . $day;
            $dlyini = $lastYear . "-" . $month . "-" . "01";
            $dlyfin = $lastYear . "-" . $month . "-" . $day;

            $repDate1 = $dini . " to " . $dfin;
            $repDate2 = $dlyini . " to " . $dlyfin;

            $idEmpresa = $empresa->idEmpresa;

            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) <= ? AND MAX(fecha) < ? UNION ALL SELECT vds_rvc.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal HAVING SUM(netSales)=0 ) AS venta GROUP BY tipo;";
            $cerradas = DB::select($sql, [$idEmpresa, $dlyfin, $dini, $idEmpresa, $dini, $dfin]);

            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_rvc.idSucursal, 'Abiertas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_rvc  INNER JOIN sucursales AS s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa = ? AND vds_rvc.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) > ? AND MAX(fecha) >= ?) AS venta GROUP BY tipo;";
            $abiertas = DB::select($sql, [$idEmpresa, $dlyfin, $dini]);

            //$sucursalesExcluidas = empty($abiertas[0]) ? null : $abiertas[0]->sucursales;
            //$sucursalesExcluidas = empty($cerradas[0]) ? $sucursalesExcluidas : ((empty($sucursalesExcluidas) ? "" : $sucursalesExcluidas . (empty($cerradas) ? "" : ",")) . $cerradas[0]->sucursales);
            $abiertasArr = array();

            if (!empty($abiertas[0]))
                $abiertasArr = explode(",", $abiertas[0]->sucursales);

            $sucursalesExcluidas = empty($cerradas[0]) ? null : ((empty($sucursalesExcluidas) ? "" : $sucursalesExcluidas . (empty($cerradas) ? "" : ",")) . $cerradas[0]->sucursales);
            //dd($sucursalesExcluidas);
            $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.netSales AS CurrentNetSales, anterior.netSales AS LYNetSales, actual.netSales*100/anterior.netSales AS LY, 
            actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
            anterior.Institucional AS InstitucionalLY, actual.Vitrina*100/anterior.Vitrina AS VLY, actual.Salon*100/anterior.Salon AS SLY, actual.Delivery*100/anterior.Delivery AS DLY, 
            actual.Institucional*100/anterior.Institucional AS ILY, 0 AS Budget, 0 AS BudgetMon FROM 
            (SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE " . (!empty($sucursalesExcluidas) ? " NOT (s.id IN (" . $sucursalesExcluidas . ")) AND " : "") . " s.idEmpresa= ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
            LEFT JOIN 
            (SELECT vds_rvc.idSucursal,MONTH(fecha), SUM(vds_rvc.netSales) netSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
            ON actual.idSucursal = anterior.idSucursal ORDER BY actual.netSales desc;";

            $venta = DB::select($sql, [$idEmpresa, $dini, $dfin, $dlyini, $dlyfin]);

            $sql = "SELECT idSucursal, SUM(budget) monto FROM budget_dia_sucursal_o A INNER JOIN sucursales S ON A.idSucursal = S.id WHERE S.idEmpresa =? AND A.fecha BETWEEN ? AND ? GROUP BY idSucursal;";
            $budget = DB::select($sql, [$idEmpresa, $dini, $dfin]);
            $sucBudget = array();
            foreach ($budget as $b) {
                //$sucBudget[$b->idSucursal] = $b->monto;
                $sucBudget[$b->idSucursal] = $idEmpresa == 1 || $idEmpresa == 2 ?  $b->monto * 1.16 : $b->monto;
            }

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

            foreach ($venta as $monto) {
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
                $monto->Budget = !empty($sucBudget[$monto->idSucursal]) && !empty($monto->CurrentNetSales) ? $monto->CurrentNetSales / $sucBudget[$monto->idSucursal] * 100 : 0;
                $monto->BudgetMon = !empty($sucBudget[$monto->idSucursal]) && !empty($monto->CurrentNetSales) ? number_format($sucBudget[$monto->idSucursal], 0, ".", ",") : 0;
                if (!in_array($monto->idSucursal, $abiertasArr)) {
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
            }

            $object = new \stdClass();
            $object->idSucursal = 0;
            $object->idEmpresa = $idEmpresa;
            $object->sucursal = "Total";
            $object->CurrentNetSales = number_format($CurrentNetSales, 0, ".", ",");
            $object->LYNetSales = number_format($LYNetSales, 0, ".", ",");
            $object->LY =  !empty($LYNetSales) ? number_format(($CurrentNetSales * 100 / $LYNetSales), 0) : 0;
            $object->Vitrina = number_format($Vitrina, 0, ".", ",");
            $object->VitrinaLY = number_format($VitrinaLY, 0, ".", ",");
            $object->Salon = number_format($Salon, 0, ".", ",");
            $object->SalonLY = number_format($SalonLY, 0, ".", ",");
            $object->Delivery = number_format($Delivery, 0, ".", ",");
            $object->DeliveryLY = number_format($DeliveryLY, 0, ".", ",");
            $object->Institucional = number_format($Institucional, 0, ".", ",");
            $object->InstitucionalLY = number_format($InstitucionalLY, 0, ".", ",");
            $object->VLY = !empty($VitrinaLY) ? number_format(($Vitrina * 100 / $VitrinaLY), 0) : 0;
            $object->SLY = !empty($SalonLY) ? number_format(($Salon * 100 / $SalonLY), 0) : 0;
            $object->DLY = !empty($DeliveryLY) ? number_format(($Delivery * 100 / $DeliveryLY), 0) : 0;
            $object->ILY = !empty($InstitucionalLY) ? number_format(($Institucional * 100 / $InstitucionalLY), 0) : 0;
            $object->Budget = !empty($BudgetTotal) ? number_format(($CurrentNetSales * 100 / $BudgetTotal), 0) : 0;
            $object->BudgetMon = !empty($BudgetTotal) ? number_format($BudgetTotal, 0, ".", ","): 0;
            $venta = $this->parser($venta);
            $venta[] = $object;
            $object = new \stdClass();
            $object->idSucursal = 0;
            $object->idEmpresa = $idEmpresa;
            $object->sucursal = "MT";
            $object->CurrentNetSales = number_format($CurrentNetSalesMT, 0, ".", ",");
            $object->LYNetSales = number_format($LYNetSalesMT, 0, ".", ",");
            $object->LY =  !empty($LYNetSalesMT) ? number_format(($CurrentNetSalesMT * 100 / $LYNetSalesMT), 0) : 0;
            $object->Vitrina = number_format($VitrinaMT, 0, ".", ",");
            $object->VitrinaLY = number_format($VitrinaLYMT, 0, ".", ",");
            $object->Salon = number_format($SalonMT, 0, ".", ",");
            $object->SalonLY = number_format($SalonLYMT, 0, ".", ",");
            $object->Delivery = number_format($DeliveryMT, 0, ".", ",");
            $object->DeliveryLY = number_format($DeliveryLYMT, 0, ".", ",");
            $object->Institucional = number_format($InstitucionalMT, 0, ".", ",");
            $object->InstitucionalLY = number_format($InstitucionalLYMT, 0, ".", ",");
            $object->VLY = !empty($VitrinaLYMT) ? number_format(($VitrinaMT * 100 / $VitrinaLYMT), 0) : 0;
            $object->SLY = !empty($SalonLYMT) ? number_format(($SalonMT * 100 / $SalonLYMT), 0) : 0;
            $object->DLY = !empty($DeliveryLYMT) ? number_format(($DeliveryMT * 100 / $DeliveryLYMT), 0) : 0;
            $object->ILY = !empty($InstitucionalLYMT) ? number_format(($InstitucionalMT * 100 / $InstitucionalLYMT), 0) : 0;
            $object->Budget = !empty($BudgetTotalMT) ? number_format(($CurrentNetSalesMT * 100 / $BudgetTotalMT), 0) : 0;
            $object->BudgetMon = !empty($BudgetTotalMT) ?number_format($BudgetTotalMT, 0, ".", ",") : 0;

            $venta[] = $object;

            $object = new \stdClass();
            $object->idSucursal = 0;
            $object->idEmpresa = $idEmpresa;
            $object->sucursal = "OT";
            $object->CurrentNetSales = number_format($CurrentNetSales - $CurrentNetSalesMT, 0, ".", ",");
            $object->LYNetSales = number_format($LYNetSales - $LYNetSalesMT, 0, ".", ",");
            $object->LY =  !empty($LYNetSales - $LYNetSalesMT) ? number_format((($CurrentNetSales - $CurrentNetSalesMT) * 100 / ($LYNetSales - $LYNetSalesMT)), 0) : 0;
            $object->Vitrina = number_format($Vitrina - $VitrinaMT, 0, ".", ",");
            $object->VitrinaLY = number_format($VitrinaLY - $VitrinaLYMT, 0, ".", ",");
            $object->Salon = number_format($Salon - $SalonMT, 0, ".", ",");
            $object->SalonLY = number_format($SalonLY - $SalonLYMT, 0, ".", ",");
            $object->Delivery = number_format($Delivery - $DeliveryMT, 0, ".", ",");
            $object->DeliveryLY = number_format($DeliveryLY - $DeliveryLYMT, 0, ".", ",");
            $object->Institucional = number_format($Institucional - $InstitucionalMT, 0, ".", ",");
            $object->InstitucionalLY = number_format($InstitucionalLY - $InstitucionalLYMT, 0, ".", ",");
            $object->VLY = !empty($VitrinaLY - $VitrinaLYMT) ? number_format((($Vitrina - $VitrinaMT) * 100 / ($VitrinaLY - $VitrinaLYMT)), 0) : 0;
            $object->SLY = !empty($SalonLY - $SalonLYMT) ? number_format(((-$SalonMT) * 100 / ($SalonLY - $SalonLYMT)), 0) : 0;
            $object->DLY = !empty($DeliveryLY - $DeliveryLYMT) ? number_format((($Delivery - $DeliveryMT) * 100 / ($DeliveryLY - $DeliveryLYMT)), 0) : 0;
            $object->ILY = !empty($InstitucionalLY - $InstitucionalLYMT) ? number_format((($Institucional - $InstitucionalMT) * 100 / ($InstitucionalLY - $InstitucionalLYMT)), 0) : 0;
            $object->Budget = !empty($BudgetTotal - $BudgetTotalMT) ? number_format((($LYNetSales - $LYNetSalesMT) * 100 / ($BudgetTotal - $BudgetTotalMT)), 0) : 0;
            $object->BudgetMon = !empty($BudgetTotal - $BudgetTotalMT) ? number_format($BudgetTotal - $BudgetTotalMT, 0, ".", ",") : 0;
            $venta[] = $object;
            //dd($venta);
            $ventas[$idEmpresa][1] = $venta;
            $vAbiertas = array();
            $vCerradas = array();
            if (!empty($abiertas[0])) {
                $sql = "SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), SUM(vds_rvc.netSales) CurrentNetSales, 0 AS 'LYNetSales' ,SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa= ? AND s.id IN (" . $abiertas[0]->sucursales . ") AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)";
                $vAbiertas = DB::select($sql, [$idEmpresa, $dini, $dfin]);
            }

            if (!empty($cerradas[0])) {
                $sql = "SELECT s.idEmpresa, s.nombre AS sucursal, vds_rvc.idSucursal, MONTH(fecha), 0 AS 'CurrentNetSales', SUM(vds_rvc.netSales) LYNetSales, SUM(IF(vds_rvc.rvc IN('Vitrina','RVC 6'),vds_rvc.netSales,0)) Vitrina, SUM(IF(vds_rvc.rvc IN ('Salon','Restaurant'),vds_rvc.netSales,0)) Salon, SUM(IF(vds_rvc.rvc='Institucional',vds_rvc.netSales,0)) Institucional, SUM(IF(vds_rvc.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_rvc.netSales,0)) Delivery  FROM vds_rvc INNER JOIN sucursales s ON s.id = vds_rvc.idSucursal WHERE s.idEmpresa= ? AND s.id IN (" . $cerradas[0]->sucursales . ") AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)";
                $vCerradas = DB::select($sql, [$idEmpresa, $dlyini, $dlyfin]);
            }

            $ventas[$empresa->idEmpresa][2] = array();
            $montoCerradas = 0;
            $montoAperturas = 0;

            foreach ($vCerradas as $v) {
                $montoCerradas += $v->LYNetSales;
            }

            foreach ($vAbiertas as $v) {
                $montoAperturas += $v->CurrentNetSales;
            }

            if (count($vAbiertas) > 0 || count($vCerradas) > 0) {
                $totales = array();
                $totalAperturas = new \stdClass();
                $totalAperturas->idEmpresa = $idEmpresa;
                $totalAperturas->sucursal = "Total";
                $totalAperturas->idSucursal = 0;
                $totalAperturas->CurrentNetSales = number_format($montoAperturas, 0, ".", ",");
                $totalAperturas->LYNetSales = number_format($montoCerradas, 0, ".", ",");
                $totalAperturas->LY =  !empty($montoCerradas) ? number_format(($montoAperturas * 100 / $montoCerradas), 0) : 0;
                $totales[] = $totalAperturas;

                $totalAperturas = new \stdClass();
                $totalAperturas->idEmpresa = $idEmpresa;
                $totalAperturas->sucursal = "Total";
                $totalAperturas->idSucursal = 0;
                $totalAperturas->CurrentNetSales = number_format($CurrentNetSales + $montoAperturas, 0, ".", ",");
                $totalAperturas->LYNetSales = number_format($LYNetSales + $montoCerradas, 0, ".", ",");
                $totalAperturas->LY =  !empty(($LYNetSales + $montoCerradas)) ? number_format((($CurrentNetSales + $montoAperturas) * 100 / ($LYNetSales + $montoCerradas)), 0) : 0;
                $totales[] = $totalAperturas;

                $ventas[$empresa->idEmpresa][2] = array_merge($this->parser($vAbiertas, 2), $this->parser($vCerradas, 2), $totales);
            }
        }
        $menu = $this->menuReports();
        
        return view('ventas.lastyear', [
            "ventas" => $ventas,
            "fechaActual" => $repDate1,
            "fechaVS" => $repDate2,
            'menu' => $menu,
            "dfin" => $dfin,
            'empresas' => $empresasUsuario,
            'includeRVC' => $request->input('rvc') == "true" ? 1 : 0
        ]);
    }
    public function getLastYearGuest(Request $request)
    {
        if (empty($request->input('fecha'))) {

            $Year = date("Y");
            $month = date("m");
            $day = (date("d") < 10 ? "0" : "") . (date("d") - 1);
        } else {
            $fecha = explode("-", $request->input('fecha'));
            $Year = $fecha[0];
            $month = $fecha[1];

            if (date("m") > $month && date("Y") <= $Year) {
                $day = date("d", strtotime($Year . "-" . ($month + 1) . "-01 -1 day"));
            } else {
                $day = (date("d") < 10 ? "0" : "") . (date("d") - 1);
            }
        }

        $ventas = array();

        $compania = empty($request->input('compania')) ? 0 : $request->input('compania');

        if (empty($request->input('compania')))
            $whereEmpresas = " idEmpresa > ? ";
        else
            $whereEmpresas = " idEmpresa = ?";

        $sql = "SELECT * FROM empresas WHERE $whereEmpresas AND estado =1;";

        $empresas = DB::select($sql, [$compania]);
        foreach ($empresas as $empresa) {
            $ventas[$empresa->idEmpresa] = null;
            $ventas[$empresa->idEmpresa][0] = $empresa->comun;
            $ventas[$empresa->idEmpresa][1] = null;
            $ventas[$empresa->idEmpresa][2] = null;

            if (empty($request->input('vsa'))) {
                $lastYear = 2019;
            } else {
                $lastYear = $request->input('vsa');
            }

            $dini = $Year . "-" . $month . "-" . "01";
            $dfin =  $Year . "-" . $month . "-" . $day;
            $dlyini = $lastYear . "-" . $month . "-" . "01";
            $dlyfin = $lastYear . "-" . $month . "-" . $day;

            $repDate1 = $dini . " to " . $dfin;
            $repDate2 = $dlyini . " to " . $dlyfin;

            $idEmpresa = $empresa->idEmpresa;

            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_guests.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_guests INNER JOIN sucursales AS s ON s.id = vds_guests.idSucursal WHERE s.idEmpresa = ? AND vds_guests.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) <= ? AND MAX(fecha) < ? UNION ALL SELECT vds_guests.idSucursal, 'Cerradas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_guests INNER JOIN sucursales AS s ON s.id = vds_guests.idSucursal WHERE s.idEmpresa = ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal HAVING SUM(netSales)=0 ) AS venta GROUP BY tipo;";
            $cerradas = DB::select($sql, [$idEmpresa, $dlyfin, $dini, $idEmpresa, $dini, $dfin]);

            $sql = "SELECT tipo , GROUP_CONCAT(idSucursal) sucursales FROM (SELECT vds_guests.idSucursal, 'Abiertas' AS tipo, MAX(fecha) cierre, MIN(fecha) apertura FROM vds_guests  INNER JOIN sucursales AS s ON s.id = vds_guests.idSucursal WHERE s.idEmpresa = ? AND vds_guests.netSales > 0 GROUP BY idSucursal HAVING MIN(fecha) > ? AND MAX(fecha) >= ?) AS venta GROUP BY tipo;";
            $abiertas = DB::select($sql, [$idEmpresa, $dlyfin, $dini]);
            /*       
            var_dump($cerradas);
            echo "<br>";
            var_dump($abiertas);
            dd($abiertas);
*/
            $sucursalesExcluidas = empty($abiertas[0]) ? null : $abiertas[0]->sucursales;
            $sucursalesExcluidas = empty($cerradas[0]) ? $sucursalesExcluidas : ((empty($sucursalesExcluidas) ? "" : $sucursalesExcluidas . (empty($cerradas) ? "" : ",")) . $cerradas[0]->sucursales);

            $sql = "SELECT actual.idSucursal, actual.idEmpresa, actual.sucursal, actual.guests AS Currentguests, anterior.guests AS LYNetguests, actual.avgCheck AS CurrentavgCheck, anterior.avgCheck AS LYNetavgCheck, actual.guests*100/anterior.guests AS LY, actual.avgCheck*100/anterior.avgCheck AS LYAvgCheck, 
            actual.Vitrina, anterior.Vitrina AS VitrinaLY, actual.Salon, anterior.Salon AS SalonLY, actual.Delivery, anterior.Delivery AS DeliveryLY, actual.Institucional, 
            anterior.Institucional AS InstitucionalLY, actual.Vitrina*100/anterior.Vitrina AS VLY, actual.Salon*100/anterior.Salon AS SLY, actual.Delivery*100/anterior.Delivery AS DLY, 
            actual.Institucional*100/anterior.Institucional AS ILY FROM 
            (SELECT s.idEmpresa, s.nombre AS sucursal, vds_guests.idSucursal, MONTH(fecha), SUM(vds_guests.guests) guests, AVG(vds_guests.avgCheck) avgCheck, SUM(IF(vds_guests.rvc IN('Vitrina','RVC 6'),vds_guests.netSales,0)) Vitrina, SUM(IF(vds_guests.rvc IN ('Salon','Restaurant'),vds_guests.netSales,0)) Salon, SUM(IF(vds_guests.rvc='Institucional',vds_guests.netSales,0)) Institucional, SUM(IF(vds_guests.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_guests.netSales,0)) Delivery  FROM vds_guests INNER JOIN sucursales s ON s.id = vds_guests.idSucursal WHERE " . (!empty($sucursalesExcluidas) ? " NOT (s.id IN (" . $sucursalesExcluidas . ")) AND " : "") . " s.idEmpresa= ? AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)) AS actual
            INNER JOIN 
            (SELECT vds_guests.idSucursal,MONTH(fecha), SUM(vds_guests.guests) guests, AVG(vds_guests.avgCheck) avgCheck,SUM(IF(vds_guests.rvc IN('Vitrina','RVC 6'),vds_guests.netSales,0)) Vitrina, SUM(IF(vds_guests.rvc IN ('Salon','Restaurant'),vds_guests.netSales,0)) Salon, SUM(IF(vds_guests.rvc='Institucional',vds_guests.netSales,0)) Institucional, SUM(IF(vds_guests.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio','Serv. Domicilio', 'Delivery'),vds_guests.netSales,0)) Delivery  FROM vds_guests WHERE fecha BETWEEN ? AND ? GROUP BY idSucursal, MONTH(fecha)) AS anterior
            ON actual.idSucursal = anterior.idSucursal ORDER BY actual.guests desc;";

            $venta = DB::select($sql, [$idEmpresa, $dini, $dfin, $dlyini, $dlyfin]);


            /*
            dd($venta);
            echo "<br>";
            var_dump($venta);*/

            $Currentguests = 0;
            $LYNetguests = 0;
            $CurrentavgCheck = 0;
            $LYNetavgCheck = 0;
            $Vitrina  = 0;
            $VitrinaLY = 0;
            $Salon  = 0;
            $SalonLY  = 0;
            $Delivery  = 0;
            $DeliveryLY  = 0;
            $Institucional  = 0;
            $InstitucionalLY  = 0;

            foreach ($venta as $monto) {
                $Currentguests += $monto->Currentguests;
                $LYNetguests += $monto->LYNetguests;
                $CurrentavgCheck += $monto->CurrentavgCheck;
                $LYNetavgCheck += $monto->LYNetavgCheck;
                $Vitrina += $monto->Vitrina;
                $VitrinaLY += $monto->VitrinaLY;
                $Salon += $monto->Salon;
                $SalonLY += $monto->SalonLY;
                $Delivery += $monto->Delivery;
                $DeliveryLY += $monto->DeliveryLY;
                $Institucional += $monto->Institucional;
                $InstitucionalLY += $monto->InstitucionalLY;
            }

            $object = new \stdClass();
            $object->idSucursal = 0;
            $object->idEmpresa = $idEmpresa;
            $object->sucursal = "Total";
            $object->Currentguests = number_format($Currentguests, 0, ".", ",");
            $object->LYNetguests = number_format($LYNetguests, 0, ".", ",");
            $object->CurrentavgCheck = number_format($CurrentavgCheck, 0, ".", ",");
            $object->LYNetavgCheck = number_format($LYNetavgCheck, 0, ".", ",");
            $object->LY =  !empty($LYNetguests) ? number_format(($Currentguests * 100 / $LYNetguests), 0) : 0;
            $object->LYAvgCheck =  !empty($LYNetavgCheck) ? number_format(($CurrentavgCheck * 100 / $LYNetavgCheck), 0) : 0;
            $object->Vitrina = number_format($Vitrina, 0, ".", ",");
            $object->VitrinaLY = number_format($VitrinaLY, 0, ".", ",");
            $object->Salon = number_format($Salon, 0, ".", ",");
            $object->SalonLY = number_format($SalonLY, 0, ".", ",");
            $object->Delivery = number_format($Delivery, 0, ".", ",");
            $object->DeliveryLY = number_format($DeliveryLY, 0, ".", ",");
            $object->Institucional = number_format($Institucional, 0, ".", ",");
            $object->InstitucionalLY = number_format($InstitucionalLY, 0, ".", ",");
            $object->VLY = !empty($VitrinaLY) ? number_format(($Vitrina * 100 / $VitrinaLY), 0) : 0;
            $object->SLY = !empty($SalonLY) ? number_format(($Salon * 100 / $SalonLY), 0) : 0;
            $object->DLY = !empty($DeliveryLY) ? number_format(($Delivery * 100 / $DeliveryLY), 0) : 0;
            $object->ILY = !empty($InstitucionalLY) ? number_format(($Institucional * 100 / $InstitucionalLY), 0) : 0;

            $venta = $this->parserGuest($venta);
            $venta[] = $object;
            $ventas[$idEmpresa][1] = $venta;
            $vAbiertas = array();
            $vCerradas = array();
            if (!empty($abiertas[0])) {
                $sql = "SELECT s.idEmpresa, s.nombre AS sucursal, vds_guests.idSucursal, MONTH(fecha), SUM(vds_guests.guests) Currentguests, 0 AS 'LYNetguests', AVG(avgCheck) AS CurrentavgCheck, 0 AS LYNetavgCheck , 0 AS LY, 0 AS LYAvgCheck , SUM(IF(vds_guests.rvc IN('Vitrina','RVC 6'),vds_guests.netSales,0)) Vitrina, SUM(IF(vds_guests.rvc IN ('Salon','Restaurant'),vds_guests.netSales,0)) Salon, SUM(IF(vds_guests.rvc='Institucional',vds_guests.netSales,0)) Institucional, SUM(IF(vds_guests.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_guests.netSales,0)) Delivery  FROM vds_guests INNER JOIN sucursales s ON s.id = vds_guests.idSucursal WHERE s.idEmpresa= ? AND s.id IN (" . $abiertas[0]->sucursales . ") AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)";
                $vAbiertas = DB::select($sql, [$idEmpresa, $dini, $dfin]);
            }

            if (!empty($cerradas[0])) {
                $sql = "SELECT s.idEmpresa, s.nombre AS sucursal, vds_guests.idSucursal, MONTH(fecha),0 Currentguests, SUM(vds_guests.guests) AS 'LYNetguests', 0 AS CurrentavgCheck,  AVG(avgCheck) AS LYNetavgCheck, 0 AS LY, 0 AS LYAvgCheck, SUM(IF(vds_guests.rvc IN('Vitrina','RVC 6'),vds_guests.netSales,0)) Vitrina, SUM(IF(vds_guests.rvc IN ('Salon','Restaurant'),vds_guests.netSales,0)) Salon, SUM(IF(vds_guests.rvc='Institucional',vds_guests.netSales,0)) Institucional, SUM(IF(vds_guests.rvc IN ('Servicio Domicilio', 'Servicio a Domicilio', 'Serv. Domicilio', 'Delivery'),vds_guests.netSales,0)) Delivery  FROM vds_guests INNER JOIN sucursales s ON s.id = vds_guests.idSucursal WHERE s.idEmpresa= ? AND s.id IN (" . $cerradas[0]->sucursales . ") AND fecha BETWEEN ? AND ? GROUP BY idSucursal,s.idEmpresa, s.nombre, MONTH(fecha)";
                $vCerradas = DB::select($sql, [$idEmpresa, $dlyini, $dlyfin]);
            }

            $ventas[$empresa->idEmpresa][2] = array();
            $montoCerradas = 0;
            $montoAperturas = 0;

            foreach ($vCerradas as $v) {
                $montoCerradas += $v->LYNetguests;
            }

            foreach ($vAbiertas as $v) {
                $montoAperturas += $v->Currentguests;
            }

            if (count($vAbiertas) > 0 || count($vCerradas) > 0) {
                $totales = array();
                $totalAperturas = new \stdClass();
                $totalAperturas->idEmpresa = $idEmpresa;
                $totalAperturas->sucursal = "Total";
                $totalAperturas->idSucursal = 0;
                $totalAperturas->Currentguests = number_format($montoAperturas, 0, ".", ",");
                $totalAperturas->LYNetguests = number_format($montoCerradas, 0, ".", ",");
                $totalAperturas->CurrentavgCheck = 0;
                $totalAperturas->LYNetavgCheck = 0;
                $totalAperturas->LY =  !empty($montoCerradas) ? number_format(($montoAperturas * 100 / $montoCerradas), 0) : 0;
                $totales[] = $totalAperturas;

                $totalAperturas = new \stdClass();
                $totalAperturas->idEmpresa = $idEmpresa;
                $totalAperturas->sucursal = "Total";
                $totalAperturas->idSucursal = 0;
                $totalAperturas->Currentguests = number_format($Currentguests + $montoAperturas, 0, ".", ",");
                $totalAperturas->LYNetguests = number_format($LYNetguests + $montoCerradas, 0, ".", ",");
                $totalAperturas->CurrentavgCheck = 0;
                $totalAperturas->LYNetavgCheck = 0;
                $totalAperturas->LY =  !empty(($LYNetguests + $montoCerradas)) ? number_format((($Currentguests + $montoAperturas) * 100 / ($LYNetguests + $montoCerradas)), 0) : 0;
                $totales[] = $totalAperturas;

                $vAbiertas = $this->parserGuest($vAbiertas, 2);
                $ventas[$empresa->idEmpresa][2] = array_merge($vAbiertas, $vCerradas, $totales);
            }
        }
        $menu = $this->menuReports();

        //dd($ventas);
        return view('ventas.lastyearguest', [
            "ventas" => $ventas,
            "fechaActual" => $repDate1,
            "fechaVS" => $repDate2,
            'menu' => $menu
        ]);
    }

    public function parserGuest($venta, $tipo = 1)
    {
        $lon = count($venta);
        $idEmpresa = 0;

        for ($i = 0; $i < $lon; $i++) {

            if ($tipo == 1) {
                $venta[$i]->LY = number_format($venta[$i]->LY, 0);
                $venta[$i]->VLY = number_format($venta[$i]->VLY, 0);
                $venta[$i]->SLY = number_format($venta[$i]->SLY, 0);
                $venta[$i]->DLY = number_format($venta[$i]->DLY, 0);
                $venta[$i]->ILY = number_format($venta[$i]->ILY, 0);

                $venta[$i]->Vitrina = number_format($venta[$i]->Vitrina, 0, ".", ",");
                $venta[$i]->VitrinaLY = number_format($venta[$i]->VitrinaLY, 0, ".", ",");
                $venta[$i]->Salon = number_format($venta[$i]->Salon, 0, ".", ",");
                $venta[$i]->SalonLY = number_format($venta[$i]->SalonLY, 0, ".", ",");
                $venta[$i]->Delivery = number_format($venta[$i]->Delivery, 0, ".", ",");
                $venta[$i]->DeliveryLY = number_format($venta[$i]->DeliveryLY, 0, ".", ",");
                $venta[$i]->Institucional = number_format($venta[$i]->Institucional, 0, ".", ",");
                $venta[$i]->InstitucionalLY = number_format($venta[$i]->InstitucionalLY, 0, ".", ",");
            }

            $venta[$i]->Currentguests = number_format($venta[$i]->Currentguests, 0, ".", ",");
            $venta[$i]->LYNetguests = number_format($venta[$i]->LYNetguests, 0, ".", ",");
            $venta[$i]->CurrentavgCheck = number_format($venta[$i]->CurrentavgCheck, 0, ".", ",");
            $venta[$i]->LYNetavgCheck = number_format($venta[$i]->LYNetavgCheck, 0, ".", ",");

            $venta[$i]->LY = number_format($venta[$i]->LY, 0, ".", ",");
            $venta[$i]->LYAvgCheck =  number_format($venta[$i]->LYAvgCheck, 0, ".", ",");
        }

        return $venta;
    }

    public function parser($venta, $tipo = 1)
    {
        $lon = count($venta);
        $idEmpresa = 0;

        for ($i = 0; $i < $lon; $i++) {

            if ($tipo == 1) {
                $venta[$i]->LY = number_format($venta[$i]->LY, 0);
                $venta[$i]->VLY = number_format($venta[$i]->VLY, 0);
                $venta[$i]->SLY = number_format($venta[$i]->SLY, 0);
                $venta[$i]->DLY = number_format($venta[$i]->DLY, 0);
                $venta[$i]->ILY = number_format($venta[$i]->ILY, 0);
                $venta[$i]->Budget = number_format($venta[$i]->Budget, 0);

                $venta[$i]->Vitrina = number_format($venta[$i]->Vitrina, 0, ".", ",");
                $venta[$i]->VitrinaLY = number_format($venta[$i]->VitrinaLY, 0, ".", ",");
                $venta[$i]->Salon = number_format($venta[$i]->Salon, 0, ".", ",");
                $venta[$i]->SalonLY = number_format($venta[$i]->SalonLY, 0, ".", ",");
                $venta[$i]->Delivery = number_format($venta[$i]->Delivery, 0, ".", ",");
                $venta[$i]->DeliveryLY = number_format($venta[$i]->DeliveryLY, 0, ".", ",");
                $venta[$i]->Institucional = number_format($venta[$i]->Institucional, 0, ".", ",");
                $venta[$i]->InstitucionalLY = number_format($venta[$i]->InstitucionalLY, 0, ".", ",");
            }

            $venta[$i]->CurrentNetSales = number_format($venta[$i]->CurrentNetSales, 0, ".", ",");
            $venta[$i]->LYNetSales = number_format($venta[$i]->LYNetSales, 0, ".", ",");
        }

        return $venta;
    }

    public function dashboard()
    {
        return view('ventas.dashboard');
    }


    public function setIscam(Request $request)
    {
        //Obtenemos la fecha de la url
        $fecha = !empty($request->input('fechaIni')) ? $request->input('fechaIni') : date("Y-m");
        $valores = explode('-', $fecha); //separamos la fecha           
        $yearGet = $valores[0]; //anio get
        $mesGet = intval($valores[1]); //mes get

        $year = date("Y"); //Variable que almacena el año
        $mes = date("m"); //Variable que almacena el mes
        $dia = date("j"); //Variable que amacena el dia
        $fechaNom = date('ymdhis'); //variable para fecha y hora

        if (!empty($fecha)) {
            echo "Proceso con fecha<br>";
            //Consulta para obtener los datos para el reporte 
            $sql = " SELECT  s.nombre,'$mesGet-$yearGet'AS fecha ,v.idArticulo ,m.itemName, d.daypart, SUM(v.netSales ) as netSale, SUM(v.quantity) as quantity from vds_producto_daypart AS v
            INNER JOIN sucursales AS s ON v.idSucursal=s.id
            INNER JOIN daypart AS d ON v.idDayPart = d.idDayPart
            INNER JOIN micros_producto AS m ON v.idArticulo=m.idItemMicros WHERE MONTH (v.fecha) = '$mesGet' AND YEAR(v.fecha) = '$yearGet'
                AND v.netSales >0 and s.idEmpresa = 1 GROUP BY s.nombre,v.idArticulo,v.idSucursal,m.itemName,v.idDayPart,d.daypart ";
            echo $sql;
            $registros = DB::select($sql); //Guardamos los datos arrojados por el query en la variable 
            $delimiter = '|'; //Delimitador para separar los datos 
            $filename = 'ISCAM-' . $mesGet . '-' . $yearGet . '_' . $fechaNom . '.csv'; //Nombre para el archivo csv
            $fileStr = "";
            foreach ($registros as $line) { //Ciclo para recorrer el arreglo de los datos del query
                $fileStr .= $line->nombre . "|" . $line->fecha . "|" . $line->idArticulo . "|" . $line->itemName . "|" . $line->daypart . "|" . $line->netSale . "|" . $line->quantity . "\n"; //Creacion del archivo csv
            }
            echo $fileStr;
            Storage::disk('ftp')->prepend($filename, $fileStr);
        } else {

            if ($dia == 2) { //Condicion para solo ejecutar los dias 2
                echo "Proceso sin fecha<br>";
                $mes = $mes - 1; //Retrocedemos el mes
                if ($mes == 0) { //Condicion para el mes de enero
                    $mes = 12; //asignacion del mes de diciembre
                    $year = $year - 1; //Retrocedemos el anio
                }

                //Consulta para obtener los datos para el reporte 
                $sql = " SELECT  s.nombre,'$mes-$year'AS fecha ,v.idArticulo ,m.itemName, d.daypart, SUM(v.netSales ) as netSale, SUM(v.quantity) as quantity from vds_producto_daypart AS v
                INNER JOIN sucursales AS s ON v.idSucursal=s.id
                INNER JOIN daypart AS d ON v.idDayPart = d.idDayPart
                INNER JOIN micros_producto AS m ON v.idArticulo=m.idItemMicros WHERE MONTH (v.fecha) = '$mes' AND YEAR(v.fecha) = '$year'
                    AND v.netSales >0 and s.idEmpresa = 1 GROUP BY s.nombre,v.idArticulo,v.idSucursal,m.itemName,v.idDayPart,d.daypart ";

                $registros = DB::select($sql); //Guardamos los datos arrojados por el query en la variable 
                $delimiter = '|'; //Delimitador para separar los datos 
                $filename = 'ISCAM-' . $mes . '-' . $year . '_' . $fechaNom . '.csv'; //Nombre para el archivo csv
                $fileStr = "";
                foreach ($registros as $line) { //Ciclo para recorrer el arreglo de los datos del query
                    $fileStr .= $line->nombre . "|" . $line->fecha . "|" . $line->idArticulo . "|" . $line->itemName . "|" . $line->daypart . "|" . $line->netSale . "|" . $line->quantity . "\n"; //Creacion del archivo csv
                }
                echo $fileStr;
                Storage::disk('ftp')->prepend($filename, $fileStr);
            }
        }
    }

    public function sendXlsCash($date)
    {
        $url = "https://intranet.prigo.com.mx/reports/get/cash/2021-12-31";
        Mail::send('sales.mailReport', ['url' => $url, 'comentario' => ''], function ($message) {
            $message->from('reportes@prigo.com.mx', 'Reportes PRIGO');
            $message->to('ggr@coscomatehospitality.com');
            #$message->to('rgallardo@prigo.com.mx');
            $message->subject("Sale Tax Tip Report 2021-12-16 to 2021-12-31");
        });
    }

    public function getXlsCash($date)
    {

        $sql = "SELECT tender, netSales, fecha FROM vds_tender WHERE tender = 'cash' AND idSucursal= 52 AND fecha BETWEEN '2021-12-16' AND '2021-12-31' ORDER BY fecha;";

        $cashItems = DB::select($sql);

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];


        $spreadsheet = new Spreadsheet();

        $sugestedItems = $spreadsheet->getActiveSheet();
        $sugestedItems->setTitle('Tender');
        $sugestedItems->setCellValue('A1', 'Tender');
        $sugestedItems->setCellValue('B1', 'Tender Total');
        $sugestedItems->setCellValue('C1', 'Date');

        $sugestedItems->getColumnDimension('A')->setWidth(20);
        $sugestedItems->getColumnDimension('B')->setWidth(30);
        $sugestedItems->getColumnDimension('C')->setWidth(20);

        $row = 1;
        foreach ($cashItems as $item) {
            if ($row == 2)
                $sugestedItems->setCellValue('A' . $row, $item->tender);
            $row++;
            $sugestedItems->setCellValue('B' . $row, $item->netSales);
            $sugestedItems->setCellValue('C' . $row, $item->fecha);
        }

        $sugestedItems->getStyle('A1:C' . $row)->applyFromArray($styleArray);
        $sugestedItems->mergeCells('A2:A' . $row);
        $sugestedItems->getStyle('A2:A' . $row)
            ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $spreadsheet->setActiveSheetIndex(0);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="cash_report.xlsx"');
        $writer->save("php://output");
    }

    // public function getXlsSlTxTp($date)
    // {

    //     $sql = "SELECT taxCollected, totalDiscount, serviceCharges, amountChecks, netSales, fecha FROM venta_diaria_sucursal WHERE idSucursal= 52 AND fecha BETWEEN '2021-12-01' AND '2021-12-31' ORDER BY fecha;";

    //     $cashItems = DB::select($sql);

    //     $styleArray = [
    //         'borders' => [
    //             'allBorders' => [
    //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //             ],
    //         ]
    //     ];


    //     $spreadsheet = new Spreadsheet();

    //     $sugestedItems = $spreadsheet->getActiveSheet();
    //     $sugestedItems->setTitle('Report');
    //     $sugestedItems->setCellValue('A1', 'Business Date');
    //     $sugestedItems->setCellValue('B1', 'Check Total');
    //     $sugestedItems->setCellValue('C1', 'Service Charge Total');
    //     $sugestedItems->setCellValue('D1', 'Tax Total');
    //     $sugestedItems->setCellValue('E1', 'Sub Total');
    //     $sugestedItems->setCellValue('F1', 'Discount Total');

    //     $sugestedItems->getColumnDimension('A')->setWidth(20);
    //     $sugestedItems->getColumnDimension('B')->setWidth(20);
    //     $sugestedItems->getColumnDimension('C')->setWidth(30);
    //     $sugestedItems->getColumnDimension('D')->setWidth(20);
    //     $sugestedItems->getColumnDimension('E')->setWidth(20);
    //     $sugestedItems->getColumnDimension('F')->setWidth(20);

    //     $row = 1;
    //     foreach ($cashItems as $item) {
    //         $row++;
    //         $sugestedItems->setCellValue('A' . $row, $item->fecha);
    //         $sugestedItems->setCellValue('B' . $row, $item->amountChecks + $item->taxCollected);
    //         $sugestedItems->setCellValue('C' . $row, $item->serviceCharges);
    //         $sugestedItems->setCellValue('D' . $row, $item->taxCollected);
    //         $sugestedItems->setCellValue('E' . $row, $item->netSales);
    //         $sugestedItems->setCellValue('F' . $row, $item->totalDiscount);
    //     }

    //     $sugestedItems->getStyle('A1:F' . $row)->applyFromArray($styleArray);
    //     $spreadsheet->setActiveSheetIndex(0);

    //     $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header('Content-Disposition: attachment; filename="tax_tip_sale_report.xlsx"');
    //     $writer->save("php://output");
    // }

    public function menuReports()
    {
        $sql = "SELECT * FROM config_reportes_categoria";
        $cat = DB::select($sql, []);
        $sql = "SELECT cru.idReporte, cr.nombre, cr.ruta, cr.idCategoria FROM config_reportes_usuario cru INNER JOIN config_reportes cr ON cr.idReporte = cru.idReporte WHERE cru.idUsuario = ?";
        $items = DB::select($sql, [1]);

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

    // public function sendEKEItemReport()
    // {
    //     $hora = date("H");

        

    //     $spreadsheet = new Spreadsheet();
    //     $sugestedItems = $spreadsheet->getActiveSheet();
    //     $path = "";

    //     if ($hora < 15) {

    //         $sql = "SELECT 'Sin Grupo' as nombre ,0 AS grupo, idItem AS item FROM producto_seguimiento_item WHERE idUsuario = ? UNION SELECT C.grupo as nombre, A.idGrupo AS grupo, B.idProducto AS item FROM producto_seguimiento_grupo A INNER JOIN producto_agrupador B ON A.idGrupo = B.idGrupo INNER JOIN producto_grupo C ON B.idGrupo = C.idGrupo WHERE idUsuario = ?";
    //         $items = DB::select($sql, [1, 1]);
    //         $sql = '';
    //         $ventasPorSucursal = [];

    //         foreach ($items as $key => $value) {
    //             if (!empty($sql))
    //                 $sql .= ",";
    //             $sql .= "'$value->item'";

    //             $ventasPorSucursal[$value->item] = [];
    //         }


    //         $sql = "SELECT fecha ,idSucMicros, idMicros, idItemMicros, SUM(cantidad) AS cantidad, SUM(ventaNeta) AS ventaNeta FROM venta_mes_producto_micros Z WHERE Z.idMicros IN ($sql) AND Z.idSucMicros LIKE 'EKE%' AND Z.fecha = DATE(NOW()) GROUP BY fecha , idSucMicros, idMicros, idItemMicros;";
    //         $venta = DB::select($sql);

    //         $sucursales = ['Menu Item'];
    //         $formats = [];

    //         $tables = [];

    //         foreach ($venta as $key => $value) {
    //             if (!in_array($value->idSucMicros, $sucursales)) {
    //                 $sucursales[] = $value->idSucMicros;
    //             }
    //             if (!in_array($value->idItemMicros, $ventasPorSucursal[$value->idMicros])) {
    //                 $ventasPorSucursal[$value->idMicros][] = $value->idItemMicros;
    //             }
    //             $ventasPorSucursal[$value->idMicros][] = $value->cantidad;
    //         }
    //         $grupoAnt = 0;
    //         $index = 0;
    //         $dataTemp = [];
    //         $sucursales[] = 'Total';
    //         $formats = array_fill(0, count($sucursales) - 1, 'N');
    //         array_unshift($formats, 'T');
    //         foreach ($items as $key => $value) {
    //             if ($value->grupo != $grupoAnt) {
    //                 $grupoAnt = $value->grupo;
    //                 $totales = [];
    //                 $totales = array_fill(0, count($sucursales), 0);
    //                 $totales[0] = 'Total';
    //                 $totalesItem = 0;
    //                 foreach ($dataTemp as $keyTemp => $item) {
    //                     $totalesItem = array_sum(array_filter($item, 'is_numeric'));
    //                     $dataTemp[$keyTemp][] = $totalesItem;
    //                     foreach ($sucursales as $keySuc => $suc) {
    //                         if (array_key_exists($keyTemp, $dataTemp) && is_numeric($dataTemp[$keyTemp][$keySuc])) {
    //                             $totales[$keySuc] += $dataTemp[$keyTemp][$keySuc];
    //                         }
    //                     }
    //                 }
    //                 array_push($dataTemp, $totales);
    //                 $tables[$index]['grupo'] = $items[$key - 1]->nombre;
    //                 $tables[$index]['headers'] = $sucursales;
    //                 $tables[$index]['data'] = $dataTemp;
    //                 $tables[$index]['formats'] = $formats;
    //                 $dataTemp = [];
    //                 $index++;
    //             }

    //             $dataTemp[] = $ventasPorSucursal[$value->item];

    //             if (count($items) - 1 == $key) {
    //                 $totales = [];
    //                 $totales = array_fill(0, count($sucursales), 0);
    //                 $totales[0] = 'Total';
    //                 foreach ($dataTemp as $keyTemp => $item) {
    //                     $totalesItem = array_sum(array_filter($item, 'is_numeric'));
    //                     $dataTemp[$keyTemp][] = $totalesItem;
    //                     foreach ($sucursales as $keySuc => $suc) {
    //                         if (array_key_exists($keySuc, $dataTemp[$keyTemp])) {
    //                             if (is_numeric($dataTemp[$keyTemp][$keySuc])) {
    //                                 $totales[$keySuc] += $dataTemp[$keyTemp][$keySuc];
    //                             }
    //                         } else {
    //                             $totalArr = $dataTemp[$keyTemp][$keySuc - 1];
    //                             $dataTemp[$keyTemp][$keySuc - 1] = 0;
    //                             $dataTemp[$keyTemp][] = $totalArr;
    //                         }
    //                     }
    //                 }
    //                 array_push($dataTemp, $totales);
    //                 $tables[$index]['grupo'] = $value->nombre;
    //                 $tables[$index]['headers'] = $sucursales;
    //                 $tables[$index]['data'] = $dataTemp;
    //                 $tables[$index]['formats'] = $formats;
    //             }
    //         }

    //         $sugestedItems->getColumnDimension('A')->setWidth(25);
    //         $sugestedItems->getColumnDimension('B')->setWidth(14);
    //         $sugestedItems->getColumnDimension('C')->setWidth(14);
    //         $sugestedItems->getColumnDimension('D')->setWidth(14);
    //         $sugestedItems->getColumnDimension('E')->setWidth(14);
    //         $sugestedItems->getColumnDimension('F')->setWidth(14);
    //         $sugestedItems->getColumnDimension('G')->setWidth(14);
    //         $sugestedItems->getColumnDimension('H')->setWidth(10);

    //         $sugestedItems->setTitle('Report');
    //         $sugestedItems = $spreadsheet->getActiveSheet();
    //         $sugestedItems->setTitle('Report');

    //         $sugestedItems->setCellValue('A1', 'Reporte');
    //         $sugestedItems->setCellValue('A2', 'Hora');
    //         $sugestedItems->setCellValue('B2', date("H:i", strtotime(date("Y-m-d H:i:s") . " +8 HOURS")));

    //         $row = 5;
    //         foreach ($tables as $key => $table) {
    //             $col = 1;
    //             $sucursales = $table['headers'];
    //             $dataFina = $table['data'];
    //             $grupo = $table['grupo'];

    //             $columnLetter = Coordinate::stringFromColumnIndex(count($sucursales) + 1);
    //             $sugestedItems->mergeCells("A$row:$columnLetter" . "$row");
    //             $style = $sugestedItems->getStyle("A$row");
    //             $alignment = $style->getAlignment();
    //             $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    //             $sugestedItems->setCellValue("A$row", $grupo);
    //             $row++;

    //             foreach ($sucursales as $suc) {
    //                 $sugestedItems->setCellValueByColumnAndRow($col, $row, $suc);
    //                 $col++;
    //             }
    //             $row++;
    //             foreach ($dataFina as  $iditem => $Items) {
    //                 $col = 1;
    //                 foreach ($Items as $item) {
    //                     $sugestedItems->setCellValueByColumnAndRow($col, $row, $item);
    //                     $col++;
    //                 }
    //                 $row++;
    //             }
    //             $row++;
    //         }

    //         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    //         $path = storage_path('app/public/') . "menuitem_report_" . date("YmdHi") . ".xlsx";
    //         $writer->save($path);
    //     } else {
    //         $sql = "SELECT fecha,
    //         SUM(IF(tipo='venta', cantidad,0)) venta,
    //         SUM(IF(tipo='merma mp', cantidad,0)) 'mermamp',
    //         SUM(IF(tipo='merma pt', cantidad,0)) 'mermapt'
    //         FROM 
    //         (
    //         SELECT fecha, 'venta' AS tipo, SUM(cantidad) AS cantidad FROM venta_mes_producto_micros Z WHERE Z.idMicros IN (90210044,90210040,90210045,90210041,90210046) AND Z.idSucMicros LIKE 'EKE%' AND Z.fecha >= '2023-02-01' GROUP BY fecha
    //         UNION all
    //         SELECT IM.fecha, 'merma mp' AS tipo, SUM(M.cantidad) cantidad FROM inventario_merma IM INNER JOIN inventario_merma_partida M ON IM.idMerma = M.idMerma INNER JOIN pedidos_articulo C ON C.idArticulo = M.idArticulo WHERE IM.idSucursal IN (56,57,58,61,69,70) AND (YEAR(IM.fecha) = 2023  AND MONTH(IM.fecha) IN (2,3)) AND M.tipo = 'MP' AND C.Descripcion LIKE '%Croissant Roll%' GROUP BY IM.fecha
    //         UNION all
    //         SELECT 
    //         A.fecha, 'merma pt' AS tipo ,SUM(B.cantidad) cantidad
    //         FROM inventario_merma A INNER JOIN inventario_merma_partida B ON A.idMerma = B.idMerma 
    //         INNER JOIN recetas_platillo D ON (D.idPlatillo = B.idArticulo)
    //         WHERE D.nombre LIKE 'CROISSANT ROLL%' AND
    //         B.tipo = 'PT' AND A.idSucursal IN (56,57,58,61,69,70) AND ((YEAR(A.fecha) = 2023  AND MONTH(A.fecha)>=2))
    //         GROUP BY A.fecha
    //         ) datos GROUP BY fecha;";
    //         $venta = DB::select($sql, []);

    //         $sugestedItems->setTitle('Resumen');
    //         $sugestedItems = $spreadsheet->getActiveSheet();
    //         $sugestedItems->setTitle('Resumen');
    //         $sugestedItems->setCellValue('A1', 'Fecha');
    //         $sugestedItems->setCellValue('B1', 'Cantidad');
    //         $sugestedItems->setCellValue('C1', 'Merma MP');
    //         $sugestedItems->setCellValue('D1', 'Merma PT');

    //         $row = 2;
    //         foreach ($venta as $resumen) {
    //             $sugestedItems->setCellValue('A' . $row, $resumen->fecha);
    //             $sugestedItems->setCellValue('B' . $row, $resumen->venta);
    //             $sugestedItems->setCellValue('C' . $row, $resumen->mermamp);
    //             $sugestedItems->setCellValue('D' . $row, $resumen->mermapt);
    //             $row++;
    //         }

    //         $spreadsheet->createSheet();
    //         $sugestedItems = $spreadsheet->getSheet(1);
    //         $sugestedItems->setTitle('Venta');

    //         $sql = "SELECT fecha, idSucMicros, idMicros, idItemMicros, SUM(cantidad) AS cantidad, SUM(ventaNeta) AS ventaNeta FROM venta_mes_producto_micros Z WHERE Z.idMicros IN (90210044,90210040,90210045,90210041,90210046,90210047) AND Z.idSucMicros LIKE 'EKE%' AND Z.fecha >= '2023-02-01' GROUP BY fecha , idSucMicros, idMicros, idItemMicros;";
    //         $venta = DB::select($sql, []);

    //         $sugestedItems->setCellValue('A1', 'Fecha');
    //         $sugestedItems->setCellValue('B1', 'Sucursal');
    //         $sugestedItems->setCellValue('C1', 'idMicros');
    //         $sugestedItems->setCellValue('D1', 'Menu Item');
    //         $sugestedItems->setCellValue('E1', 'Cantidad');
    //         $sugestedItems->setCellValue('F1', 'Venta');

    //         $row = 2;
    //         foreach ($venta as $data) {
    //             $sugestedItems->setCellValue('A' . $row, $data->fecha);
    //             $sugestedItems->setCellValue('B' . $row, $data->idSucMicros);
    //             $sugestedItems->setCellValue('C' . $row, $data->idMicros);
    //             $sugestedItems->setCellValue('D' . $row, $data->idItemMicros);
    //             $sugestedItems->setCellValue('E' . $row, $data->cantidad);
    //             $sugestedItems->setCellValue('F' . $row, $data->ventaNeta);
    //             $row++;
    //         }


    //         $spreadsheet->createSheet();
    //         $sugestedItems = $spreadsheet->getSheet(2);
    //         $sugestedItems->setTitle('Merma MP');

    //         $sql = "SELECT fecha,S.nombre Sucursal, M2.CodPrigo, M2.UnidadPrg, M2.Descripcion, M2.cantidad FROM (SELECT IM.fecha,IM.idSucursal,C.CodPrigo, C.UnidadPrg, C.Descripcion, SUM(M.cantidad) cantidad FROM inventario_merma IM INNER JOIN inventario_merma_partida M ON IM.idMerma = M.idMerma INNER JOIN pedidos_articulo C ON C.idArticulo = M.idArticulo WHERE IM.idSucursal IN (56,57,58,61,69,70)  AND (YEAR(IM.fecha) = 2023  AND MONTH(IM.fecha) IN (2,3)) AND M.tipo = 'MP' AND C.Descripcion LIKE '%Croissant Roll%' GROUP BY IM.fecha,IM.idSucursal,C.CodPrigo, C.UnidadPrg, C.Descripcion ) M2 INNER JOIN sucursales S ON M2.idSucursal = S.id ORDER BY fecha, Sucursal DESC;";
    //         $venta = DB::select($sql, []);

    //         $sugestedItems->setCellValue('A1', 'Fecha');
    //         $sugestedItems->setCellValue('B1', 'Sucursal');
    //         $sugestedItems->setCellValue('C1', 'SAP');
    //         $sugestedItems->setCellValue('D1', 'Unidad');
    //         $sugestedItems->setCellValue('E1', 'Descripcion');
    //         $sugestedItems->setCellValue('F1', 'Cantidad');

    //         $row = 2;
    //         foreach ($venta as $data) {
    //             $sugestedItems->setCellValue('A' . $row, $data->fecha);
    //             $sugestedItems->setCellValue('B' . $row, $data->Sucursal);
    //             $sugestedItems->setCellValue('C' . $row, $data->CodPrigo);
    //             $sugestedItems->setCellValue('D' . $row, $data->UnidadPrg);
    //             $sugestedItems->setCellValue('E' . $row, $data->Descripcion);
    //             $sugestedItems->setCellValue('F' . $row, $data->cantidad);
    //             $row++;
    //         }

    //         $spreadsheet->createSheet();
    //         $sugestedItems = $spreadsheet->getSheet(3);
    //         $sugestedItems->setTitle('Merma PT');

    //         $sql = "SELECT S.nombre sucursal,MERMA.* FROM 
    //         (SELECT 
    //         A.fecha, A.idSucursal, B.tipo, B.idArticulo, D.idMicros codigo,D.nombre Descripcion,SUM(B.cantidad) cantidad
    //         FROM inventario_merma A INNER JOIN inventario_merma_partida B ON A.idMerma = B.idMerma 
    //         INNER JOIN recetas_platillo D ON (D.idPlatillo = B.idArticulo)
    //         WHERE D.nombre LIKE 'CROISSANT ROLL%' AND
    //         B.tipo = 'PT' AND A.idSucursal IN (56,57,58,61,69,70) AND ((YEAR(A.fecha) = 2023  AND MONTH(A.fecha) >= 2))
    //         GROUP BY A.fecha,A.idSucursal, B.tipo, B.idArticulo, D.idMicros,D.nombre
    //         ) MERMA INNER JOIN sucursales S ON S.id = MERMA.idSucursal LEFT JOIN micros_producto_clasificacion MPC ON (MPC.idMicros = MERMA.codigo AND MPC.idEmpresa = 4) ORDER BY  MERMA.fecha,MERMA.idSucursal DESC;";

    //         $venta = DB::select($sql, []);

    //         $sugestedItems->setCellValue('A1', 'Fecha');
    //         $sugestedItems->setCellValue('B1', 'Sucursal');
    //         $sugestedItems->setCellValue('C1', 'idMicros');
    //         $sugestedItems->setCellValue('D1', 'Menu Item');
    //         $sugestedItems->setCellValue('E1', 'Cantidad');


    //         $row = 2;
    //         foreach ($venta as $data) {
    //             $sugestedItems->setCellValue('A' . $row, $data->fecha);
    //             $sugestedItems->setCellValue('B' . $row, $data->sucursal);
    //             $sugestedItems->setCellValue('C' . $row, $data->codigo);
    //             $sugestedItems->setCellValue('D' . $row, $data->Descripcion);
    //             $sugestedItems->setCellValue('E' . $row, $data->cantidad);
    //             $row++;
    //         }

    //         $spreadsheet->setActiveSheetIndex(0);

    //         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    //         $path = storage_path('app/public/') . "menuitem_endofday_" . date("YmdHi") . ".xlsx";
    //         $writer->save($path);
    //     }

    //     Mail::send('incidencias.mail.mailMensualIncidencias', [], function ($message) use ($path) {
    //         $message->from('reportes@prigo.com.mx', 'Reporte venta Croissant Roll');
    //         $message->to(['ab@maison-kayser.es', 'lm@maison-kayser.es', 'sg@maison-kayser.es', 'rgallardo@maison-kayser.com.mx', 'arata@maison-kayser.com.mx']);
    //         #$message->to(['rgallardo@maison-kayser.com.mx']);
    //         $message->subject("Reporte venta Croissant Roll");
    //         $message->attach($path);
    //     });

    //     Storage::delete($path);
    // }

    public function getSucursales(Request $request)
    {
        $company = !empty($request->input('company')) ? $request->input('company') : 0;
        $typeLoc = $request->input('type');
        $location = new UserLocation();
        $location->get($company, $typeLoc);
        $sucsNombres = $location->locationNombres;
        $sucsSap = explode(',', $location->locationSap);
        $sucs = [];
        foreach ($sucsNombres as $key => $value) {
            $sucs[] = [
                'idSucursal' => $sucsSap[$key],
                'nombre' => $value,
            ];
        }
        $data = [
            'sucursales' => $sucs
        ];

        return response()->json($data, 200);
    }
}
