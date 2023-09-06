<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RHVacanteController extends Controller
{  
  public function index()
  {
    $RHRole = 1; //session('RHRole');
    // $sucursales = session('sucursales');
    $strValSucursales = "";
    // if ($RHRole != 1)
    //   if (!empty($sucursales))
    //     $strValSucursales = " IN (" . $sucursales . ") ";
    //   else 
    $strValSucursales = " IN (0) ";

    // Este fue el de prueba, ya que funciono, lo dejo para futuras pruebas. 
    // $sql = "SELECT * FROM sucursales;";
    // $res = DB::select($sql);
    // dd($res); <-- Esto nos manda en la pantalla el resultado estilo json de la consulta.

    $sql = "SELECT SUM(autorizado.total)autorizados, SUM(empleados) empleados, SUM(autorizado.total) -SUM(empleados) vacantes, SUM(solicitudes.total) solicitudes, SUM(solicitudes.atraso) solicitudes_atraso, SUM(solicitudes.bien) solicitudes_bien FROM (SELECT 'vacante' tipo,of.nombre oficina, plazas.* FROM (SELECT idSucursal, SUM(cantidad) total FROM rh_plazas_autorizadas " . ($RHRole != 1 ? " WHERE idSucursal " . $strValSucursales : "") . " GROUP BY idSucursal)plazas INNER JOIN sucursales of ON of.id = plazas.idSucursal) autorizado LEFT JOIN (SELECT idSucursal, COUNT(estado) empleados FROM rh_empleado WHERE estado = 1 GROUP BY idSucursal) empleados ON autorizado.idSucursal = empleados.idSucursal LEFT JOIN (SELECT idSucursal, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3)) data GROUP BY idSucursal,estado)solicitudes ON solicitudes.idSucursal = autorizado.idSucursal GROUP BY tipo;";
    $vacantes = DB::select($sql);
    $actuales = empty($vacantes[0]->empleados) ? 0 : $vacantes[0]->empleados;
    $autorizados = empty($vacantes[0]->autorizados) ? 0 : $vacantes[0]->autorizados;
    $diferencia = empty($vacantes[0]->vacantes) ? 0 : $vacantes[0]->vacantes;
    $abiertas = empty($vacantes[0]->solicitudes) ? 0 : $vacantes[0]->solicitudes;
    $retrasadas = empty($vacantes[0]->solicitudes_atraso) ? 0 : $vacantes[0]->solicitudes_atraso;
    $entiempo = empty($vacantes[0]->solicitudes_bien) ? 0 : $vacantes[0]->solicitudes_bien;

    $sql = "SELECT COUNT(estado) total FROM rh_vacante_solicitud_partida WHERE (MONTH(lastUpdateDate) = " . date("m") . " AND YEAR(lastUpdateDate) = " . date("Y") . ") AND estado IN (4) " . ($RHRole != 1 ? " AND idSucursal " . $strValSucursales : "") . " ;";
    $cerradas = DB::select($sql);


    $sql = "SELECT SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien   FROM (SELECT 2018 anio,idSucursal, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3)) data GROUP BY idSucursal,estado UNION ALL SELECT 2018 anio,idSucursal, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4)) data GROUP BY idSucursal,estado) solicitudes GROUP BY anio;";
    $efectividad = DB::select($sql);

    $sql = "SELECT base.*, actual.perbien actual, actual.bien bienActual, actual.atraso atrasoActual, anterior.perbien anterior FROM (SELECT solicitudes.idReclutador, rh_reclutador.nombre ,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM ( SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) ) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado UNION ALL SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4)	) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado) solicitudes INNER JOIN rh_reclutador ON solicitudes.idReclutador = rh_reclutador.idReclutador GROUP BY anio,solicitudes.idReclutador,rh_reclutador.nombre ) base LEFT JOIN (SELECT solicitudes.idReclutador,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM ( SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) AND MONTH(sol.fechaCrea) = ".date("m")." ) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado UNION ALL SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4) AND MONTH(sol.fechaCrea) = ".date("m")."	) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado) solicitudes INNER JOIN rh_reclutador ON solicitudes.idReclutador = rh_reclutador.idReclutador GROUP BY anio,solicitudes.idReclutador,rh_reclutador.nombre) actual ON actual.idReclutador = base.idReclutador LEFT JOIN (SELECT solicitudes.idReclutador,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM ( SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) AND MONTH(sol.fechaCrea) = ".(date("m")-1)." ) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado UNION ALL SELECT 2018 anio,idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4) AND MONTH(sol.fechaCrea) = ".(date("m")-1)."	) data INNER JOIN rh_reclutador_sucursal recsuc ON data.idSucursal = recsuc.idSucursal GROUP BY idReclutador,estado) solicitudes INNER JOIN rh_reclutador ON solicitudes.idReclutador = rh_reclutador.idReclutador GROUP BY anio,solicitudes.idReclutador,rh_reclutador.nombre) anterior ON anterior.idReclutador = base.idReclutador ORDER BY base.perbien DESC;";
    $sql = "SELECT 	base.*, actual.perbien actual, actual.bien bienActual, actual.atraso atrasoActual, anterior.perbien anterior FROM 	( 		SELECT solicitudes.idReclutador, users.name nombre ,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM 		( 			SELECT 2018 anio, recsuc.idUsuario idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) ) data 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 			UNION ALL 			SELECT 2018 anio, recsuc.idUsuario idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4)	) data 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 		) solicitudes INNER JOIN users ON solicitudes.idReclutador = users.id GROUP BY anio,solicitudes.idReclutador,users.name 		 	) 	base LEFT JOIN 	(	 		SELECT solicitudes.idReclutador,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM 		( 			SELECT 2018 anio, recsuc.idUsuario idReclutador,  COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) AND MONTH(sol.fechaCrea) = " . date("m") . " ) data 		 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 			UNION ALL 			SELECT 2018 anio, recsuc.idUsuario idReclutador,  COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4) AND MONTH(sol.fechaCrea) = " . date("m") . "	) data 			 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 		) solicitudes INNER JOIN users ON solicitudes.idReclutador = users.id GROUP BY anio,solicitudes.idReclutador,users.name 		 	) 	actual ON actual.idReclutador = base.idReclutador LEFT JOIN 	( 		SELECT solicitudes.idReclutador,SUM(total) total, SUM(atraso) atraso, SUM(bien)bien , SUM(atraso)/SUM(total)*100 peratraso, SUM(bien)/SUM(total)*100 perbien FROM 		( 			SELECT 2018 anio, recsuc.idUsuario idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM (	SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) AND MONTH(sol.fechaCrea) = " . (date("m") - 1) . " ) data 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 			UNION ALL 			SELECT 2018 anio, recsuc.idUsuario idReclutador, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) bien FROM ( SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,IF( partida.lastUpdateDate > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (4) AND MONTH(sol.fechaCrea) = " . (date("m") - 1) . "	) data 			INNER JOIN rh_sucursal_usuario recsuc ON data.idSucursal = recsuc.idSucursal INNER JOIN config_app_access ON (recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3)  GROUP BY recsuc.idUsuario,estado 		) solicitudes INNER JOIN users ON solicitudes.idReclutador = users.id GROUP BY anio,solicitudes.idReclutador,users.name 	) anterior ON anterior.idReclutador = base.idReclutador ORDER BY base.perbien DESC;";
    $efectividadReclutador = DB::select($sql);

    return view('vacantes.index', ['autorizados' => $autorizados, "cerradas" => (empty($cerradas) ? 0 : $cerradas[0]->total), "abiertas" => $abiertas, "retrasadas" => $retrasadas, "entiempo" => $entiempo, 'actuales' => $actuales, 'diferencia' => $diferencia, 'efectividad' => $efectividad, 'efectividadReclutador' => $efectividadReclutador, 'role' => session('RHRole')]);
    //return view('pruebitas.prueba1');
  }

  public function showGlobalHeadcount()
  {

    $RHRole = 1;
    //$RHRole = session('RHRole');
    // $sucursales = session('sucursales');
    // $user = Auth::user();
    // $idEmpresa = $user->idEmpresa;
    $idEmpresa = 1;
    // $strValSucursales = "";
    // if ($RHRole != 1)
    //   if (!empty($sucursales))
    //     $strValSucursales = "AND idSucursal IN (" . $sucursales . ") ";
    //   else
        // $strValSucursales = "AND idSucursal IN (0)";
        $strValSucursales = "AND idSucursal IN (0)";
    $sql = "SELECT autorizado.idSucursal, autorizado.oficina ,autorizado.total autorizado, empleados.empleados, solicitudes.total, solicitudes.atraso, solicitudes.bien, contratacion.contrataciones, bajasMenor.bajaMenor, bajasMayor.bajaMayor FROM 
				(SELECT of.id AS idSucursal,of.nombre oficina, plazas.total FROM (
						SELECT idSucursal, SUM(cantidad) total FROM rh_plazas_autorizadas GROUP BY idSucursal)
				plazas RIGHT JOIN sucursales of ON of.id = plazas.idSucursal WHERE estado = 1 AND idEmpresa = $idEmpresa ) 
			autorizado LEFT JOIN (
				SELECT idSucursal, COUNT(estado) empleados FROM rh_empleado WHERE estado = 1 GROUP BY idSucursal) 
			empleados ON autorizado.idSucursal = empleados.idSucursal LEFT JOIN (
				SELECT idSucursal, COUNT(estado) total, SUM(IF(atraso=1,1,0)) atraso, SUM(IF(atraso=0,1,0)) 
			bien FROM (
				SELECT partida.idSucursal ,sol.fechaCrea, ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, partida.lastUpdateDate ,
				IF(NOW() > ADDDATE(sol.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0)atraso , partida.estado FROM rh_vacante_solicitud sol 
				INNER JOIN rh_vacante_solicitud_partida partida ON sol.idSolicitud = partida.idSolicitud INNER JOIN rh_tiempo_contrata tiempo 
				ON tiempo.idPuesto = partida.idPuesto WHERE partida.estado IN (1,2,3) ) 
			data GROUP BY idSucursal )solicitudes ON solicitudes.idSucursal = autorizado.idSucursal LEFT JOIN (SELECT COUNT(A.idEmpLog) AS contrataciones,B.idSucursal FROM rh_vacante_log AS A INNER JOIN 
			rh_empleado B ON A.idEmpleado = B.idEmpleado WHERE A.idAccion = 7 GROUP BY B.idSucursal) contratacion ON autorizado.idSucursal = contratacion.idSucursal 
			LEFT JOIN (SELECT count(bajaMenor) AS bajaMenor, idSucursal FROM (SELECT A.idEmpLog AS bajaMenor, B.idSucursal FROM rh_vacante_log A 
			INNER JOIN rh_empleado B ON A.idEmpleado = B.idEmpleado WHERE A.idAccion = 3 AND DATEDIFF('2023-02-23', A.fechaCrea) < 90 GROUP BY idSucursal, A.idEmpLog
			UNION all
			SELECT C.idBaja AS bajaMenor, B.idSucursal FROM rh_empleado_baja AS C 
			INNER JOIN rh_empleado AS B ON C.idEmpleado = B.idEmpleado WHERE DATEDIFF('2023-02-23', C.fecha) < 90 GROUP BY B.idSucursal,C.idBaja) T GROUP BY idSucursal) bajasMenor ON autorizado.idSucursal = bajasMenor.idSucursal LEFT JOIN (
			SELECT count(bajaMayor) AS bajaMayor, idSucursal FROM (SELECT A.idEmpLog AS bajaMayor, B.idSucursal FROM rh_vacante_log A 
			INNER JOIN rh_empleado B ON A.idEmpleado = B.idEmpleado WHERE A.idAccion = 3 AND DATEDIFF('2023-02-23', A.fechaCrea) > 90 GROUP BY idSucursal, A.idEmpLog
			UNION ALL 
			SELECT C.idBaja AS bajaMayor, B.idSucursal AS idSucursal FROM rh_empleado_baja AS C INNER JOIN rh_empleado AS B ON C.idEmpleado = B.idEmpleado WHERE DATEDIFF('2023-02-23', C.fecha) > 90 GROUP BY B.idSucursal, C.idBaja) T GROUP BY idSucursal) bajasMayor ON autorizado.idSucursal = bajasMayor.idSucursal
			ORDER BY autorizado.oficina ASC";
    $plantilla = DB::select($sql);
    //dd($plantilla);
    return view('vacantes.plantilla', ['plantilla' => $plantilla, 'role' => $RHRole]); //session('RHRole')
  }

  public function detPlantillaTable(Request $request) 
  {
    $type = $request->input('type');

    $sql = "SELECT aut.idPuesto, puesto.nombre puesto, puesto.orden, aut.cantidad FROM rh_plazas_autorizadas aut INNER JOIN rh_puesto puesto ON aut.idPuesto = puesto.idPuesto WHERE idSucursal = " . $request->input('ids') . " ORDER BY puesto.orden, puesto.nombre;";
    $autorizados = DB::select($sql);
    $plantilla = array();
    $puestos = "";

    foreach ($autorizados as $autorizado) {

      $sql = "SELECT rh_empleado.idEmpleado ,CONCAT(rh_empleado.nombre, ' ',rh_empleado.apellido_pat, ' ',rh_empleado.apellido_mat) as nombre, rh_puesto.nombre puesto, 0 excedente, D.nombre as area FROM rh_empleado INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_empleado.idPuesto INNER JOIN rh_puesto_area as C ON rh_puesto.idPuesto = C.idPuesto INNER JOIN rh_area as D ON C.idArea = D.id WHERE rh_empleado.idPuesto = " . $autorizado->idPuesto . " AND rh_empleado.idSucursal = " . $request->input('ids') . " AND rh_empleado.estado =1 ORDER BY rh_puesto.orden ASC;";
      $empleados = DB::select($sql);
      $puesto = 0;
      foreach ($empleados as $empleado) {
        $puesto++;

        if ($autorizado->cantidad < $puesto) {
          $empleado->excedente = 1;
        }

        $plantilla[] = $empleado;
        //$autorizado->idPuesto
      }

      $sql  = "SELECT CONCAT('Solicitud #',idSolicitud) nombre, rh_puesto.nombre puesto, 0 excedente FROM rh_vacante_solicitud_partida INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_vacante_solicitud_partida.idPuesto  WHERE rh_vacante_solicitud_partida.estado IN (1,2,3,4,9) AND rh_vacante_solicitud_partida.idPuesto = " . $autorizado->idPuesto . " AND rh_vacante_solicitud_partida.idSucursal = " . $request->input('ids') . " AND rh_vacante_solicitud_partida.estado IN (1);";
      $solicitudes = DB::select($sql);
      foreach ($solicitudes as $solicitud) {
        $puesto++;
        if ($autorizado->cantidad < $puesto) {
          $solicitud->excedente = 1;
        } else {
          $solicitud->excedente = 2;
        }

        $plantilla[] = $solicitud;
        //$autorizado->idPuesto

      }


      if ($autorizado->cantidad > $puesto) {
        for (; $puesto < $autorizado->cantidad; $puesto++) {
          $disponible = new  \stdClass();
          @$disponible->idEmpleado = '';
          @$disponible->nombre = "Disponible " . ($puesto + 1);
          @$disponible->puesto = $autorizado->puesto;
          @$disponible->excedente = 3;
          @$disponible->area = '';
          $plantilla[] = $disponible;
          //$autorizado->idPuesto
        }
      }

      if (!empty($autorizado->idPuesto))
        $puestos .= ",";
      $puestos .= $autorizado->idPuesto;
    }

    return view('vacantes.detPlantillaTable', ['detalle' => $plantilla, 'role' => session('RHRole'), 'type' => $type]);
  }

  public function plantillaDetail($nombre = '', $id = 0)
  {
    //$user = Auth::user();
    //$idEmpresa = $user->idEmpresa;
    $RHRole = 1;
    $idEmpresa = 1;
    $sucursales = DB::table('sucursales')
      ->select('id as idSucursal', 'nombre')
      ->where('idEmpresa', $idEmpresa)
      ->get();
    $puestos = DB::table('rh_puesto')
      ->where('estado', 1)
      ->get();
    $tipoBaja = DB::table('rh_baja_tipo')
      ->get();

    //dd($sucursales);
    //dd($puestos);
    //dd($tipoBaja);

    return view('vacantes.plantillaDetail', ['role' => $RHRole, 'nombre' => $nombre, 'id' => $id, 'sucursales' => $sucursales, 'puestos' => $puestos, 'tipoBaja' => $tipoBaja]);
  }

  public function saveBaja(Request $request)
  {
    if (!empty($request->input('id'))) {
      DB::enableQueryLog();
      $user = Auth::user();
      $idUsuario = $user->id;
      $isValid = true;
      $boletinado = $request->input('boletinado') == 'on' ? 1 : 0;
      $recontratable = $request->input('recontratable') == 'on' ? 1 : 0;
      $idEmpleado = $request->input('id');

      if ($request->input('accion') == 1) {
        DB::table('rh_empleado')
          ->where('idEmpleado', $request->input('id'))
          ->update(['estado' => 6, 'fechaBaja' => date('Y-m-d')]);
        DB::insert('insert into rh_empleado_baja (idEmpleado, comentario, idUsuario, fecha, hora, tipo, boletinado, recontratable) values (?, ?, ?, ?, ?, ?, ?, ?)', [$request->input('id'), $request->input('comentario'), $idUsuario, date("Y-m-d"), date("H:i:s"), $request->input('tipo'), $boletinado, $recontratable]);
        $lid = DB::getPdo()->lastInsertId();
      } else if ($request->input('accion') == 2) {
        DB::table('rh_empleado')
          ->where('idEmpleado', $request->input('id'))
          ->update(['estado' => 1]);
        DB::insert('insert into rh_empleado_baja (idEmpleado, comentario, idUsuario, fecha, hora, tipo, boletinado, recontratable) values (?, ?, ?, ?, ?, ?, ?, ?)', [$request->input('id'), $request->input('comentario'), $idUsuario, date("Y-m-d"), date("H:i:s"), $request->input('tipo'), $boletinado, $recontratable]);
        $lid = DB::getPdo()->lastInsertId();
      } else if ($request->input('accion') == 3) {
        DB::table('rh_empleado_baja')
          ->where('idEmpleado', $request->input('id'))
          ->where('idBaja', $request->input('idBaja'))
          ->update([
            'comentario' => $request->input('comentario'),
            'boletinado' => $boletinado,
            'recontratable' => $recontratable,
          ]);
        DB::table('rh_vacante_log')
          ->insert([
            'idUsuario' => $idUsuario,
            'idEmpleado' => $idEmpleado,
            'idAccion' => 15,
            'fechaCrea' => date('Y-m-d'),
            'horaCrea' => date('H:i:s'),
            'valorAnterior' => $request->input('comentario'),
          ]);
      } else {
        return "{ \"success\": false }";
      }


      return "{ \"success\": true }";
    } else {
      return "{ \"success\": false }";
    }
  }

  public function getEmployeeDetail($id = null, Request $request = null)
  {

    if ($id == null) {
      $id = $request->input('id');
    }

    $RHRole = 1;
    //$sucursales = session('sucursales');

    $empleado = DB::select("SELECT rh_e.idEmpleado, CONCAT(rh_e.nombre, ' ',rh_e.apellido_pat, ' ',rh_e.apellido_mat) as nombre, rh_e_e.nombre estado, rh_o.id AS idSucursal, rh_o.nombre sucursal, rh_p.idPuesto, rh_p.nombre puesto, rh_e.fechaCrea fecha, rh_e.fechaNacimiento, rh_e.fechaIngreso, rh_e.rfc, rh_e.nss, rh_e.curp, rh_e.correo FROM rh_empleado AS rh_e INNER JOIN rh_empleado_estado AS rh_e_e ON rh_e.estado = rh_e_e.idEstado LEFT JOIN sucursales AS rh_o ON rh_o.id = rh_e.idSucursal LEFT JOIN rh_puesto AS rh_p ON rh_e.idPuesto = rh_p.idPuesto WHERE  rh_e.idEmpleado = '" . $id . "';");
    $partidas = DB::select("SELECT * FROM (	SELECT rh_v_s.idSolicitud, rh_p.nombre puesto, rh_o.nombre sucursal, CONCAT(rh_v_t.tipo, IF(rh_o_t.id IS NULL,'', CONCAT(' a ',rh_o_t.nombre ))) solicitud, rh_v_s.fechaCrea fecha,rh_v_s.horaCrea hora, rh_u.name usuario FROM rh_vacante_solicitud AS rh_v_s INNER JOIN rh_vacante_solicitud_partida AS rh_v_p ON rh_v_s.idSolicitud = rh_v_p.idSolicitud INNER JOIN sucursales AS rh_o ON rh_o.id = rh_v_p.idSucursal LEFT JOIN rh_puesto AS rh_p ON rh_v_p.idPuesto = rh_p.idPuesto INNER JOIN rh_vacante_tipo AS rh_v_t ON rh_v_p.solicitud = rh_v_t.idTipo INNER JOIN users rh_u ON rh_v_s.idUsuario = rh_u.id LEFT JOIN sucursales AS rh_o_t ON rh_v_p.idSucursalTrans = rh_o_t.id WHERE rh_v_p.idEmpleado =" . $id . " UNION ALL SELECT rh_v_s.idSolicitud, rh_p.nombre puesto, rh_o.nombre sucursal, 'Solicita contratacion' solicitud, rh_v_s.fechaCrea fecha, rh_v_s.horaCrea hora, rh_u.name usuario FROM rh_vacante_solicitud AS rh_v_s INNER JOIN rh_vacante_solicitud_partida AS rh_v_p ON rh_v_s.idSolicitud = rh_v_p.idSolicitud INNER JOIN sucursales AS rh_o ON rh_o.id = rh_v_p.idSucursal INNER JOIN rh_puesto AS rh_p ON rh_v_p.idPuesto = rh_p.idPuesto INNER JOIN rh_vacante_tipo AS rh_v_t ON rh_v_p.solicitud = rh_v_t.idTipo INNER JOIN users rh_u ON rh_v_s.idUsuario = rh_u.id WHERE rh_v_p.idContratado = " . $id . " UNION ALL SELECT idBaja, comentario puesto, '' sucursal, 'Baja Confirmada' solicitud, fecha, hora, rh_u.name usuario  FROM rh_empleado_baja rh_b INNER JOIN users rh_u ON rh_b.idUsuario = rh_u.id WHERE rh_b.idEmpleado = " . $id . " UNION ALL SELECT idEmpLog, valorAnterior puesto, '' sucursal, accion solicitud, fechaCrea fecha, horaCrea hora, rh_u.name usuario  FROM rh_vacante_log rh_l INNER JOIN rh_vacante_accion rh_a on rh_l.idAccion=rh_a.idAccion INNER JOIN users rh_u ON rh_l.idUsuario = rh_u.id WHERE rh_l.idEmpleado =" . $id . ") data ORDER BY fecha, hora");
    $puestos = DB::select("SELECT * FROM rh_puesto");
    $sucursales = DB::select("SELECT id AS idSucursal, nombre FROM sucursales WHERE estado = 1 ORDER BY nombre;");

    if ($request != null) {
      return response()->json([
        'success' => true,
        'empleado' => $empleado[0],
        'partidas' => $partidas,
        'sucursales' => $sucursales,
        'puestos' => $puestos,
      ]);
    } else {
      return (object)[
        'success' => true,
        'empleado' => $empleado,
        'partidas' => $partidas,
        'sucursales' => $sucursales,
        'puestos' => $puestos,
      ];
    }
  }

  public function actualizaEmpleado(Request $request)
  {
    if (!empty($request->input('idEmpleado'))) {
      DB::enableQueryLog();
      $user = Auth::user();
      $idUsuario = $user->id;
      $valor = $request->input('valor');
      $idEmpleado = $request->input('idEmpleado');
      $accion = $request->input('tipoVac');
      $idNuvPuesto = $request->input('idPuesto') ?? 0;
      $salario = $request->input('salario');
      $idSucursal = 0;

      $empleado = DB::select('select rh_empleado.*, sucursales.nombre sucursal, rh_puesto.nombre puesto, rh_puesto.idPuesto,sucursales.id idSucursal from rh_empleado LEFT JOIN sucursales ON rh_empleado.idSucursal = sucursales.id LEFT JOIN rh_puesto ON rh_puesto.idPuesto = rh_empleado.idPuesto WHERE idEmpleado = ?', [$request->input('idEmpleado')]);

      switch ($request->input('dato')) {
        case 1:
          $campo = "nombre";
          $valorAnterior = $empleado[0]->nombre;
          break;
        case 2:
          $campo = "idPuesto";
          $valorAnterior = $empleado[0]->puesto;
          $idNuvPuesto = $valor;
          break;
        case 3:
          $campo = "idSucursal";
          $valorAnterior = $empleado[0]->sucursal;
          $idSucursal = $valor;
          break;
        case 5:
          $campo = "fechaNacimiento";
          $valorAnterior = $empleado[0]->fechaNacimiento;
          break;
        case 6:
          $campo = "fechaIngreso";
          $valorAnterior = $empleado[0]->fechaIngreso;
          break;
      }




      DB::insert('insert into rh_vacante_log (idEmpleado, valorAnterior, idUsuario, fechaCrea, horaCrea, idAccion) values (?, ?, ?, ?, ?, ?)', [$request->input('idEmpleado'), $valorAnterior, $idUsuario, date("Y-m-d"), date("H:i:s"), $request->input('dato')]);

      if (!empty($idNuvPuesto) && empty($idSucursal)) {
        DB::table('rh_vacante_log')
          ->insert([
            'idUsuario' => $idUsuario,
            'idEmpleado' => $idEmpleado,
            'idAccion' => 2,
            'fechaCrea' => date('Y-m-d'),
            'horaCrea' => date('H:i:s'),
            'valorAnterior' => $empleado[0]->puesto
          ]);
      }

      if (!empty($salario)) {
        DB::table('rh_empleado')
          ->where('idEmpleado', $idEmpleado)
          ->update(['salario100' => $salario]);

        DB::table('rh_vacante_log')
          ->insert([
            'idUsuario' => $idUsuario,
            'idEmpleado' => $idEmpleado,
            'idAccion' => 11,
            'fechaCrea' => date('Y-m-d'),
            'horaCrea' => date('H:i:s'),
            'valorAnterior' => $empleado[0]->salario100
          ]);
      }

      $idDepartamento = 0;

      $sql = "SELECT A.idSucursal, A.idPuesto, B.idArea FROM rh_empleado AS A INNER JOIN rh_puesto_area AS B ON A.idPuesto = B.idPuesto INNER JOIN rh_area AS C ON B.idArea = C.id WHERE idEmpleado = ?";
      $datos = DB::select($sql, [$idEmpleado]);

      if (!empty($accion) && $accion != 11) {

        $soli = DB::table('rh_vacante_solicitud_partida')
          ->where('idPuesto', $idNuvPuesto != 0 ? $idNuvPuesto : $empleado[0]->idPuesto)
          ->where('idSucursal', $idSucursal != 0 ? $idSucursal : $empleado[0]->idSucursal)
          ->where('estado', 1)
          ->limit(1)
          ->get();

        if ($accion != 10) {
          $lid = $this->saveRequest(null, [$empleado[0]->idSucursal, $empleado[0]->idSucursal], [$idSucursal, 0], [$datos[0]->idArea, $datos[0]->idArea], [$idDepartamento, $idDepartamento], [$accion, 1], [$idEmpleado, 0], [$idNuvPuesto, 0], [$empleado[0]->idPuesto, $empleado[0]->idPuesto], [6, 1]);
        } else {
          $lid = $this->saveRequest(null, [$empleado[0]->idSucursal], [$idSucursal], [$datos[0]->idArea], [$idDepartamento], [$accion], [$idEmpleado], [$idNuvPuesto], [$empleado[0]->idPuesto], [6]);
        }
        if ($accion == 5) {
          DB::table('rh_empleado')
            ->where('idEmpleado', $idEmpleado)
            ->update(['idPuesto' => $idNuvPuesto]);
        }

        if (!empty($soli[0])) {
          DB::table('rh_vacante_solicitud_partida')
            ->where('idPartida', $soli[0]->idPartida)
            ->update(['estado' => 6, 'idSolicitudCierra' => $lid]);
        }
      }
      DB::update('update rh_empleado set ' . $campo . ' = ? where idEmpleado = ?', [$valor, $request->input('idEmpleado')]);

      // return response()->json([
      // 	'success' => true,
      // 	"$campo" => "$valor"
      // ]);
      return "{ \"success\": true, \"$campo\": \"$valor\" }";
    } else {
      return "{ \"success\": false }";
    }
  }

  public function verificarPuestos(Request $request)
  {
    $idPuesto = $request->input('idPuesto');
    $idSucursal = $request->input('idSucursal');

    $sql = "SELECT A.cantidad as auth, COUNT(B.idEmpleado) as ocupadas FROM rh_plazas_autorizadas AS A LEFT JOIN rh_empleado AS B ON (A.idPuesto = B.idPuesto AND B.idSucursal = ?) WHERE A.idSucursal= ? AND A.idPuesto = ? GROUP BY A.cantidad";
    $plazas = DB::select($sql, [$idSucursal, $idSucursal, $idPuesto]);
    if (!empty($plazas)) {
      $libres = $plazas[0]->auth - $plazas[0]->ocupadas;
    } else {
      $libres = 0;
    }

    if ($libres > 0) {
      return response()->json([
        'success' => true,
        'msg' => "Se tienen $libres vacantes libres"
      ]);
    } else {
      return response()->json([
        'success' => false,
        'msg' => "No se tienen vacantes disponibles"
      ]);
    }
  }

  public function editarPlantilla($id)
  {
    $sql = "SELECT * FROM sucursales WHERE id = ?";
    $sucursales = DB::select($sql, [$id]);
    $sql = "SELECT aut.idPlaza,aut.idPuesto, puesto.nombre puesto, puesto.orden, aut.cantidad, COUNT(emp.idPuesto) AS Activos FROM rh_plazas_autorizadas aut 
				INNER JOIN rh_puesto puesto ON aut.idPuesto = puesto.idPuesto 
				LEFT JOIN rh_empleado emp ON (puesto.idPuesto = emp.idPuesto AND aut.idSucursal = emp.idSucursal AND emp.estado = 1)
				WHERE aut.idSucursal = ? AND aut.estado = 1
				GROUP BY aut.idPuesto, puesto.nombre, puesto.orden, aut.cantidad, aut.idPlaza
				ORDER BY puesto.orden, puesto.nombre;";
    $puestosActivos = DB::select($sql, [$id]);
    $sql = "SELECT * FROM rh_puesto WHERE estado = 1";
    $puestos = DB::select($sql);
    return view('vacantes.editPlantilla', ['role' => session('RHRole'), 'sucursal' => $sucursales[0], 'puestosActivos' => $puestosActivos, 'puestos' => $puestos]);
  }

  public function actualizarPlantilla(Request $request)
  {
    $idSucursal = $request->input('idSucursal');
    $puestosIds = $request->input('puestosIds');
    $cantidad = $request->input('cantidad');
    $puestos = $request->input('puestos');

    if (!empty($idSucursal) && !empty($puestos) && !empty($cantidad)) {

      foreach ($puestosIds as $key => $puesto) {
        $sql = "INSERT INTO rh_plazas_autorizadas (idPlaza,idSucursal, idPuesto,cantidad) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE cantidad = ?";
        DB::insert($sql, [$puesto, $idSucursal, $puestos[$key], $cantidad[$key], $cantidad[$key]]);
      }

      return response()->json([
        'success' => true,
        'msg' => 'La plantilla se actualizo!',
      ]);
    } else {
      return response()->json([
        'success' => false,
        'msg' => 'Faltan datos',
      ]);
    }
  }
  public function actualizarPlantillaTabla(Request $request)
  {
    $idSucursal = $request->input('idSucursal');

    $sql = "SELECT aut.idPlaza,aut.idPuesto, puesto.nombre puesto, puesto.orden, aut.cantidad, COUNT(emp.idPuesto) AS Activos FROM rh_plazas_autorizadas aut 
				INNER JOIN rh_puesto puesto ON aut.idPuesto = puesto.idPuesto 
				LEFT JOIN rh_empleado emp ON (puesto.idPuesto = emp.idPuesto AND aut.idSucursal = emp.idSucursal AND emp.estado = 1)
				WHERE aut.idSucursal = ?
				GROUP BY aut.idPuesto, puesto.nombre, puesto.orden, aut.cantidad, aut.idPlaza
				ORDER BY puesto.orden, puesto.nombre;";
    $puestosActivos = DB::select($sql, [$idSucursal]);
    return response()->json([
      'success' => true,
      'data' => $puestosActivos,
    ]);
  }

  public function agregarPuestoSuc(Request $request)
  {
    $idSucursal = $request->input('idSucursal');
    $idPuesto = $request->input('idPuesto');
    $cantidad = $request->input('cantidad');

    if (!empty($idSucursal) && !empty($idPuesto) && !empty($cantidad)) {

      $sql = "SELECT * FROM rh_plazas_autorizadas WHERE idSucursal = ? AND idPuesto = ?";
      $select = DB::select($sql, [$idSucursal, $idPuesto]);


      if (empty($select)) {

        $sql = "INSERT INTO rh_plazas_autorizadas (idSucursal, idPuesto, cantidad) VALUES (?,?,?)";
        $insert = DB::insert($sql, [$idSucursal, $idPuesto, $cantidad]);
        $lid = DB::getPdo()->lastInsertId();

        if ($insert) {
          return response()->json([
            'success' => true,
            'msg' => 'Se añadio la nueva plaza!',
            'id' => $lid,
          ]);
        } else {
          return response()->json([
            'success' => false,
            'msg' => 'No se pudo añadir la nueva plaza'
          ]);
        }
      } else if ($select[0]->estado == 0) {
        $sql = "UPDATE rh_plazas_autorizadas SET cantidad = ?, estado = 1 WHERE idPlaza = ?";
        DB::update($sql, [$cantidad, $select[0]->idPlaza]);
        return response()->json([
          'success' => true,
          'msg' => 'Se añadio la nueva plaza!',
          'id' => $select[0]->idPlaza,
        ]);
      } else {
        return response()->json([
          'success' => false,
          'msg' => 'La plaza ya existe'
        ]);
      }
    } else {
      return response()->json([
        'success' => false,
        'msg' => 'Es obligatorio llenar todos los campos'
      ]);
    }
  }

  public function borrarPuestoSuc(Request $request)
  {
    $idPlaza = $request->input('idPlaza');

    if (!empty($idPlaza)) {
      $sql = "UPDATE rh_plazas_autorizadas SET cantidad = 0 , estado = 0 WHERE idPlaza = ?";
      $delete = DB::update($sql, [$idPlaza]);

      if ($delete) {
        return response()->json([
          'success' => true,
          'msg' => 'Se elimino correctamente!'
        ]);
      } else {
        return response()->json([
          'success' => false,
          'msg' => 'No se encontro el registro'
        ]);
      }
    } else {
      return response()->json([
        'success' => false,
        'msg' => 'Faltan datos'
      ]);
    }
  }

  public function downloadPlantilla($idSucursal = 0)
  {
    if ($idSucursal) {
      $sql = "SELECT * FROM sucursales WHERE id = ?";
      $sucursal = DB::select($sql, [$idSucursal]);
      /*
			$sql = "SELECT idPuesto, cantidad FROM rh_plazas_autorizadas WHERE idSucursal = ? AND cantidad >0";
			$plazas = DB::select($sql,[$idSucursal]);
			$puestos = array();				
			foreach($plazas AS $plaza)
			{
				$puestos[$plaza->idPuesto] = $plaza->cantidad;
			}
			
			$sql = "SELECT rh_empleado.idPuesto,rh_empleado.nombre, rh_puesto.nombre puesto FROM rh_empleado INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_empleado.idPuesto WHERE rh_empleado.idSucursal = ".$idSucursal." AND rh_empleado.estado =1 ORDER BY rh_puesto.orden ASC;";
			$detalle = DB::select($sql);
*/
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->mergeCells('A1:D1');
      $sheet->mergeCells('A2:D2');

      $sheet->getColumnDimension('A')->setWidth(6);
      $sheet->getColumnDimension('B')->setWidth(4);
      $sheet->getColumnDimension('C')->setWidth(25);
      $sheet->getColumnDimension('E')->setWidth(25);
      $sheet->getColumnDimension('D')->setWidth(40);

      $sheet->setCellValue('A1', 'ERIC KAYSER MEXICO SAPI DE CV ');
      $sheet->setCellValue('A2', $sucursal[0]->nombre);
      $sheet->setCellValue('E1', 'PLANTILLA AUTORIZADA');
      $sheet->setCellValue('E2', 'PLANTILLA ACTUAL');

      $sheet->setCellValue('A5', "# AUT");
      $sheet->setCellValue('B5', "#");
      $sheet->setCellValue('C5', "PUESTO");
      $sheet->setCellValue('D5', "NOMBRE");
      $totEmp = 0;
      $totPuestos = 0;
      $i = 6;
      //$puesto = "";

      $sql = "SELECT aut.idPuesto, puesto.nombre puesto, puesto.orden, aut.cantidad FROM rh_plazas_autorizadas aut INNER JOIN rh_puesto puesto ON aut.idPuesto = puesto.idPuesto WHERE cantidad > 0 AND idSucursal = $idSucursal ORDER BY puesto.orden, puesto.nombre;";
      $autorizados = DB::select($sql);


      foreach ($autorizados as $autorizado) {
        $totPuestos += $autorizado->cantidad;
        $sql = "SELECT rh_empleado.nombre, rh_puesto.nombre puesto, 0 excedente FROM rh_empleado INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_empleado.idPuesto WHERE rh_empleado.idPuesto = " . $autorizado->idPuesto . " AND rh_empleado.idSucursal = $idSucursal AND rh_empleado.estado =1 ORDER BY rh_puesto.orden ASC;";
        $empleados = DB::select($sql);
        $puesto = 0;

        $sheet->setCellValue('A' . $i, $autorizado->cantidad);

        foreach ($empleados as $empleado) {
          $puesto++;
          $i++;
          $totEmp++;

          $sheet->setCellValue('B' . $i, $puesto);
          $sheet->setCellValue('C' . $i, $empleado->puesto);
          $sheet->setCellValue('D' . $i, $empleado->nombre);

          if ($autorizado->cantidad < $puesto) {
            $sheet->setCellValue('E' . $i, "Excedente");
          }
        }

        $sql  = "SELECT CONCAT('Solicitud #',idSolicitud) nombre, rh_puesto.nombre puesto, 0 excedente FROM rh_vacante_solicitud_partida INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_vacante_solicitud_partida.idPuesto  WHERE rh_vacante_solicitud_partida.estado IN (1,2,3,4,9) AND rh_vacante_solicitud_partida.idPuesto = " . $autorizado->idPuesto . " AND rh_vacante_solicitud_partida.idSucursal = $idSucursal ;";
        $solicitudes = DB::select($sql);
        foreach ($solicitudes as $solicitud) {
          $puesto++;
          $i++;
          if ($autorizado->cantidad < $puesto) {
            $sheet->setCellValue('E' . $i, "Excedente");
          }

          $sheet->setCellValue('B' . $i, $puesto);
          $sheet->setCellValue('C' . $i, $solicitud->puesto);
          $sheet->setCellValue('D' . $i, $solicitud->nombre);
        }

        if ($autorizado->cantidad > $puesto) {
          for (; $puesto < $autorizado->cantidad; $puesto++) {
            $i++;
            $sheet->setCellValue('A' . $i, $autorizado->cantidad);
            $sheet->setCellValue('B' . $i, 1);
            $sheet->setCellValue('C' . $i, $autorizado->puesto);
            $sheet->setCellValue('D' . $i, "Disponible " . ($puesto + 1));
          }
        }
      }

      $sheet->setCellValue('F2', $totEmp);
      $sheet->setCellValue('F1', $totPuestos);

      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="plantilla_' . $sucursal[0]->idSap . '_' . date("Ymd") . '.xlsx"');
      $writer->save("php://output");
    }

    //return $idSucursal;
  }



}
