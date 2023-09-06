<?php

use App\Http\Controllers\PruebaController;
use App\Http\Controllers\RHVacanteController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('prueba', [PruebaController::class, 'index']);
Route::get('vacante', [RHVacanteController::class, 'index']);
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

