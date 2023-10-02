<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\Budget\Budget;

use App\Classes\Reports\Report;
use App\Classes\Reports\utils\UserLocation;

class ReportsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth', ["except" => ['CheckListReport', 'budgetRedistribute', 'getWidgetListMovil', 'getWidgetReportDataMovil', 'GetReportData']]);

        // $this->middleware(function ($request, $next) {
        //     $idUsuario = Auth::id();
        //     $user = Auth::user();
        //     $sql = "SELECT * FROM config_app_access WHERE idUsuario = ? AND idAplicacion = 23;";
        //     $accesQuery = DB::select($sql, [$idUsuario]);
        //     if (!empty($accesQuery)) {
        //         session(['RepRole' => $accesQuery[0]->idRole]);
        //         if ($accesQuery[0]->idRole != 1) {
        //             $sql = "SELECT group_concat(`idSucursal` separator ',') as `sucursales` FROM pedidos_sucursal_usuario WHERE idUsuario = ? GROUP BY idUsuario;";
        //             $sucursales = DB::select($sql, [$idUsuario]);
        //             if (!empty($sucursales)) {
        //                 session(['sucursales' => $sucursales[0]->sucursales]);
        //             }
        //         }
        //     }
        //     return $next($request);
        // });
    }

    public function index()
    {
        //$menu = $this->menuReports();
        //return view('reports.index', ['menu' => $menu]);
        return view('reports.index');
    }

    public function indexVue()
    {
        $menu = $this->menuReports();
        return view('reports.indexVue', ['menu' => $menu]);
        //return view('reports.indexVue');
    }

    // public function MenuEngineeringReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     return view('reports.MenuEngineering', ["hierachy" => $hierachy, 'menu' => $menu]);
    // }

    // public function ProductMixReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $families = $this->getFamilies();
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.ProductMix', ["hierachy" => $hierachy, "families" => $families, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function PMixTBReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $families = $this->getFamilies();
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.PMixTB', ["hierachy" => $hierachy, "families" => $families, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function DayPartReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.DayPart', ["hierachy" => $hierachy, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function VITReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.VIT', ["hierachy" => $hierachy, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function VSProdReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.VSProd', ["hierachy" => $hierachy, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function DiscountsReport()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $tiers = $this->getTiersLocal();
    //     return view('reports.Discount', ["hierachy" => $hierachy, "tiers" => $tiers, 'menu' => $menu]);
    // }

    // public function CashReport()
    // {
    //     $menu = $this->menuReports();
    //     return view('reports.Cash', ['menu' => $menu]);
    // }

    // public function SlTxTpReport()
    // {
    //     $menu = $this->menuReports();
    //     return view('reports.SlTxTp', ['menu' => $menu]);
    // }

    // public function CheckListReportPage()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     $years = DB::select('SELECT YEAR(fechaGenerada) as year FROM checklist_generados GROUP BY YEAR(fechaGenerada)');
    //     return view('reports.CheckList', ['hierachy' => $hierachy, 'menu' => $menu, 'years' => $years]);
    // }

    // public function CheckListInci()
    // {
    //     $menu = $this->menuReports();
    //     $location = new UserLocation();
    //     $hierachy = json_decode(json_encode($location->getHierachy2()));
    //     return view('reports.CheckListInci', ['hierachy' => $hierachy, 'menu' => $menu]);
    // }

    // public function Mantenimiento()
    // {

    //     $menu = $this->menuReports();
    //     $location = new UserLocation();
    //     $hierachy = json_decode(json_encode($location->getHierachy2()));
    //     $sucursales = '';
    //     $user = Auth::user();
    //     $idEmpresa = $user->idEmpresa;

    //     if (!empty(session('sucursales'))) {
    //         $sucursales = "AND B.idSucursal IN (" . session('sucursales') . ")";
    //     }

    //     $sql = "SELECT C.name, C.id as idUsuario FROM config_app_access AS A INNER JOIN manto_sucursal_usuario B ON A.idUsuario = B.idUsuario INNER JOIN users AS C ON A.idUsuario = C.id WHERE A.idAplicacion = 5 AND A.idRole = 3 AND C.idEmpresa = $idEmpresa $sucursales GROUP BY C.id,C.name";
    //     $tecnicos = DB::select($sql, []);
    //     return view('reports.Mantenimiento', ['hierachy' => $hierachy, 'menu' => $menu, 'tecnicos' => $tecnicos]);
    // }

    public function BudgetReport()
    {
        $menu = $this->menuReports();
        $location = new UserLocation();
        $hierachy = json_decode(json_encode($location->getHierachy2()));
        // $hierachy = json_decode(json_encode($this->getHierachy()));
        $meses = json_decode(json_encode($this->getMeses()));
        /*
        $budget = new Budget(22,"2022-08-01");
        $sucs = [27, 4, 22, 1, 3, 39, 8, 25, 68, 28, 2, 21, 60, 20, 5, 26, 6, 16, 55, 7, 9, 10, 11, 12, 32, 18, 33, 13, 67, 14, 15];
        foreach($sucs AS $suc) {
            $budget->setLocation($suc);
            $budget->redistributeBuget(); 
        }
        */
        return view('reports.Budget', ["hierachy" => $hierachy, "meses" => $meses, 'menu' => $menu]);
    }

    // public function CheckListReport(Request $request)
    // {
    //     $fecha = empty($request->input("fecha")) ? date("Y-m-", strtotime(date("Y-m-d") . " -1 MONTH")) . "01" : $request->input("fecha");
    //     $reporte = new Report(13, ["daterange" => $fecha, "location" => 1], "email");
    //     return $reporte->runReport();
    // }

    // public function RVCSales()
    // {
    //     $reporte = new Report(14, ["daterange" => "2022-09-01 - 2022-09-30", "location" => 1], "json");
    //     return $reporte->runReport();
    // }

    // public function GuestWeekPage()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     return view('reports.GuestWeek', ['hierachy' => $hierachy, 'menu' => $menu]);
    // }


    // public function VentasSucPage()
    // {
    //     $menu = $this->menuReports();
    //     $hierachy = json_decode(json_encode($this->getHierachy()));
    //     return view('reports.VentaSucDia', ['hierachy' => $hierachy, 'menu' => $menu]);
    // }

    // public function VentaSucReport(Request $request)
    // {
    //     $reporte = new Report(23, ["daterange" => "2022-12-01 - 2022-12-18", "location" => 4], "json");
    //     return $reporte->runReport();
    // }

    // public function AnalisisPrecioPage()
    // {
    //     $menu = $this->menuReports();
    //     $location = new UserLocation();
    //     $hierachy = json_decode(json_encode($location->getHierachy2(1)));
    //     return view('reports.AnalisisPrecio', ['hierachy' => $hierachy, 'menu' => $menu]);
    // }

    // public function GuestWeekReport(Request $request)
    // {
    //     $reporte = new Report(16, ["daterange" => "2022-12-01 - 2022-12-18", "location" => 4], "json");
    //     return $reporte->runReport();
    // }

    // public function budgetRedistribute(Request $request)
    // {
    //     $fecha = empty($request->input("fecha")) ? date("Y-m-d") : $request->input("fecha");
    //     $budget = new Budget(22, $fecha);

    //     $sql = "SELECT id FROM sucursales WHERE idEmpresa=1 AND franquicia=0 AND idTipo>0 and estado= 1;";
    //     $sucursales = DB::select($sql, []);

    //     $sucs = array();

    //     foreach ($sucursales as $suc) {
    //         $sucs[] = $suc->id;
    //     }

    //     foreach ($sucs as $suc) {
    //         $budget->setLocation($suc);
    //         $budget->redistributeBuget();
    //     }
    // }

    // public function budgetDistribute(Request $request)
    // {
    //     $fecha = empty($request->input("fecha")) ? date("Y-m-01") : $request->input("fecha");
    //     $budget = new Budget(22, $fecha);
    //     $sucs = [27, 4, 22, 1, 3, 39, 8, 68, 28, 2, 21, 60, 20, 5, 26, 6, 16, 55, 7, 9, 10, 11, 12, 32, 18, 33, 13, 67, 14, 15, 76, 75, 73, 56, 57, 58, 61, 70, 24, 50, 53];
    //     foreach ($sucs as $suc) {
    //         $budget->setLocation($suc);
    //         $budget->distributeBuget();
    //     }
    // }

    // public function deliveryReport(Request $request)
    // {
    //     // $reporte = new Report(13, ["daterange" => $fecha, "location" => 1], "email");
    //     // return $reporte->runReport();
    // }

    // public function getWidgetReportData(Request $request, $id, $format = "json")
    // {

    //     if (!empty($request->input("daterange")) && !empty($request->input("location")) && !empty($id)) {

    //         $reporte = new Report($id, $request->all(), $format);

    //         return $reporte->widget();
    //     }

    //     return response()->json(["success" => false, "msg" => "All filter params are mandatory!!"]);
    // }

    // public function getWidgetReportDataMovil(Request $request, $id, $format = "json")
    // {

    //     if (!empty($request->input("daterange")) && !empty($request->input("location")) && !empty($id)) {

    //         $reporte = new Report($id, $request->all(), $format);

    //         return $reporte->widget();
    //     }

    //     return response()->json(["success" => false, "msg" => "All filter params are mandatory!!"]);
    // }

    // public function GetReportData(Request $request, $id, $format = "json")
    // {

    //     if (!empty($request->input("daterange")) && !empty($request->input("location")) && !empty($id)) {

    //         $reporte = new Report($id, $request->all(), $format);

    //         return $reporte->runReport();
    //     }

    //     return response()->json(["success" => false, "msg" => "All filter params are mandatory!!"]);
    // }

    // public function SettingsIndex()
    // {
    //     return view('reports.SettingsIndex', []);
    // }

    public function getMeses()
    {
        return array(
            array("mes" => "Enero 2023", "id" => "2023-01-01"), array("mes" => "Febrero 2023", "id" => "2023-02-01"), array("mes" => "Marzo 2023", "id" => "2023-03-01"), array("mes" => "Abril 2023", "id" => "2023-04-01"), array("mes" => "Mayo 2023", "id" => "2023-05-01"), array("mes" => "Junio 2023", "id" => "2023-06-01"), array("mes" => "Julio 2023", "id" => "2023-07-01"), array("mes" => "Agosto 2023", "id" => "2023-08-01"), array("mes" => "Septiembre 2023", "id" => "2023-09-01"), array("mes" => "Octubre 2023", "id" => "2023-10-01"), array("mes" => "Noviembre 2023", "id" => "2023-11-01"), array("mes" => "Diciembre 2023", "id" => "2023-12-01"), array("mes" => "Agosto 2022", "id" => "2022-08-01"), array("mes" => "Septiembre 2022", "id" => "2022-09-01"), array("mes" => "Octubre 2022", "id" => "2022-10-01"), array("mes" => "Noviembre 2022", "id" => "2022-11-01"), array("mes" => "Diciembre 2022", "id" => "2022-12-01")

        );
    }

    // public function getHierachy()
    // {

    //     $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";
    //     $empresas = DB::select($sql, [Auth::id()]);
    //     $hierachy = array();

    //     foreach ($empresas as $empresa) {
    //         if (session('RepRole') == 1)
    //             $hierachy[] = array("id" => $empresa->idEmpresa, "nombre" => $empresa->empresa, "tipo" => 1);
    //         if (session('RepRole') > 1)
    //             $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND id IN (" . session('sucursales') . ") AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
    //         else
    //             $sql = "SELECT * FROM sucursales WHERE idEmpresa = ? AND estado = 1 AND idTipo > 0 ORDER BY nombre;";
    //         $sucursales = DB::select($sql, [$empresa->idEmpresa]);

    //         foreach ($sucursales as $sucursal) {
    //             $hierachy[] = array("id" => $sucursal->idMicros, "nombre" => $sucursal->nombre, "tipo" => 2);
    //         }
    //     }
    //     return $hierachy;
    // }

    // public function getHierachyVue()
    // {
    //     $location = new UserLocation();
    //     $hierachy = $location->getHierachy2();
    //     return $hierachy;
    // }

    // public function getTiersLocal()
    // {

    //     $sql = "SELECT GROUP_CONCAT(empresas.idEmpresa) idEmpresa FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE dashboard_empresa_usuario.idUsuario = ? GROUP BY dashboard_empresa_usuario.idUsuario;";
    //     $empresas = DB::select($sql, [Auth::id()]);

    //     $sql = "SELECT * FROM sucursales_tier WHERE idEmpresa IN (" . $empresas[0]->idEmpresa . ") ";
    //     $tiers = DB::select($sql, []);

    //     return $tiers;
    // }

    // public function getTiers(Request $request)
    // {
    //     $idEmpresa = $request->input('idEmpresa');
    //     if (!empty($idEmpresa)) {

    //         if (!is_numeric($idEmpresa)) {
    //             $sql = "SELECT GROUP_CONCAT(empresas.idEmpresa) idEmpresa FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE dashboard_empresa_usuario.idUsuario = ? GROUP BY dashboard_empresa_usuario.idUsuario;";
    //             $empresas = DB::select($sql, [Auth::id()]);

    //             $sql = "SELECT idEmpresa FROM sucursales WHERE idMicros = ?";
    //             $empresa = DB::select($sql, [$idEmpresa]);
    //             $idEmpresa = $empresa[0]->idEmpresa;
    //         }


    //         $sql = "SELECT * FROM sucursales_tier WHERE idEmpresa IN (" . $idEmpresa . ")";
    //         $tiers = DB::select($sql, []);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $tiers
    //     ]);
    // }

    // public function getFamilies()
    // {
    //     $sql = "SELECT GROUP_CONCAT(empresas.idEmpresa) idEmpresa FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE dashboard_empresa_usuario.idUsuario = ? GROUP BY dashboard_empresa_usuario.idUsuario;";
    //     $empresas = DB::select($sql, [Auth::id()]);

    //     $sql = "SELECT idFamily, family FROM micros_family_group WHERE idEmpresa IN (" . $empresas[0]->idEmpresa . ") ";
    //     $families = DB::select($sql, []);

    //     return $families;
    // }

    // public function getMicrosItem(Request $request)
    // {
    //     $q = $request->input('q');

    //     $sql = "SELECT empresas.* FROM dashboard_empresa_usuario INNER JOIN empresas ON empresas.idEmpresa = dashboard_empresa_usuario.idEmpresa WHERE idUsuario = ?;";

    //     $empresas = DB::select($sql, [Auth::id()]);
    //     $company = $empresas[0]->idEmpresa;

    //     $sql = "SELECT idItemMicros AS id, itemName AS name FROM micros_producto MP WHERE itemName LIKE '%$q%' AND MP.idEmpresa IN ($company) AND MP.lastSale >='2021-10-01'";
    //     $items = DB::select($sql, []);

    //     return  response()->json([
    //         'total_count' => count($items),
    //         'items' => $items,
    //         'page' => 1
    //     ]);
    // }
    // public function getMajorAndFamily(Request $request)
    // {
    //     $user = Auth::user();
    //     $empresa = $request->input('location');

    //     if (!is_numeric($empresa)) {
    //         $sql = "SELECT idEmpresa FROM sucursales WHERE idMicros = ?";
    //         $idEmpresa = DB::select($sql, [$empresa]);
    //         $empresa = $idEmpresa[0]->idEmpresa;
    //     }

    //     $sql = "SELECT idMajor as id, major as name FROM micros_major_group WHERE idEmpresa = ?";
    //     $majors = DB::select($sql, [$empresa]);

    //     return  response()->json([
    //         'success' => true,
    //         'majors' => $majors,
    //     ]);
    // }

    // public function getFamiliesForm(Request $request)
    // {
    //     $idMajor = $request->input('idMajor');
    //     $empresa = $request->input('location');

    //     if (!is_numeric($empresa)) {
    //         $sql = "SELECT idEmpresa FROM sucursales WHERE idMicros = ?";
    //         $idEmpresa = DB::select($sql, [$empresa]);
    //         $empresa = $idEmpresa[0]->idEmpresa;
    //     }

    //     $sql = "SELECT idFamily as id, family as name FROM micros_family_group WHERE idMajor = ? AND idEmpresa = ?";
    //     $families = DB::select($sql, [$idMajor, $empresa]);

    //     return  response()->json([
    //         'success' => true,
    //         'families' => $families,
    //     ]);
    // }

    // public function saveClasiMicrosProd(Request $request)
    // {

    //     $user = Auth::user();
    //     $empresa = $user->idEmpresa;
    //     $idMicros = $request->input('idMicros');
    //     $idFamily = $request->input('idFamily');
    //     $idMajor = $request->input('idMajor');

    //     if (!empty($idMicros) && !empty($idFamily) && !empty($idMajor)) {

    //         $sql = "INSERT INTO micros_producto_clasificacion (idEmpresa, idMicros, idMajor, idFamily) VALUES (?,?,?,?);";
    //         $insert = DB::insert($sql, [$empresa, $idMicros, $idMajor, $idFamily]);

    //         if ($insert == true) {
    //             return  response()->json([
    //                 'success' => true,
    //                 'msg' => 'Se asigno correctamente'
    //             ]);
    //         } else {
    //             return  response()->json([
    //                 'success' => false,
    //                 'msg' => 'Algo salio mal del lado del servidor',

    //             ]);
    //         }
    //     } else {

    //         return  response()->json([
    //             'success' => false,
    //             'msg' => 'Datos faltantes',
    //         ]);
    //     }
    // }

    // public function getWidgetList($all = 0)
    // {

    //     $sql = "SELECT B.idReporte, B.idTipo, B.size, C.idClase, C.ruta, B.data FROM config_reportes C INNER JOIN config_reportes_usuario A ON A.idReporte= C.idReporte INNER JOIN config_reportes_widget B ON A.idReporte = B.idReporte WHERE A.widgetVisible = ? AND A.idUsuario = ?;";

    //     $widgets = DB::select($sql, [1, Auth::id()]);

    //     foreach ($widgets as $index => $widget) {
    //         $widgets[$index]->ruta = route($widget->ruta);
    //     }

    //     return  response()->json([
    //         'success' => true,
    //         'data' => $widgets
    //     ]);
    // }

    // public function getWidgetListMovil($all = 0, Request $request)
    // {
    //     $idUsuario = $request->input('idUsuario');
    //     $sql = "SELECT B.idReporte, B.idTipo, B.size, C.idClase, C.ruta, B.data FROM config_reportes C INNER JOIN config_reportes_usuario A ON A.idReporte= C.idReporte INNER JOIN config_reportes_widget B ON A.idReporte = B.idReporte WHERE A.widgetVisible = ? AND A.idUsuario = ?;";

    //     $widgets = DB::select($sql, [$all, $idUsuario]);

    //     // foreach ($widgets as $index => $widget) {
    //     //     $widgets[$index]->ruta = route($widget->ruta);
    //     // }

    //     return  response()->json([
    //         'success' => true,
    //         'data' => $widgets
    //     ]);
    // }

    public function menuReports()
    {
        $sql = "SELECT * FROM config_reportes_categoria";
        $cat = DB::select($sql, []);
        $sql = "SELECT cru.idReporte, cr.nombre, cr.ruta, cr.idCategoria, '' rutaCompleta FROM config_reportes_usuario cru INNER JOIN config_reportes cr ON cr.idReporte = cru.idReporte WHERE cru.idUsuario = ? AND cr.estado = 1";
        $items = DB::select($sql, [1]);


        foreach ($cat as $key => $value) {
            $value->menu = [];
            foreach ($items as $keyItem => $item) {

                if ($value->idCategoria == $item->idCategoria) {
                    $item->rutaCompleta = !empty($item->ruta) ? route($item->ruta) : "#";
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

    // public function getProveedores(Request $request)
    // {
    //     $idEmpresa = $request->company;

    //     $proveedores = DB::table('pedidos_proveedor')
    //         ->select('idProveedor AS id', 'nombre')
    //         ->where('idEmpresa', $idEmpresa)
    //         ->get()
    //         ->toArray();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $proveedores,
    //     ], 200);
    // }

    // public function getArituclos(Request $request)
    // {
    //     $idEmpresa = $request->company;
    //     $query = "%{$request->input('query')}%";

    //     $sql = "SELECT PA.CodPrigo as id, PA.Descripcion as name FROM pedidos_articulo PA INNER JOIN pedidos_proveedor PP ON PA.idProveedor = PP.idProveedor WHERE PP.idEmpresa = ? AND PA.Descripcion LIKE ? GROUP BY PA.CodPrigo, PA.Descripcion";

    //     $articulos = DB::select($sql, [$idEmpresa, $query]);

    //     return response()->json([
    //         'data' => $articulos,
    //         'total_count' => count($articulos),
    //     ], 200);
    // }
}