<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PruebaController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RHVacanteController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('home', [HomeController::class, 'index'])->name('home'); 
Route::get('/', function () {
    return view('auth.login'); 
});

 
Route::post('login', [AuthApiController::class, 'login'])->name('login');

// Todo esto tiene que ver con el controlador de RH. Recuerda moverlo de tal manera que se minimice el codigo.
Route::get('home', [HomeController::class, 'index'])->name('home'); 
Route::get('prueba', [PruebaController::class, 'index'])->name('prueba'); 
Route::get('vacantes', [RHVacanteController::class, 'index'])->name('vacantes');
Route::get('plantilla', [RHVacanteController::class, 'showGlobalHeadcount'])->name('plantilla');
Route::get('plantilla/sucursal', [RHVacanteController::class, 'detPlantillaTable'])->name('detPlantillaTable');
Route::get('plantilla/ver/{nombre?}/{id}', [RHVacanteController::class, 'plantillaDetail'])->name('plantillaDetail');
Route::post('guardabaja', [RHVacanteController::class, 'savebaja'])->name('guardabaja');
Route::post('empleado/detail', [RHVacanteController::class, 'getEmployeeDetail'])->name('getEmployeeDetail');
Route::post('actualizaEmpleado', [RHVacanteController::class, 'actualizaEmpleado'])->name('actualizaEmpleado');
Route::post('empleado/verificar/puestos', [RHVacanteController::class, 'verificarPuestos'])->name('verificarPuestos');
Route::get('plantilla/editar/{id}', [RHVacanteController::class, 'editarPlantilla'])->name('editPlantilla');
Route::post('plantilla/editar/actualizar', [RHVacanteController::class, 'actualizarPlantilla'])->name('actualizarPlantilla');
Route::post('plantilla/editar/agregar', [RHVacanteController::class, 'agregarPuestoSuc'])->name('agregarPuestoSuc');
Route::post('plantilla/editar/borrar', [RHVacanteController::class, 'borrarPuestoSuc'])->name('borrarPuestoSuc');
Route::post('plantilla/editar/actualizar/tabla', [RHVacanteController::class, 'actualizarPlantillaTabla'])->name('actualizarPlantillaTabla');
Route::get('plantilla/download/{idSucursal}', [RHVacanteController::class, 'downloadPlantilla'])->name('xlsPlantilla');
Route::get('vacantes/consultavacantes', [RHVacanteController::class, 'showRequests'])->name('consultavacantes');
Route::post('vacantes/exportar', [RHVacanteController::class, 'exportRequest'])->name('exportavacantes');
Route::post('vacantes/getSolicitudes', [RHVacanteController::class, 'getRequest'])->name('getSolicitudes');
Route::get('detallevacante/{id?}', [RHVacanteController::class, 'requestDetail'])->name('detallevacante');
Route::get('vacantes/consultaRetrasadas', [RHVacanteController::class, 'showRetrasadas'])->name('showRetrasadas');
Route::get('vacantes/consultaEnTiempo', [RHVacanteController::class, 'showEnTiempo'])->name('showEnTiempo');
Route::get('vacantes/nuevavacante', [RHVacanteController::class, 'showNewRequestForm'])->name('nuevavacante');
Route::get('vacantes/getEmpleados', [RHVacanteController::class, 'getEmployees'])->name('getEmpleados');
Route::get('vacantes/listPuestosCrece', [RHVacanteController::class, 'getPuestosGrowup'])->name('getpuestoscrece');
Route::get('vacantes/listPuestos', [RHVacanteController::class, 'getPuestosList'])->name('getpuestos');
Route::get('getPuestos', [RHVacanteController::class, 'getPuestos'])->name('getPuestos');
Route::get('vacantes/validaPuesto', [RHVacanteController::class, 'validaPuesto'])->name('validapuesto');
Route::get('vacantes/guardasolicitud', [RHVacanteController::class, 'saveRequest'])->name('guardasolicitud');
//Route::get('vacantes/consultavacantes', [RHVacanteController::class, 'showRequests'])->name('consultavacantes'); <-- La agregue por que estaba viendo los links del menu lateral.
// Yasser del futuro: Quedaron pendientes los links /vacantes/contrataciones y /vacantes/capacitados
// Tienes que volver a hacer los submodulos por que se eliminaron hace un tiempo por malos manejos de RH (les daba hueva)
// P.D. Lo estas haciendo bien amix uwu
Route::get('vacantes/bajas', [RHVacanteController::class, 'showPendingDismiss'])->name('getBajas');
Route::get('empleados/baja/get', [RHVacanteController::class, 'getBaja'])->name('getBaja');
Route::get('vacantes/getSolicitudesBaja', [RHVacanteController::class, 'getDismissRequest'])->name('getSolicitudesBaja');
Route::get('vacantes/empleados', [RHVacanteController::class, 'showEmployees'])->name('empleados');
Route::get('empleados/crear/{id?}', [RHVacanteController::class, 'formNewEmployee'])->name('formNewEmployee');
Route::get('empleados/alta', [RHVacanteController::class, 'uploadXlsxScreen'])->name('uploadXlsx');
Route::post('empleado/registrar', [RHVacanteController::class, 'registrarEmpleado'])->name('registrarEmpleado');
Route::get('empleado/{id?}', [RHVacanteController::class, 'employeeDetail'])->name('detalleempleado');
Route::get('gestion', [RHVacanteController::class, 'gestionPuestos'])->name('gestionPuestos');
Route::post('gestion/agregar', [RHVacanteController::class, 'agregarPuesto'])->name('agregarPuesto');
Route::post('gestion/editar', [RHVacanteController::class, 'editarPuesto'])->name('editarPuesto');
Route::post('gestion/eliminar', [RHVacanteController::class, 'eliminarPuesto'])->name('eliminarPuesto');
Route::get('micros', [RHVacanteController::class, 'micros'])->name('micros');
Route::post('micros/obtener/perfiles', [RHVacanteController::class, 'getPerfilesMicros'])->name('getPerfilesMicros');
Route::post('micros/obtener/empleados', [RHVacanteController::class, 'getEmpleadosMicros'])->name('getEmpleadosMicros');
Route::post('micros/crear', [RHVacanteController::class, 'crearEmpleadoPerf'])->name('crearEmpleadoPerf');
Route::post('micros/agrupar', [RHVacanteController::class, 'agruparPerfilesEmp'])->name('agruparPerfilesEmp');
Route::get('reports', [ReportsController::class, 'indexVue'])->name('reportsIndex');

// Los reportes uwu
Route::get('venta/mes',[SalesController::class, 'mensual'])->name('ventaMensual');
Route::get('venta/lastyear',[SalesController::class, 'getLastYear'])->name('getLastYear');
Route::get('reports/Budget/',[ReportsController::class, 'BudgetReport'])->name('budgetOps');
Route::get('reports/VentaSemProd/', [ReportsController::class, 'VSProdReport'])->name('ventaSemProd');
Route::get('reports/CheckListPrub/', [ReportsController::class, 'CheckListReportPage'])->name('CheckListReportPage');
Route::get('guests/lastyear', [SalesController::class, 'getLastYearGuest'])->name('getLastYearGuest');
Route::get('reports/guest/week', [ReportsController::class, 'GuestWeekPage'])->name('GuestWeekPage');
Route::post('reports/delivery', [ReportsController::class, 'deliveryReport'])->name('deliveryReportPage');
Route::get('reports/checklist/incidencias', [ReportsController::class, 'CheckListInci'])->name('CheckListInci');
Route::get('reports/mantenimiento', [ReportsController::class, 'Mantenimiento'])->name('MantoReport');
Route::get('reports/mantenimiento/holamundo', [ReportsController::class, 'Mantenimiento'])->name('deliveryItemReportPage'); // Este esta mal, pero para que jale
Route::get('reports/ProductMix/', [ReportsController::class, 'ProductMixReport'])->name('reportsPMix');
Route::get('reports/PMixTB/', [ReportsController::class, 'PMixTBReport'])->name('reportsPMixTB');
Route::get('reports/DayPart/', [ReportsController::class, 'DayPartReport'])->name('reportsDPart');
Route::get('reports/VIT/', [ReportsController::class, 'VITReport'])->name('reportsVIT');
Route::get('reports/Discounts/', [ReportsController::class, 'DiscountsReport'])->name('reportsDsc');
Route::get('dashboard-encuesta/reports', [DashboardEncuestaController::class, 'getReportsView'])->name('encuestaReports');
Route::get('reports/Cash/', [ReportsController::class, 'CashReport'])->name('reportsCash');
Route::get('reports/ventas/sucursal', [ReportsController::class, 'VentasSucPage'])->name('VentasSucPage');
Route::get('inventory/cost', [InventarioController::class, 'costo'])->name('costoVenta');
Route::get('reports/MenuEngineering/', [ReportsController::class, 'MenuEngineeringReport'])->name('reportsME');
Route::get('reports/analisis/precio', [ReportsController::class, 'AnalisisPrecioPage'])->name('AnalisisPrecioPage');
Route::post('venta/get/sucursales', [SalesController::class, 'getSucursales'])->name('getSucursalesVen');
Route::get('venta/mes/detalle', [SalesController::class, 'getMensual'])->name('getDetVenta');
Route::post('venta/mes/detalle/Xls', [SalesController::class, 'getMensualXls'])->name('getDetVentaXls');
Route::any('/reports/get/{id}/{format?}', [ReportsController::class, 'GetReportData'])->name('getReport');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');
// Route::get('', [::class, ''])->name('');








