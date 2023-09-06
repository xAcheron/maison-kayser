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
}
