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
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    $strValSucursales = "";
    if ($RHRole != 1)
    if (!empty($sucursales))
    $strValSucursales = " IN (" . $sucursales . ") ";
    else 
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
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    // $sucursales = session('sucursales');
    // $user = Auth::user();
    // $idEmpresa = $user->idEmpresa;
    $idEmpresa = 1;
    $strValSucursales = "";
    if ($RHRole != 1)
    if (!empty($sucursales))
    $strValSucursales = "AND idSucursal IN (" . $sucursales . ") ";
    else
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

  public function showRequests($tipo = 1)
  {
    $tipos = array("Abiertas", "En Tiempo", "Atrasadas");
    return view('vacantes.showRequests', ["tipo" => $tipo, "titulo" => "Solicitudes " . $tipos[$tipo - 1], 'role' => session('RHRole')]);
  }

  public function exportRequest(Request $request)
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Solicitud');
    $sheet->setCellValue('B1', 'Fecha');
    $sheet->setCellValue('C1', 'Sucursal');
    $sheet->setCellValue('D1', 'Solicita');
    $sheet->setCellValue('E1', 'Puesto');
    $sheet->setCellValue('F1', 'Solicitud');
    $sheet->setCellValue('G1', 'Reclutador');
    $sheet->setCellValue('H1', 'Estado');
    $sheet->setCellValue('I1', 'Referencia');
    $sheet->setCellValue('J1', 'Comentario');

    $idUsuario = Auth::id();
    $RHRole = session('RHRole');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    //$sucursales = session('sucursales');
    $strValSucursales = "";

    if ($RHRole != 1)
      if (!empty($sucursales))
        $strValSucursales = " IN (" . $sucursales . ") ";
      else
        $strValSucursales = " IN (0) ";

    $puesto = !empty($request->input('findPuesto')) ? $request->input('findPuesto') : "";
    $sucursal = !empty($request->input('findSucursal')) ? $request->input('findSucursal') : "";
    $tipo = !empty($request->input('tipo')) ? $request->input('tipo') : 1;

    $strBusca =  "";
    $strAtraso = "";

    $busca = array();

    if (!empty($sucursal))
      $busca[] = " rh_o.nombre LIKE '%" . $sucursal . "%' ";
    if (!empty($puesto))
      $busca[] = " rh_p.nombre LIKE '%" . $puesto . "%' ";

    if (!empty($busca)) {
      $strBusca = " AND " . implode(" AND ", $busca);
    }

    if (!empty($tipo) && $tipo != 1) {
      $strAtraso .= " atraso = " . ($tipo == 2 ? 1 : 0);
    }

    $items =    DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, IF(rh_vsp.solicitud =1,'Cubrir Vacante','Reemplazo') AS solicitud , rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, estado.estado, rh_rec.nombre AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  LEFT JOIN rh_reclutador_sucursal rh_recsuc ON rh_recsuc.idSucursal = rh_vsp.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN rh_reclutador rh_rec ON rh_rec.idReclutador = rh_recsuc.idReclutador WHERE NOT(rh_vsp.estado IN(4,5) ) " . ($RHRole != 1 ? " AND rh_vsp.idSucursal " . $strValSucursales : "") . " " . $strBusca . ") AS datos " . (empty($strAtraso) ? "" : " WHERE $strAtraso") . ";");

    $row = 2;
    foreach ($items as $item) {
      $sheet->setCellValue('A' . $row, $item->idSolicitud);
      $sheet->setCellValue('B' . $row, $item->fechaCrea);
      $sheet->setCellValue('C' . $row, $item->sucursal);
      $sheet->setCellValue('D' . $row, "");
      $sheet->setCellValue('E' . $row, $item->puesto);
      $sheet->setCellValue('F' . $row, $item->solicitud);
      $sheet->setCellValue('G' . $row, $item->reclutador);
      $sheet->setCellValue('H' . $row, $item->estado);
      $sheet->setCellValue('I' . $row, "");
      $sheet->setCellValue('J' . $row, "");
      $row++;
    }

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="vacantes_' . date("Ymd") . '.xlsx"');
    $writer->save("php://output");
  }

  public function getRequest(Request $request)
  {
    $idUsuario = Auth::id();
    $RHRole = session('RHRole');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    //$sucursales = session('sucursales');
    $strValSucursales = "";
    $strStatus = "";
    if ($RHRole != 1)
      if (!empty($sucursales))
        $strValSucursales = " IN (" . $sucursales . ") ";
      else
        $strValSucursales = " IN (0) ";

    $draw = !empty($request->input('draw')) ? $request->input('draw') : 1;
    $start = !empty($request->input('start')) ? $request->input('start') : 0;
    $length = !empty($request->input('length')) ? $request->input('length') : 10;
    $queryarr = !empty($request->input('search')) ? $request->input('search') : array();
    $query = !empty($queryarr["value"]) ? $queryarr["value"] : "";
    $allitems = null;
    $strBusca =  "";
    $strAtraso = "";

    if (!empty($request->input('columns'))) {
      $cols = $request->input('columns');
      $busca = array();
      foreach ($cols as $col) {
        if (!empty($col["search"]["value"])) {
          switch ($col["data"]) {
            case "puesto":
              $busca[] = " rh_p.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
            case "sucursal":
              $busca[] = " rh_o.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
          }
        }
      }
      if (!empty($busca)) {
        $strBusca = " AND " . implode(" AND ", $busca);
      }
    }

    if (!empty($request->input('tipo')) && ($request->input('tipo') == 2 || $request->input('tipo') == 3)) {
      $strAtraso .= " atraso = " . ($request->input('tipo') == 2 ? 1 : 0);
    } else if (!empty($request->input('tipo')) && $request->input('tipo') == 4) {
      $strStatus = "4";
    } else if (!empty($request->input('tipo')) && $request->input('tipo') == 5) {
      $strStatus = "9";
    }

    $orden = " datos.fechaCrea DESC ";
    if (!empty($request->input('order'))) {
      $ordena = $request->input('order');
      $orden = "";
      switch ($ordena[0]["column"]) {
        case 0:
          $orden = " datos.idSolicitud " . $ordena[0]["dir"];
          break;
        case 1:
          $orden = " datos.fechaCrea " . $ordena[0]["dir"];
          break;
        case 2:
          $orden = " datos.sucursal " . $ordena[0]["dir"];
          break;
        case 3:
          $orden = " datos.puesto  " . $ordena[0]["dir"];
          break;
        case 4:
          $orden = " datos.solicitud " . $ordena[0]["dir"];
          break;
        case 5:
          $orden = " datos.reclutador  " . $ordena[0]["dir"];
          break;
        case 6:
          $orden = " datos.estado  " . $ordena[0]["dir"];
          break;
        default:
          $orden = " datos.fechaCrea DESC ";
          break;
      }
    }

    //TODO: Checar que pasa cuando no existe tiempo de contratacion para algun puesto, podemos poner default 8 dias  atodos los peustos nuevos
    $allitems = DB::select("SELECT idSolicitud FROM ( SELECT rh_vsp.idSolicitud FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  INNER JOIN rh_vacante_tipo ON rh_vsp.solicitud = rh_vacante_tipo.idTipo LEFT JOIN rh_sucursal_usuario rh_recsuc ON rh_vsp.idSucursal = rh_recsuc.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN users rh_rec ON rh_rec.id = rh_recsuc.idUsuario WHERE " . (empty($strStatus) ? "NOT(rh_vsp.estado IN(4,5,6,9,8) )" : " rh_vsp.estado = " . $strStatus) . " " . (($RHRole != 1  && $RHRole != 4) ? " AND rh_vsp.idSucursal " . $strValSucursales : "") . " " . $strBusca . ") AS datos " . (empty($strAtraso) ? "" : " WHERE $strAtraso") . " GROUP BY idSolicitud ;");

    //$items = DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, IF(rh_vsp.solicitud =1,'Cubrir Vacante','Reemplazo') AS solicitud , rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, estado.estado, rh_rec.nombre AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN rh_oficinas AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  LEFT JOIN rh_reclutador_sucursal rh_recsuc ON rh_recsuc.idSucursal = rh_vsp.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN rh_reclutador rh_rec ON rh_rec.idReclutador = rh_recsuc.idReclutador WHERE ". (empty($strStatus)?"NOT(rh_vsp.estado IN(4,5,6,9,8) )": " rh_vsp.estado = ".$strStatus)." ".(($RHRole != 1  && $RHRole !=4) ?" AND rh_vsp.idSucursal ".$strValSucursales:"")." ".$strBusca.") AS datos ".(empty($strAtraso)?"":" WHERE $strAtraso")."  ORDER BY $orden LIMIT ".$start.", ".$length.";");
    $sql = "SELECT * FROM ( SELECT rh_vsp.idSolicitud FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  INNER JOIN rh_vacante_tipo ON rh_vsp.solicitud = rh_vacante_tipo.idTipo LEFT JOIN rh_sucursal_usuario rh_recsuc ON rh_vsp.idSucursal = rh_recsuc.idSucursal INNER JOIN config_app_access ON (rh_recsuc.idUsuario = config_app_access.idUsuario AND  config_app_access.idRole IN (1,2,3,4) AND config_app_access.idAplicacion = 3) INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN users rh_rec ON rh_rec.id = rh_recsuc.idUsuario WHERE " . (empty($strStatus) ? "NOT(rh_vsp.estado IN(4,5,6,9,8) )" : " rh_vsp.estado = " . $strStatus) . " " . (($RHRole != 1  && $RHRole != 4) ? " AND rh_vsp.idSucursal " . $strValSucursales : "") . " " . $strBusca . ") AS datos " . (empty($strAtraso) ? "" : " WHERE $strAtraso") . " ;";
    $items = DB::select("SELECT idSolicitud,  solicitud, sucursal, fechaCrea, limite, lastUpdateDate, atraso, puesto, comentario, estado, GROUP_CONCAT(reclutador) reclutador FROM ( SELECT rh_vsp.idSolicitud, rh_vacante_tipo.tipo AS solicitud , rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, estado.estado, rh_rec.name AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  INNER JOIN rh_vacante_tipo ON rh_vsp.solicitud = rh_vacante_tipo.idTipo LEFT JOIN rh_sucursal_usuario rh_recsuc ON rh_vsp.idSucursal = rh_recsuc.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN users rh_rec ON rh_rec.id = rh_recsuc.idUsuario WHERE " . (empty($strStatus) ? "NOT(rh_vsp.estado IN(4,5,6,9,8) )" : " rh_vsp.estado = " . $strStatus) . " " . (($RHRole != 1  && $RHRole != 4) ? " AND rh_vsp.idSucursal " . $strValSucursales : "") . " " . $strBusca . ") AS datos " . (empty($strAtraso) ? "" : " WHERE $strAtraso") . " GROUP BY idSolicitud,  solicitud, sucursal,fechaCrea, limite, lastUpdateDate, atraso, puesto, comentario, estado ORDER BY $orden LIMIT " . $start . ", " . $length . ";");

    return  response()->json([
      'draw' => $draw,
      'recordsTotal' => count($allitems),
      'recordsFiltered' => count($allitems),
      'data' => $items,
      'sql' => $sql
    ]);
  }

  public function requestDetail($id)
  {
    $RHRole = 1;
    //$RHRole = session('RHRole');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    $strValSucursales = "";
    if ($RHRole != 1)
      if (!empty($sucursales))
        $strValSucursales = " IN (" . $sucursales . ") ";
      else
        $strValSucursales = " IN (0) ";

    $solicitud = DB::select("SELECT rh_vacante_solicitud.idSolicitud, users.name nombre, rh_vacante_solicitud.fechaCrea AS fecha, rh_vacante_solicitud.estado, rh_vacante_solicitud.comentario FROM rh_vacante_solicitud INNER JOIN users ON users.id = rh_vacante_solicitud.idUsuario WHERE rh_vacante_solicitud.idSolicitud = '" . $id . "';");
    $partidas = DB::select("SELECT tipo.tipo solicitud, estado.estado, estado.idEstado, rh_vsp.idSolicitud, rh_vsp.idPartida, rh_vs.fechaCrea, rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, CONCAT(rh_empleado.nombre, ' ',rh_empleado.apellido_pat, ' ',rh_empleado.apellido_mat) as nombre , CONCAT(contratado.nombre, ' ', contratado.apellido_pat, ' ', contratado.apellido_mat) as contratado FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_tipo tipo ON tipo.idTipo = rh_vsp.solicitud INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado LEFT JOIN rh_empleado ON rh_empleado.idEmpleado = rh_vsp.idEmpleado  LEFT JOIN rh_empleado as contratado ON contratado.idEmpleado = rh_vsp.idContratado WHERE rh_vsp.idSolicitud = '" . $id . "' " . (($RHRole != 1 && $RHRole != 4) ? " AND rh_vsp.idSucursal " . $strValSucursales : "") . ";");

    return view('vacantes.detalle', ['solicitud' => $solicitud[0], 'partidas' => $partidas, 'role' => session('RHRole')]);
  }

  public function showRetrasadas()
  {
    //$RHRole = session('RHRole');
    $RHRole = 1;
    //$sucursales = session('sucursales');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    $tipo = 2;
    $tipos = array("Abiertas", "En Tiempo", "Atrasadas");
    return view('vacantes.showRequests', ["tipo" => $tipo, 'role' => $RHRole, 'sucursales' => $sucursales, "titulo" => "Solicitudes " . $tipos[$tipo - 1]]);
  }

  public function showEnTiempo()
  {
    $RHRole = session('RHRole');
    $sucursales = session('sucursales');
    $tipo = 3;
    $tipos = array("Abiertas", "En Tiempo", "Atrasadas");
    return view('vacantes.showRequests', ["tipo" => $tipo, 'role' => session('RHRole'), 'sucursales' => $sucursales, "titulo" => "Solicitudes " . $tipos[$tipo - 1]]);
  }

  public function showNewRequestForm()
  {
    //$RHRole = session('RHRole');
    $RHRole = 1;
    //$sucursales = session('sucursales');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    $strValSucursales = "";
    if ($RHRole != 1)
      if (!empty($sucursales))
        $strValSucursales = " IN (" . $sucursales . ") ";
      else
        $strValSucursales = " IN (0) ";

    $sucursales = DB::select("SELECT id AS idSucursal, nombre FROM sucursales WHERE estado = 1 " . ($RHRole != 1 ? "AND id " . $strValSucursales : "") . " ORDER BY nombre;");
    $trsucursales = DB::select("SELECT id AS idSucursal, nombre FROM sucursales WHERE estado = 1 ORDER BY nombre;");
    return view('vacantes.newRequest', ['sucursales' => $sucursales, 'trsucursales' => $trsucursales, 'role' => session('RHRole')]);
  }

  public function getEmployees(Request $request)
  {
    //$idUsuario = Auth::id();
    //$RHRole = session('RHRole');
    $RHRole = 1;
    //$sucursales = session('sucursales');
    $sucursales = DB::select("SELECT s.id FROM sucursales AS s;");
    $strValSucursales = "";
    $strStatus = "";
    if ($RHRole != 1)
      if (!empty($sucursales))
        $strValSucursales = " IN (" . $sucursales . ") ";
      else
        $strValSucursales = " IN (0) ";

    $draw = !empty($request->input('draw')) ? $request->input('draw') : 1;
    $start = !empty($request->input('start')) ? $request->input('start') : 0;
    $length = !empty($request->input('length')) ? $request->input('length') : 10;
    $queryarr = !empty($request->input('search')) ? $request->input('search') : array();
    $query = !empty($queryarr["value"]) ? $queryarr["value"] : "";
    $allitems = null;
    $strBusca =  "";

    if (!empty($request->input('columns'))) {
      $cols = $request->input('columns');
      $busca = array();
      foreach ($cols as $col) {
        if (!empty($col["search"]["value"])) {
          switch ($col["data"]) {
            case "nombre":
              if (!empty($col["search"]["value"]))
                $busca[] = " CONCAT(rh_e.nombre, ' ',rh_e.apellido_pat, ' ',rh_e.apellido_mat) LIKE '%" . $col["search"]["value"] . "%' ";
              break;
            case "puesto":
              if (!empty($col["search"]["value"]))
                $busca[] = " rh_p.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
            case "sucursal":
              if (!empty($col["search"]["value"]))
                $busca[] = " rh_o.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
          }
        }
      }
      if (!empty($busca)) {
        $strBusca = " AND " . implode(" AND ", $busca);
      }
    }

    $orden = " datos.nombre DESC ";
    if (!empty($request->input('order'))) {
      $ordena = $request->input('order');
      $orden = "";
      switch ($ordena[0]["column"]) {
        case 0:
          $orden = " datos.idEmpleado " . $ordena[0]["dir"];
          break;
        case 1:
          $orden = " CONCAT(datos.nombre, ' ',datos.apellido_pat, ' ',datos.apellido_mat) " . $ordena[0]["dir"];
          break;
        case 2:
          $orden = " datos.sucursal " . $ordena[0]["dir"];
          break;
        case 3:
          $orden = " datos.puesto  " . $ordena[0]["dir"];
          break;
      }
    }

    //TODO: Checar que pasa cuando no existe tiempo de contratacion para algun puesto, podemos poner default 8 dias  atodos los peustos nuevos
    //$allitems = DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso  , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, rh_rec.nombre AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN rh_oficinas AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN rh_reclutador_sucursal rh_recsuc ON rh_recsuc.idSucursal = rh_vsp.idSucursal LEFT JOIN rh_reclutador rh_rec ON rh_rec.idReclutador = rh_recsuc.idReclutador WHERE ". (empty($strStatus)?"NOT(rh_vsp.estado IN(4,5,6,9,8) )": " rh_vsp.estado = ".$strStatus)." ".(($RHRole != 1 && $RHRole !=4 ) ?" AND rh_vsp.idSucursal ".$strValSucursales:"")." ".$strBusca.") AS datos ".(empty($strAtraso)?"":" WHERE $strAtraso")." ;");
    $allitems = DB::select("SELECT * FROM ( SELECT rh_e.idEmpleado FROM rh_empleado AS rh_e INNER JOIN rh_empleado_estado AS rh_e_e ON rh_e.estado = rh_e_e.idEstado INNER JOIN sucursales AS rh_o ON rh_o.id = rh_e.idSucursal LEFT JOIN rh_puesto AS rh_p ON rh_e.idPuesto = rh_p.idPuesto  " . (($RHRole != 1 && $RHRole != 4) ? " WHERE rh_e.idSucursal " . $strValSucursales : "") . " " . $strBusca . ") AS datos");
    //$sql = "SELECT * FROM ( SELECT rh_e.idEmpleado, CONCAT(rh_e.nombre, ' ',rh_e.apellido_pat, ' ',rh_e.apellido_mat) as nombre, rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_e_e.nombre as estado, rh_e.idPuesto FROM rh_empleado AS rh_e INNER JOIN rh_empleado_estado AS rh_e_e ON rh_e.estado = rh_e_e.idEstado INNER JOIN sucursales AS rh_o ON rh_o.id = rh_e.idSucursal LEFT JOIN rh_puesto AS rh_p ON rh_e.idPuesto = rh_p.idPuesto WHERE " . (($RHRole != 1 && $RHRole != 4) ? "  rh_e.idSucursal " . $strValSucursales : " 1=1 ") . " " . $strBusca . "GROUP BY rh_e.idPuesto,idEmpleado, rh_o.nombre, rh_p.nombre , rh_e_e.nombre, rh_e.nombre,rh_e.apellido_pat,rh_e.apellido_mat) AS datos ORDER BY $orden LIMIT " . $start . ", " . $length . ";";
    //dd($sql);
    $items = DB::select("SELECT * FROM ( SELECT rh_e.idEmpleado, CONCAT(rh_e.nombre, ' ',rh_e.apellido_pat, ' ',rh_e.apellido_mat) as nombre, rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_e_e.nombre as estado, rh_e.idPuesto FROM rh_empleado AS rh_e INNER JOIN rh_empleado_estado AS rh_e_e ON rh_e.estado = rh_e_e.idEstado LEFT JOIN sucursales AS rh_o ON rh_o.id = rh_e.idSucursal LEFT JOIN rh_puesto AS rh_p ON rh_e.idPuesto = rh_p.idPuesto WHERE " . (($RHRole != 1 && $RHRole != 4) ? " rh_e.idSucursal " . $strValSucursales : " 1=1 ") . " " . $strBusca . " GROUP BY rh_e.idPuesto,idEmpleado, rh_o.nombre, rh_p.nombre , rh_e_e.nombre, rh_e.nombre,rh_e.apellido_pat,rh_e.apellido_mat) AS datos ORDER BY $orden LIMIT " . $start . ", " . $length . ";");

    return  response()->json([
      'draw' => $draw,
      'recordsTotal' => count($allitems),
      'recordsFiltered' => count($allitems),
      'data' => $items
    ]);
  }

  public function getPuestosGrowup(Request $request)
  {
    $puestos = DB::select("SELECT rh_puesto.idPuesto id, rh_puesto.nombre FROM rh_plazas_autorizadas INNER JOIN rh_puesto ON rh_plazas_autorizadas.idPuesto = rh_puesto.idPuesto WHERE rh_puesto.estado = 1 AND  rh_plazas_autorizadas.idSucursal = ? ORDER BY rh_puesto.nombre ASC; ", [$request->input('sucursal')]);

    if (!empty($puestos[0])) {
      return  response()->json([
        'data' => $puestos
      ]);
    } else {
      return  response()->json([
        'tipo' => 0,
        'data' => []
      ]);
    }
  }

  public function getPuestosList(Request $request)
  {
    $puestos = DB::select("SELECT rh_puesto.idPuesto id, rh_puesto.nombre FROM rh_puesto_area INNER JOIN rh_puesto ON rh_puesto.idPuesto = rh_puesto_area.idPuesto WHERE estado = 1 AND idArea = " . $request->input('id')); // AND idArea = " . $request->input('id'));
    if (!empty($puestos[0])) {
      return  response()->json([
        'data' => $puestos
      ]);
    } else {
      return  response()->json([
        'tipo' => 0,
        'data' => []
      ]);
    }
  }

  public function getPuestos(Request $request)
  {
    $idSucursal = $request->input('idSucursal');

    if (!empty($idSucursal)) {
      $puestos = DB::select("SELECT aut.idPuesto, puesto.nombre puesto, puesto.orden, aut.cantidad, COUNT(B.idEmpleado) AS ocupado FROM rh_plazas_autorizadas aut 
				LEFT JOIN rh_puesto puesto ON aut.idPuesto = puesto.idPuesto
				LEFT JOIN rh_empleado AS B ON (puesto.idPuesto = B.idPuesto AND B.idSucursal = ? AND B.estado != 6)
				WHERE cantidad > 0 AND aut.idSucursal = ?
				GROUP BY aut.idPuesto, puesto.nombre, puesto.orden, aut.cantidad
				ORDER BY puesto.orden, puesto.nombre", [$idSucursal, $idSucursal]);

      $puestosArr = [];
      if (!empty($puestos)) {

        foreach ($puestos as $key => $value) {
          if ($value->cantidad > $value->ocupado) {
            $puestosArr[] = $value;
          }
        }
      }

      return response()->json([
        'success' => true,
        'data' => $puestosArr
      ]);
    }
  }

  public function validaPuesto(Request $request)
  {

    $disponibles = DB::select("SELECT if(empleados.total is NULL, 0 , empleados.total) total, plazas.autorizados, if(sols.cantidad is NULL, 0 , sols.cantidad) solicitudes, (plazas.autorizados - if(empleados.total is NULL, 0 , empleados.total)- if(sols.cantidad is NULL, 0 , sols.cantidad)) AS diferencia  FROM ( SELECT estado, idPuesto, SUM(cantidad) autorizados FROM rh_plazas_autorizadas WHERE estado = 1 AND idPuesto = " . $request->input('idPuesto') . " AND idSucursal = " . $request->input('idSucursal') . " GROUP BY estado , idPuesto ) plazas LEFT JOIN ( SELECT rh_empleado.estado, idPuesto, COUNT(rh_empleado.estado) total FROM rh_empleado INNER JOIN sucursales ON rh_empleado.idSucursal = sucursales.id WHERE rh_empleado.estado = 1 AND idPuesto = " . $request->input('idPuesto') . " AND idSucursal = " . $request->input('idSucursal') . " GROUP BY rh_empleado.estado, idPuesto ) empleados ON empleados.estado = plazas.estado LEFT JOIN (SELECT idPuesto, COUNT(idPuesto) cantidad FROM  rh_vacante_solicitud_partida WHERE idPuesto = " . $request->input('idPuesto') . " AND solicitud =1 AND idSucursal = " . $request->input('idSucursal') . " AND estado = 1 GROUP BY idPuesto) sols ON sols.idPuesto = plazas.idPuesto;");

    $cantidad = 0;

    if (!empty($disponibles[0])) {
      if (!empty($disponibles[0]->diferencia) && $disponibles[0]->diferencia > 0)
        $cantidad = $disponibles[0]->diferencia;
    }

    $sql = "SELECT rh_empleado.idEmpleado as id, rh_empleado.nombre FROM rh_empleado WHERE rh_empleado.estado = 1 AND idPuesto = " . $request->input('idPuesto') . " AND idSucursal = " . $request->input('idSucursal') . ";";

    $empleados = DB::select($sql);

    return  response()->json(['disponibles' => $cantidad, 'empleados' => $empleados]);
  }

  public function saveRequest(Request $request = null, $sucursales = null, $trsucursales = null, $areas = null, $deptos = null, $acciones = null, $empleados = null, $nvoPuesto = null, $ids = null, $estado = null)
  {
    DB::enableQueryLog();
    $user = Auth::user();
    $idUsuario = $user->id;
    $uemail = $user->email;
    $uname = $user->name;
    if ($request != null) {
      $ids = $request->input('id');
      $sucursales = $request->input('idSucursal');
      $trsucursales = $request->input('transucId');
      $areas = $request->input('idArea');
      $deptos = $request->input('idDepto');
      $acciones = $request->input('accion');
      $empleados = $request->input('empleado');
      $comentario = $request->input('comentario');
      $nvoPuesto = $request->input('nvoPuesto');
      $empleadoBaja = $request->input('empleadoBaja');
    }

    if (!empty($sucursales)) {
      $comentario = empty($comentario) ? "" : $comentario;
      DB::insert('insert into rh_vacante_solicitud (idUsuario, fechaCrea, horaCrea, comentario) values (?, ?, ?, ?)', [$idUsuario, date("Y-m-d"), date("H:i:s"), $comentario]);
      $lid = DB::getPdo()->lastInsertId();
      $sql = "";
      $sqlBajas = "";
      $sqlNvoPuesto = array();
      if (!empty($lid)) {
        foreach ($empleados as $id => $value) {

          // if($request != null){
          $sql = "SELECT A.idSucursal, A.idPuesto, B.idArea FROM rh_empleado AS A INNER JOIN rh_puesto_area AS B ON A.idPuesto = B.idPuesto INNER JOIN rh_area AS C ON B.idArea = C.id WHERE idEmpleado = ?";
          $datos = DB::select($sql, [$value]);

          if (!empty($datos)) {
            $idPuesto = $datos[0]->idPuesto;
            $areas[$id] = $datos[0]->idArea;
            $deptos[$id] = 0;
          } else {
            $idPuesto = 0;
            $areas[$id] = 0;
            $deptos[$id] = 0;
          }
          // }
          if ($acciones[$id] == 3 || $acciones[$id] == 5) {
            $idPuesto = $nvoPuesto[$id];
          }
          $sql = '';
          if (!empty($sql))
            $sql .= ", ";


          if ($acciones[$id] == 10) {
            // $idPuesto = DB::select("SELECT idPuesto FROM rh_empleado WHERE idEmpleado = " . (empty($empleados[$id])?0:$empleados[$id]));
            if (!empty($sql))
              $sql .= ", ";
            $sql .= "( " . $lid . " ," . $idPuesto . ", " . $sucursales[$id] . ", " . $trsucursales[$id] . ", " . $areas[$id] . ", " . $deptos[$id] . ", 1 ,  0 ,   1  ,'" . date("Y-m-d") . "','" . date("H:i:s") . "'," . $idUsuario . ", '" . date("Y-m-d") . "', 0)";
            $sql .= ",( " . $lid . " ," . $nvoPuesto[$id] . ", " . $sucursales[$id] . ", " . $trsucursales[$id] . ", " . $areas[$id] . ", " . $deptos[$id] . ", " . $acciones[$id] . ", " . (empty($empleados[$id]) ? 0 : $empleados[$id]) . ",6,'" . date("Y-m-d") . "','" . date("H:i:s") . "'," . $idUsuario . ", '" . date("Y-m-d") . "', " . (empty($empleados[$id]) ? 0 : $empleados[$id]) . " )";

            $sqlNvoPuesto[] = "UPDATE rh_empleado SET idPuesto= " . $nvoPuesto[$id] . " WHERE idEmpleado = " . (empty($empleados[$id]) ? 0 : $empleados[$id]);

            // if (!empty($empleadoBaja[$id])) {
            // 	$auxEmpBaja = explode("|", $empleadoBaja[$id]);	
            // 	if ($auxEmpBaja[0] == 1 && !empty($auxEmpBaja[1])) {
            // 		if (!empty($sqlBajas))
            // 			$sqlBajas .= ", ";
            // 		$sqlBajas .= $empleados[$id];
            // 	} else if ($auxEmpBaja[0] == 4 && !empty($auxEmpBaja[1])) {
            // 		$sqlNvoPuesto[] = "UPDATE rh_empleado SET idPuesto=" . $auxEmpBaja[1] . " WHERE id = " . $empleados[$id];
            // 	} else if (!empty($auxEmpBaja[1])) {
            // 		$sqlNvoPuesto[] = "UPDATE rh_vacante_solicitud_partida SET estado = 6 ,idContratado= " . $empleados[$id] . " WHERE idPartida = " . $auxEmpBaja[1];
            // 	}
            // }
          } else {
            $estadoP = !empty($estado) ? $estado[$id] : '1';

            $sql .= "( " . $lid . " ," . $idPuesto . ", " . $sucursales[$id] . ", " . $trsucursales[$id] . ", " . $areas[$id] . ", " . $deptos[$id] . ", " . $acciones[$id] . ", " . (empty($empleados[$id]) ? 0 : $empleados[$id]) . ",'" . $estadoP . "','" . date("Y-m-d") . "','" . date("H:i:s") . "'," . $idUsuario . ", '" . date("Y-m-d") . "', 0)";

            if (($acciones[$id] == 6 || $acciones[$id] == 2) && !empty($empleados[$id])) {
              if (!empty($sqlBajas))
                $sqlBajas .= ", ";
              $sqlBajas .= $empleados[$id];
            }
          }
        }
        if (!empty($sql)) {
          DB::insert('insert into rh_vacante_solicitud_partida (idSolicitud, idPuesto, idSucursal, idSucursalTrans, idArea, idDepartamento, solicitud , idEmpleado,estado,lastUpdateDate, lastUpdateTime, idUserUpdate, ingreso, idContratado) values ' . $sql);

          if (!empty($sqlBajas))
            $affected = DB::update('update rh_empleado set estado = 2 , fechaSolBaja= "' . date('Y-m-d') . '", horaSolBaja= "' . date('H:i:s') . '" where idEmpleado IN (' . $sqlBajas . ")");

          if (!empty($sqlNvoPuesto))
            foreach ($sqlNvoPuesto as $evsql)
              $affected = DB::update($evsql);
          if (!empty($lid)) {
            $sql = "SELECT RHR.email FROM users RHR INNER JOIN rh_sucursal_usuario RHRS ON RHR.id = RHRS.idUsuario INNER JOIN config_app_access RHAC ON RHAC.idUsuario = RHR.id INNER JOIN rh_vacante_solicitud_partida RHRP ON RHRP.idSucursal = RHRS.idSucursal WHERE RHAC.idAplicacion=3 AND RHAC.idRole = 2 and  RHRP.idSolicitud = " . $lid . " GROUP BY RHR.email;";
            $reclutadores = DB::select($sql);
            $recArray = array();
            foreach ($reclutadores as $rec) {
              $recArray[] = $rec->email;
            }
            $recArray[] = "apiana@prigo.com.mx";
            $recArray[] = "emarin@prigo.com.mx";
            $recArray[] = "rgallardo@prigo.com.mx";
            $recArray[] = "ntorres@prigo.com.mx";
            $url = url('/detallevacante/' . $lid);

            $partidas = DB::select("SELECT tipo.idTipo, tipo.tipo solicitud, estado.estado, rh_vsp.idSolicitud, rh_vsp.idPartida, rh_vs.fechaCrea, rh_o.nombre AS sucursal, rh_o2.nombre AS transferencia,  rh_p.nombre AS puesto, rh_vs.comentario, rh_empleado.nombre FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN sucursales AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_tipo tipo ON tipo.idTipo = rh_vsp.solicitud INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado LEFT JOIN rh_empleado ON rh_empleado.idEmpleado = rh_vsp.idEmpleado LEFT JOIN sucursales AS rh_o2 ON rh_o2.id = rh_vsp.idSucursalTrans WHERE rh_vsp.idSolicitud = '" . $lid . "';");

            $sqlAut = "";

            foreach ($partidas as $partida) {
              if ($partida->idTipo == 4 || $partida->idTipo == 5 || $partida->idTipo == 3) {
                if (!empty($sqlAut))
                  $sqlAut .= ", ";
                $sqlAut .= "(" . $lid . ", " . $partida->idPartida . ",0," . $partida->idTipo . " )";
              }
            }

            if (!empty($sqlAut))
              DB::insert('insert into rh_vacante_solicitud_autorizacion (idSolicitud, idPartida, idAutoriza, tipoSolicitud ) values ' . $sqlAut);

            // Mail::send('vacantes.mailVacante', ['url' => $url,'name' => $uname, 'partidas' => $partidas, 'comentario' => $comentario], function ($message) use ($recArray)
            // {
            // 	$message->from('reportes@prigo.com.mx', 'Reportes PRIGO');
            // 	$message->to($recArray);
            // 	$message->subject("Nueva solicitud de reclutamiento recibida");
            // });

            return $lid;
          } else {
            return "{ 'success': false, 'msg': 'Error al guardar los datos!'}";
          }
        }
      }
    }
  }

  public function showPendingDismiss()
  {
    $tipo = 6;
    $sql = "SELECT * FROM rh_baja_tipo";
    $tiposBaja = DB::select($sql);
    return view('vacantes.showDismissRequests', ["tipo" => $tipo, "titulo" => "Bajas", 'role' => session('RHRole'), 'tipoBaja' => $tiposBaja]);
  }

  public function getBaja(Request $request)
  {
    $idBaja = $request->input('idBaja');
    $idEmpleado = $request->input('idEmpleado');

    $baja = DB::table('rh_empleado_baja')
      ->where('idEmpleado', $idEmpleado)
      ->where('idBaja', $idBaja)
      ->get();

    return response()->json([
      'success' => true,
      'data' => $baja,
    ]);
  }

  public function getDismissRequest(Request $request)
  {
    $strBusca = "";
    $draw = !empty($request->input('draw')) ? $request->input('draw') : 1;
    $start = !empty($request->input('start')) ? $request->input('start') : 0;
    $length = !empty($request->input('length')) ? $request->input('length') : 10;
    $queryarr = !empty($request->input('search')) ? $request->input('search') : array();
    $query = !empty($queryarr["value"]) ? $queryarr["value"] : "";
    $allitems = null;

    if (!empty($request->input('columns'))) {
      $cols = $request->input('columns');
      $busca = array();
      foreach ($cols as $col) {
        if (!empty($col["search"]["value"])) {
          switch ($col["data"]) {
            case "puesto":
              $busca[] = " rh_p.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
            case "sucursal":
              $busca[] = " rh_o.nombre LIKE '%" . $col["search"]["value"] . "%' ";
              break;
          }
        }
      }
      if (!empty($busca)) {
        $strBusca = " AND " . implode(" AND ", $busca);
      }
    }

    $orden = " datos.fechaCrea DESC ";
    if (!empty($request->input('order'))) {
      $ordena = $request->input('order');
      $orden = "";
      switch ($ordena[0]["column"]) {
        case 0:
          $orden = " datos.sucursal " . $ordena[0]["dir"];
          break;
        case 1:
          $orden = " datos.puesto  " . $ordena[0]["dir"];
          break;
        case 2:
          $orden = " datos.nombre  " . $ordena[0]["dir"];
          break;
      }
    }

    //TODO: Checar que pasa cuando no existe tiempo de contratacion para algun puesto, podemos poner default 8 dias  atodos los peustos nuevos
    //$allitems = DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso  , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, rh_rec.nombre AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN rh_oficinas AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN rh_reclutador_sucursal rh_recsuc ON rh_recsuc.idSucursal = rh_vsp.idSucursal LEFT JOIN rh_reclutador rh_rec ON rh_rec.idReclutador = rh_recsuc.idReclutador WHERE ".$strBusca.") AS datos;");
    $allitems = DB::select("SELECT * FROM ( SELECT rh_e.idEmpleado, rh_e.nombre, rh_o.nombre sucursal, rh_p.nombre puesto FROM rh_empleado rh_e LEFT JOIN sucursales rh_o ON rh_e.idSucursal = rh_o.id LEFT JOIN rh_puesto rh_p ON rh_e.idPuesto = rh_p.idPuesto WHERE (rh_e.estado = 2 OR rh_e.estado = 6) " . $strBusca . " ) AS datos;");
    //$items =    DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, IF(rh_vsp.solicitud =1,'Cubrir Vacante','Reemplazo') AS solicitud , rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, estado.estado, rh_rec.nombre AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN rh_oficinas AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado  LEFT JOIN rh_reclutador_sucursal rh_recsuc ON rh_recsuc.idSucursal = rh_vsp.idSucursal INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN rh_reclutador rh_rec ON rh_rec.idReclutador = rh_recsuc.idReclutador WHERE ". (empty($strStatus)?"NOT(rh_vsp.estado IN(4,5,6) )": " rh_vsp.estado = ".$strStatus)." ".($RHRole != 1 ?" AND rh_vsp.idSucursal ".$strValSucursales:"")." ".$strBusca.") AS datos ".(empty($strAtraso)?"":" WHERE $strAtraso")."  ORDER BY $orden LIMIT ".$start.", ".$length.";");
    //$items = DB::select("SELECT * FROM ( SELECT rh_vsp.idSolicitud, IF(rh_vsp.solicitud =1,'Cubrir Vacante','Reemplazo') AS solicitud , rh_vs.fechaCrea , ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ) limite, rh_vsp.lastUpdateDate ,IF(NOW() > ADDDATE(rh_vs.fechaCrea, INTERVAL tiempo.tiempo+1 DAY ),1,0) AS atraso , rh_o.nombre AS sucursal, rh_p.nombre AS puesto, rh_vs.comentario, estado.estado, rh_rec.name AS reclutador FROM rh_vacante_solicitud_partida AS rh_vsp INNER JOIN rh_vacante_solicitud AS rh_vs ON rh_vsp.idSolicitud = rh_vs.idSolicitud INNER JOIN rh_puesto AS rh_p ON rh_p.idPuesto = rh_vsp.idPuesto INNER JOIN rh_oficinas AS rh_o ON rh_o.id = rh_vsp.idSucursal INNER JOIN rh_vacante_estado estado ON estado.idEstado = rh_vsp.estado   	 	LEFT JOIN rh_sucursal_usuario rh_recsuc ON rh_vsp.idSucursal = rh_recsuc.idSucursal INNER JOIN config_app_access ON (rh_recsuc.idUsuario = config_app_access.idUsuario AND config_app_access.idRole = 2  AND config_app_access.idAplicacion = 3) INNER JOIN rh_tiempo_contrata tiempo ON tiempo.idPuesto = rh_vsp.idPuesto LEFT JOIN users rh_rec ON rh_rec.id = rh_recsuc.idUsuario WHERE ". (empty($strStatus)?"NOT(rh_vsp.estado IN(4,5,6) )": " rh_vsp.estado = ".$strStatus)." ".($RHRole != 1 ?" AND rh_vsp.idSucursal ".$strValSucursales:"")." ".$strBusca.") AS datos ".(empty($strAtraso)?"":" WHERE $strAtraso")."  ORDER BY $orden LIMIT ".$start.", ".$length.";");
    $items = DB::select("SELECT * FROM ( SELECT rh_e.idEmpleado, CONCAT(rh_e.nombre, ' ',rh_e.apellido_pat, ' ',rh_e.apellido_mat) as nombre, rh_o.nombre sucursal, rh_p.nombre puesto, rh_e.fechaSolBaja fechaSolBaja, rh_e.fechaBaja fechaBaja, rh_e.estado,  rh_b.idBaja,rh_b.boletinado, rh_b.recontratable FROM rh_empleado rh_e LEFT JOIN sucursales rh_o ON rh_e.idSucursal = rh_o.id LEFT JOIN rh_puesto rh_p ON rh_e.idPuesto = rh_p.idPuesto INNER JOIN rh_empleado_baja rh_b ON rh_b.idEmpleado = rh_e.idEmpleado WHERE (rh_e.estado = 2 OR rh_e.estado = 6) " . $strBusca . " ) AS datos ORDER BY $orden LIMIT " . $start . ", " . $length . ";");
    return  response()->json([
      'draw' => $draw,
      'recordsTotal' => count($allitems),
      'recordsFiltered' => count($allitems),
      'data' => $items
    ]);
  }

  public function showEmployees()
  {
    $user = Auth::user();
    //$idEmpresa = $user->idEmpresa;
    $idEmpresa = 1;
    $sql = "SELECT * FROM sucursales WHERE idEmpresa = ?";
    $sucursales = DB::select($sql, [$idEmpresa]);

    $sql = "SELECT * FROM rh_baja_tipo";
    $tiposBaja = DB::select($sql);
    return view('vacantes.empleados', ['role' => session('RHRole'), 'sucursales' => $sucursales, 'tipoBaja' => $tiposBaja]);
  }

  public function formNewEmployee($id = 0)
  {
    $user = Auth::user();
    //$idEmpresa = $user->idEmpresa;
    $idEmpresa = 1;
    $sql = "SELECT id as idSucursal, nombre FROM sucursales WHERE idEmpresa = ?";
    $sucursales = DB::select($sql, [$idEmpresa]);

    $infoEmp = DB::table('rh_empleado as A')
      ->select('A.*', 'B.nombre as sucursal', 'C.nombre as puesto')
      ->leftJoin('sucursales as B', 'A.idSucursal', 'B.id')
      ->leftJoin('rh_puesto as C', 'A.idPuesto', 'C.idPuesto')
      ->where('idEmpleado', $id)
      ->get();
    $puestos = DB::table('rh_puesto')
      ->where('estado', 1)
      ->get();
    $deptos = DB::table('rh_departamento')
      ->where('estado', 1)
      ->get();
    $sexos = DB::table('rh_sexo')
      ->where('estado', 1)
      ->get();
    $medios = DB::table('rh_medios_pago')
      ->where('estado', 1)
      ->get();
    $estudios = DB::table('rh_nivel_estudios')
      ->where('estado', 1)
      ->get();
    $edoCivil = DB::table('rh_edocivil')
      ->where('estado', 1)
      ->get();
    $sociedad = DB::table('rh_sociedad')
      ->where('estado', 1)
      ->get();

    return view('vacantes.formNewEmpleado', ['role' => session('RHRole'), 'sucursales' => $sucursales, 'infoEmp' => $infoEmp, 'puestos' => $puestos, 'deptos' => $deptos, 'sexos' => $sexos, 'medios' => $medios, 'estudios' => $estudios, 'edoCivil' => $edoCivil, 'sociedad' => $sociedad]);
  }

  public function uploadXlsxScreen()
  {
    //return view('vacantes.altasXlsx', ['role' => session('RHRole'),]);
    return view('vacantes.altasXlsx', ['role' => 1,]);
  }

  public function registrarEmpleado(Request $request)
  {
    $razSocial = $request->input('razSocial');
    $numColaborador = $request->input('numColaborador');
    $sexo = $request->input('sexo');
    $edoCivil = $request->input('edoCivil');
    $estudios = $request->input('estudios');
    $depto = $request->input('departamento');
    $credInfo = $request->input('credInfo');
    $formPago = $request->input('formPago');
    $numTarjeta = $request->input('numTarjeta');
    $nombre = $request->input('nombre');
    $apellidoPat = $request->input('apellidoPat');
    $apellidoMat = $request->input('apellidoMat');
    $fechaNacimiento = $request->input('fechaNacimiento');
    $fechaIngreso = $request->input('fechaIngreso');
    $sucursal = $request->input('sucursal');
    $idPuesto = $request->input('puesto');
    $nss = $request->input('nss');
    $rfc = $request->input('rfc');
    $curp = $request->input('curp');
    $telFijo = $request->input('telFijo');
    $celular = $request->input('celular');
    $calle = $request->input('calle');
    $numExt = $request->input('numExt');
    $numInt = $request->input('numInt');
    $colonia = $request->input('colonia');
    $munOAlc = $request->input('munOAlc');
    $cp = $request->input('cp');
    $estadoDir = $request->input('estado');
    $correo = $request->input('correo');
    $idEmpleado = $request->input('idEmpleado');
    $salario100 = $request->input('salario100');
    $salario90 = $request->input('salario90');
    $salario10 = $request->input('salario10');
    $user = Auth::user();
    $idUsuario = $user->id;
    $array = $request->all();


    if (!empty($nombre) && !empty($sucursal) && !empty($idPuesto)) {

      if (!empty($idEmpleado)) {
        $infoEmp = DB::table('rh_empleado')
          ->select('idEmpleado', 'nombre', 'apellido_pat as apellidoPat', 'apellido_mat as apellidoMat', 'fechaNacimiento', 'fechaIngreso', 'idSucursal as sucursal', 'idPuesto as puesto', 'correo', 'nss',  'rfc',  'curp',  'telFijo',  'celular',  'calle',  'numExt',  'numInt',  'colonia',  'munOAlc',  'cp',  'estadoDir as estado',  'salario100',  'salario90',  'salario10', 'idDepartamento as departamento', 'idSociedad as razSocial', 'sexo', 'edoCivil', 'estudios', 'credInfo', 'formPago', 'numTarjeta', 'numColaborador')
          ->where('idEmpleado', $idEmpleado)
          ->get();
      }

      if (!empty($salario10) && !empty($salario90) && empty($salario100)) {
        $salario100 = $salario10 + $salario90;
      }

      $sql = "SELECT * FROM rh_puesto WHERE idPuesto = ?";
      $puesto = DB::select($sql, [$idPuesto]);

      $sql = "INSERT INTO rh_empleado (idEmpleado, idSucursal, idPuesto, puesto, nombreCompleto,nombre, apellido_pat, apellido_mat,fechaNacimiento, fechaIngreso, idUsuario, fechaCrea, horaCrea, nss, rfc, curp, calle, numExt, numInt, colonia, munOAlc, cp, estadoDir, telFijo, celular, correo, salario100, salario90, salario10, idDepartamento, idSociedad, sexo, edoCivil, estudios, credInfo, formPago, numTarjeta, numColaborador) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE idSucursal = ?, idPuesto = ?, puesto = ?, nombre = ?, apellido_pat = ?, apellido_mat = ?,fechaNacimiento = ?, fechaIngreso = ?, nss = ?, rfc = ?, curp = ?, calle = ?, numExt = ?, numInt = ?, colonia = ?, munOAlc = ?, cp = ?, estadoDir = ?, telFijo = ?, celular = ?, correo = ?, salario100 = ?, salario90 = ?, salario10 = ?, idDepartamento = ?, idSociedad = ?, sexo = ?, edoCivil = ?, estudios = ?, credInfo = ?, formPago = ?, numTarjeta = ?, numColaborador = ?";
      $insert = DB::insert($sql, [$idEmpleado, $sucursal, $idPuesto, $puesto[0]->nombre, '', $nombre, $apellidoPat, $apellidoMat, $fechaNacimiento, $fechaIngreso, $idUsuario, date('Y-m-d'), date('H:i:s'), $nss, $rfc, $curp, $calle, $numExt, $numInt, $colonia, $munOAlc, $cp, $estadoDir, $telFijo, $celular, $correo, $salario100, $salario90, $salario10, $depto, $razSocial, $sexo, $edoCivil, $estudios, $credInfo, $formPago, $numTarjeta, $numColaborador, $sucursal, $idPuesto, $puesto[0]->nombre, $nombre, $apellidoPat, $apellidoMat, $fechaNacimiento, $fechaIngreso, $nss, $rfc, $curp, $calle, $numExt, $numInt, $colonia, $munOAlc, $cp, $estadoDir, $telFijo, $celular, $correo, $salario100, $salario90, $salario10, $depto, $razSocial, $sexo, $edoCivil, $estudios, $credInfo, $formPago, $numTarjeta, $numColaborador]);
      $lid = DB::getPdo()->lastInsertId();


      if ($insert && !empty($idEmpleado)) {
        $keys = array_keys($array);
        array_shift($keys);
        $sql = [];
        $vacAccion = '';

        foreach ($keys as $key => $value) {
          $vacAccion = '';
          if ($infoEmp[0]->$value != $array[$value]) {
            if ($value == 'calle'  || $value == 'numExt'  || $value == 'numInt'  || $value == 'colonia'  || $value == 'munOAlc'  || $value == 'cp'  || $value == 'estado') {
              $vacAccion = 8;
              if ($value == 'munOAlc') {
                $valorAnterior = "Municipio o Alcaldia: {$infoEmp[0]->$value}";
              } else {
                $valorAnterior = "$value: {$infoEmp[0]->$value}";
              }
            } else if ($value == 'curp' || $value == 'nss' || $value == 'rfc') {
              $vacAccion = 9;
              $valorAnterior = "$value: {$infoEmp[0]->$value}";
            } else if ($value == 'correo' || $value == 'telFijo' || $value == 'celular') {
              $vacAccion = 10;
              $valorAnterior = "$value: {$infoEmp[0]->$value}";
            } else if ($value == 'fechaNacimiento') {
              $vacAccion = 5;
              $valorAnterior = "{$infoEmp[0]->$value}";
            } else if ($value == 'fechaIngreso') {
              $vacAccion = 6;
              $valorAnterior = "{$infoEmp[0]->$value}";
            } else if ($value == 'nombre' || $value == 'apellidoPat' || $value == 'apellidoMat') {
              $vacAccion = 1;
              $valorAnterior = "$value: {$infoEmp[0]->$value}";
            } else if ($value == 'salario100') {
              $vacAccion = 11;
              $valorAnterior = "{$infoEmp[0]->$value}";
            } else if ($value == 'salario90') {
              $vacAccion = 12;
              $valorAnterior = "{$infoEmp[0]->$value}";
            } else if ($value == 'salario10') {
              $vacAccion = 13;
              $valorAnterior = "{$infoEmp[0]->$value}";
            }

            if ($vacAccion != '') {
              array_push($sql, [
                'idUsuario' => $idUsuario,
                'idEmpleado' => $idEmpleado,
                'idAccion' => $vacAccion,
                'fechaCrea' => date('Y-m-d'),
                'horaCrea' => date('H:i:s'),
                'valorAnterior' => $valorAnterior,
              ]);
            }
          }
        }

        DB::table('rh_vacante_log')
          ->insert($sql);
      }

      if ($insert && $lid != 0 && empty($idEmpleado)) {
        DB::table('rh_vacante_log')
          ->insert([
            'idUsuario' => $idUsuario,
            'idEmpleado' => $lid,
            'idAccion' => 7,
            'fechaCrea' => date('Y-m-d'),
            'horaCrea' => date('H:m:s'),
            'valorAnterior' => 'N / R',
          ]);
      }

      if ($insert) {
        return response()->json([
          'success' => true,
          'msg' => 'El empleado se registro correctamente!'
        ]);
      } else {
        return response()->json([
          'success' => false,
          'msg' => ''
        ]);
      }
    } else {
      return response()->json([
        'success' => false,
        'msg' => 'Faltan datos'
      ]);
    }
  }

  public function employeeDetail($id)
  {

    $datos = $this->getEmployeeDetail($id);
    $empleado = $datos->empleado;
    $partidas = $datos->partidas;
    $sucursales = $datos->sucursales;
    $puestos = $datos->puestos;

    $sql = "SELECT * FROM rh_vacante_tipo WHERE estatus = 1";
    $tiposVac = DB::select($sql);
    /*if(empty($empleado[0]))
		dd($id);*/
    return view('vacantes.empleado', ['empleado' => $empleado[0], 'partidas' => $partidas, 'role' => session('RHRole'), 'sucursales' => $sucursales, 'puestos' => $puestos, 'tiposVac' => $tiposVac]);
  }

  public function gestionPuestos(Request $request)
  {
    $sql = "SELECT * FROM rh_puesto";
    $puestos = DB::select($sql);
    return view('vacantes.gestionPuestos', ['role' => session('RHRole'), 'puestos' => $puestos]);
  }

  public function agregarPuesto(Request $request)
  {
    $nombre = $request->input('nombre');

    if (!empty($nombre)) {
      $sql = "INSERT INTO rh_puesto (nombre, orden, estado) VALUES (?, 1, 1)";
      $insert = DB::insert($sql, [$nombre]);

      $lid = DB::getPdo()->lastInsertId();
      if ($insert) {
        return response()->json([
          "success" => true,
          "msg" => "El puesto se creo con exito!",
          "id" => $lid
        ]);
      } else {
        return response()->json([
          "success" => false,
          "msg" => "Ocurrio un error al crear"
        ]);
      }
    } else {
      return response()->json([
        "success" => false,
        "msg" => "Faltan datos"
      ]);
    }
  }

  public function editarPuesto(Request $request)
  {
    $idPuesto = $request->input('idPuesto');
    $nombre = $request->input('nombre');

    if (!empty($idPuesto)) {
      $sql = "UPDATE rh_puesto SET nombre = ? WHERE idPuesto = ?";
      $update = DB::update($sql, [$nombre, $idPuesto]);

      if ($update) {
        return response()->json([
          "success" => true,
          "msg" => "El puesto se actualizo con exito!"
        ]);
      } else {
        return response()->json([
          "success" => false,
          "msg" => "Ocurrio un error al actualizar"
        ]);
      }
    } else {
      return response()->json([
        "success" => false,
        "msg" => "Faltan datos"
      ]);
    }
  }

  public function eliminarPuesto(Request $request)
  {
    $idPuesto = $request->input('idPuesto');

    if (!empty($idPuesto)) {

      $sql = "SELECT * FROM rh_empleado WHERE idPuesto = ?";
      $select = DB::select($sql, [$idPuesto]);

      if (empty($select)) {

        $sql = "DELETE FROM rh_puesto WHERE idPuesto = ?";
        $delete = DB::delete($sql, [$idPuesto]);

        if ($delete) {
          return response()->json([
            "success" => true,
            "msg" => "Se ha eliminado exitosamente!"
          ]);
        } else {
          return response()->json([
            "success" => false,
            "msg" => "Ocurrio un problema al eliminar"
          ]);
        }
      } else {
        return response()->json([
          "success" => false,
          "msg" => "No se puede eliminar",
          "msg2" => "Debes eliminar o desasignar los empleados de este puesto antes de eliminar"
        ]);
      }
    } else {
      return response()->json([
        "success" => false,
        "msg" => "Faltan datos"
      ]);
    }
  }

  public function micros(Request $request)
  {
    $sucu = session('sucursales') ?? 0;
    $sql = "SELECT idEmpleado as id, nombre FROM rh_empleado WHERE estado = 1";
    $empleados = DB::select($sql);
    $sql = "SELECT A.idprofile AS id,CONCAT(A.firstName, ' ',A.lastName) AS nombre, A.checkName, B.nombre AS sucursal, A.HireDate, A.DateofBirth FROM rh_micros_profile A INNER JOIN sucursales B ON A.locationRef = B.idSap WHERE micros_id is null";
    $perfiles = DB::select($sql);
    $sql = "SELECT * FROM rh_puesto WHERE estado = 1";
    $puestos = DB::select($sql);
    $sql = "SELECT id, nombre, idSap FROM sucursales WHERE estado = 1 AND id IN ($sucu)";
    $sucursales = DB::select($sql, []);
    return view('vacantes.micros', ['role' => session('RHRole'), 'empleados' => $empleados, 'perfiles' => $perfiles, 'puestos' => $puestos, 'sucursales' => $sucursales]);
  }

  public function getPerfilesMicros(Request $request)
  {
    $sucursales = session('sucursales') ? "WHERE id IN (" . session('sucursales') . ")" : '';

    $sucursales = DB::select("SELECT idSap as `sucursales` FROM sucursales $sucursales", []);
    $sucu = "";
    foreach ($sucursales as $key => $value) {
      if (!empty($sucu))
        $sucu .= ",";
      $sucu .= "'{$value->sucursales}'";
    }

    $nacimientoIni = substr($request->input('nacimiento') ?? date('0000-00-00'), 0, 10);
    $nacimientoFin = !empty($request->input('nacimiento')) ? substr($request->input('nacimiento'), 13, 22) : date('Y-m-d');
    $contratacionIni = substr($request->input('contratacion') ?? date('0000-00-00'), 0, 10);
    $contratacionFin = !empty($request->input('contratacion')) ? substr($request->input('contratacion'), 13, 22) : date('Y-m-d');
    $sucursal = !empty($request->input('sucursal')) ? "'{$request->input('sucursal')}'" : $sucu;
    $nombre = "%{$request->input('nombre')}%" ?? '%%';
    $limit = $request->input('limit') ?? 20;
    $offset = $request->input('offset') ?? 0;



    $sql = "SELECT A.idprofile AS id,CONCAT(A.firstName, ' ',A.lastName) AS nombre, A.checkName, B.nombre AS sucursal, A.HireDate, A.DateofBirth FROM rh_micros_profile A INNER JOIN sucursales B ON A.locationRef = B.idSap WHERE micros_id is NULL AND A.DateofBirth BETWEEN ? AND ? AND A.HireDate BETWEEN ? AND ? AND A.locationRef IN ($sucursal) AND CONCAT(A.firstName, ' ',A.lastName) LIKE ? LIMIT ? OFFSET ?";
    $perfiles = DB::select($sql, [$nacimientoIni, $nacimientoFin, $contratacionIni, $contratacionFin, $nombre, $limit, $offset]);


    $sql = "SELECT COUNT(A.idprofile)as registros FROM rh_micros_profile A INNER JOIN sucursales B ON A.locationRef = B.idSap WHERE micros_id is NULL AND A.DateofBirth BETWEEN ? AND ? AND A.HireDate BETWEEN ? AND ? AND A.locationRef IN ($sucursal) AND CONCAT(A.firstName, ' ',A.lastName) LIKE ?";
    $perfilesCount = DB::select($sql, [$nacimientoIni, $nacimientoFin, $contratacionIni, $contratacionFin, $nombre]);

    $paginas = $perfilesCount[0]->registros / $limit;

    $paginaActual = $offset / $limit;

    return response()->json([
      'success' => true,
      'data' => $perfiles,
      'paginas' => ceil($paginas),
      'paginaActual' => $paginaActual,
    ]);
  }

  public function getEmpleadosMicros(Request $request)
  {
    $nombre = "%{$request->input('nombre')}%" ?? '%%';
    $limit = $request->input('limit') ?? 20;
    $offset = $request->input('offset') ?? 0;
    $sucursales = session('sucursales') ? "AND WHERE idSucursal IN (" . session('sucursales') . ")" : '';
    // $sucursales = session('sucursales') ?? 0;

    $sql = "SELECT idEmpleado as id, CONCAT(nombre, ' ',apellido_pat, ' ',apellido_mat) as nombre FROM rh_empleado WHERE estado = 1 AND nombreCompleto LIKE ? $sucursales LIMIT ? OFFSET ?";
    $empleados = DB::select($sql, [$nombre, $limit, $offset]);

    $sql = "SELECT COUNT(idEmpleado) as registros FROM rh_empleado WHERE estado = 1 AND nombre LIKE ? $sucursales";
    $empleadosCount = DB::select($sql, [$nombre]);

    $paginas = $empleadosCount[0]->registros / $limit;

    $paginaActual = $offset / $limit;

    return response()->json([
      'success' => true,
      'data' => $empleados,
      'paginas' => ceil($paginas),
      'paginaActual' => $paginaActual,
    ]);
  }

  public function crearEmpleadoPerf(Request $request)
  {
    $idPerfiles = $request->input('idPerfiles');
    $nombre = $request->input('nombre');
    $puesto = $request->input('puesto');
    $idSucursal = $request->input('sucursal');

    if (!empty($idPerfiles) && !empty($puesto) && !empty($nombre) && !empty($idSucursal)) {

      $idPerfilesString = implode(",", $idPerfiles);

      $sql = "SELECT * FROM rh_puesto WHERE idPuesto = ?";
      $puesto = DB::select($sql, [$puesto]);

      $sql = "SELECT HireDate, DateofBirth FROM rh_micros_profile A WHERE idProfile IN ($idPerfilesString) ORDER BY HireDate LIMIT 1";
      $perfiles = DB::select($sql);

      $sql = "INSERT INTO rh_empleado (nombre, fechaNacimiento, fechaIngreso,fechaCrea, horaCrea, puesto, idPuesto, idSucursal) VALUE (?,?,?,?,?,?,?,?)";
      $insert = DB::insert($sql, [$nombre, $perfiles[0]->DateofBirth, $perfiles[0]->HireDate, date('Y-m-d'), date('H:m:s'), $puesto[0]->nombre, $puesto[0]->idPuesto, $idSucursal]);

      $lid = DB::getPdo()->lastInsertId();

      $sql = "UPDATE rh_micros_profile SET micros_id = ? WHERE idProfile = ?";

      foreach ($idPerfiles as $key => $value) {
        DB::update($sql, ["$lid-$key", $value]);
      }

      if ($insert) {
        return response()->json([
          "success" => true,
          "msg" => "El empleado se creo exitosamente!",
          "empleado" => (object)[
            "id" => $lid,
            "nombre" => $nombre,
          ]
        ]);
      } else {
        return response()->json([
          "success" => false,
          "msg" => "Ocurrio un error"
        ]);
      }
    } else {
      return response()->json([
        "success" => false,
        "msg" => "Debe seleccionar por lo menos 1 perfil"
      ]);
    }
  }

  public function agruparPerfilesEmp(Request $request)
  {
    $idEmpleado = $request->input('idEmpleado');
    $idPerfiles = $request->input('idPerfiles');

    if (!empty($idEmpleado) && !empty($idPerfiles)) {

      $key = 0;
      $sql = "SELECT micros_id  FROM rh_micros_profile WHERE micros_id LIKE ? ORDER BY micros_id DESC LIMIT 1";
      $perfiles = DB::select($sql, ["$idEmpleado-%"]);

      if (!empty($perfiles)) {
        $numero = explode("-", $perfiles[0]->micros_id);
        $key = intval($numero[1]) + 1;
      }

      $sql = "UPDATE rh_micros_profile SET micros_id = ? WHERE idProfile = ?";

      foreach ($idPerfiles as $value) {
        DB::update($sql, ["$idEmpleado-$key", $value]);
        $key++;
      }

      return response()->json([
        "success" => true,
        "msg" => "Los perfiles se asignaron correctamente!"
      ]);
    } else {
      return response()->json([
        "success" => false,
        "msg" => "Debe seleccionar 1 empleado y por lo menos 1 perfil"
      ]);
    }
  }

}
