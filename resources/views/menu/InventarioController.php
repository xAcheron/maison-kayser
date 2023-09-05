<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Storage;
use Barryvdh\DomPDF\Facade as PDF;

class InventarioController extends Controller
{
    public function __construct()
    {
		
		$this->middleware('auth', ['except' => ['getSQL','getPendingTransfer','confirmTransfer']]);
		
		$this->convertidos = array();
		
		$this->middleware(function ($request, $next) {
			$idUsuario = Auth::id();
			$user = Auth::user();
			$sql = "SELECT * FROM config_app_access WHERE idUsuario = ? AND idAplicacion = 1;";
			$accesQuery = DB::select($sql, [$idUsuario]);
			if(!empty($accesQuery))
			{
			   session(['PDRole' => $accesQuery[0]->idRole]);
			   if($accesQuery[0]->idRole != 1)
			   {
					$sql = "SELECT group_concat(`idSucursal` separator ',') as `sucursales` FROM pedidos_sucursal_usuario WHERE idUsuario = ? GROUP BY idUsuario;";
					$sucursales = DB::select($sql, [$idUsuario]);
					if(!empty($sucursales))
					{
						session(['sucursales' => $sucursales[0]->sucursales]);
					}
			   }
			}

            return $next($request);
        });
    }

    public function index()
    {
        return view('inventario.index',['role' =>session('PDRole')]);
	}
	
	public function getSQL()
	{
		$sql = array();
		$sucursales = DB::select("SELECT * FROM sucursales WHERE estado = 1 AND idEmpresa = 1;",[]);
		foreach($sucursales as $sucursal)
		{
			$SucMicros = $sucursal->idMicros;
			$SucSap= $sucursal->idSap;
			$sql[] = "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, ROUND(INMD.InvInicial,2) InvInicial, INMD.CompraDirecta, INMD.TransferIn, INMD.Merma, INMD.Comida, ROUND(INMD.TransferOut,2) TransferOut , ROUND(INMD.CostoVenta,2) CostoVenta, ROUND(INMD.InvFinal,2) InvFinal, IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '2021-02-28' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '2021-02-28' AND '2021-02-28' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='2021-02-28' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '2021-02-28'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '2021-02-28' AND ARTS.Descripcion LIKE '%oreja%' ORDER BY INMD.idSucSap , INMD.ItemCode";
		}

		return implode(" UNION ALL ",$sql);
		
	}

	public function read(Request $request)
	{

		$idUsuario = Auth::id();
		$draw = !empty($request->input('draw'))?$request->input('draw'):1;
		$start = !empty($request->input('start'))?$request->input('start'):0;
		$length = !empty($request->input('length'))?$request->input('length'):10;
		$queryarr = !empty($request->input('search'))?$request->input('search'):array();
		$query = !empty($queryarr["value"]) ? $queryarr["value"] : "%%" ;
		$sucursalq = "%%" ;
		$fechaq = "%%" ;
		
		$allitems = null;
		
		$PDRole = session('PDRole');
		$sucursales = session('sucursales');
		$strValSucursales = "";

		if($PDRole != 1)
		{
			if(!empty($sucursales))
			{
				$strValSucursales = " IN (".$sucursales.") ";
			}
			else
			{				
				$strValSucursales = " IN (29,30,31) ";
			}
		}
		
		if(!empty($request->input('columns')))
		{
			$cols = $request->input('columns');
			$busca = array();
			foreach($cols as $col)
			{
				if(!empty($col["search"]["value"]))
				{
					switch($col["data"]){
						case "fecha":
							$fechaq = "%".$col["search"]["value"]."%";
						break;
						case "sucursal":
							$sucursalq = "%".$col["search"]["value"]."%";
						break;
					}
				}
			}
			if(!empty($busca))
			{
				$strBusca = implode(" AND ", $busca); 
			}
			
		}
		

		if($PDRole != 1)
		{

			$allitems = DB::table('inventario')
			->join('sucursales', 'inventario.idSucursal', '=', 'sucursales.id')
			->select('inventario.idInventario')
			->where('inventario.idInventario','LIKE',$query)
			->where('inventario.fecha','LIKE',$fechaq)
			->where('sucursales.nombre','LIKE',$sucursalq)
			->whereRaw('inventario.idSucursal '.$strValSucursales)
			->orderBy('inventario.idInventario', 'DESC')
			->get();
		}
		else
		{
			$allitems = DB::table('inventario')
			->join('sucursales', 'inventario.idSucursal', '=', 'sucursales.id')
			->select('inventario.idInventario')
			->where('inventario.idInventario','LIKE',$query)
			->where('inventario.fecha','LIKE',$fechaq)
			->where('sucursales.nombre','LIKE',$sucursalq)
			->orderBy('inventario.idInventario', 'DESC')
			->get();
		}

		
		if($PDRole != 1)
		{
			$items = DB::table('inventario')
			->join('sucursales', 'inventario.idSucursal', '=', 'sucursales.id')
			->join('pedidos_pedido_estado AS ppe','ppe.idEstado','=','inventario.estado')
			->select('inventario.idInventario AS idPedido' ,'sucursales.nombre AS sucursal' , 'inventario.fecha', 'inventario.created_at AS hora', 'ppe.estado', 'inventario.codigoSAP AS codsap')
			->where('inventario.idInventario','LIKE',$query)
			->where('inventario.fecha','LIKE',$fechaq)
			->where('sucursales.nombre','LIKE',$sucursalq)
			->whereRaw('inventario.idSucursal '.$strValSucursales)
			->orderBy('inventario.idInventario', 'DESC')
			->skip($start)->take($length)
			->get();
		}
		else 
		{
			$items = DB::table('inventario')
			->join('sucursales', 'inventario.idSucursal', '=', 'sucursales.id')
			->join('pedidos_pedido_estado AS ppe','ppe.idEstado','=','inventario.estado')
			->select('inventario.idInventario AS idPedido' ,'sucursales.nombre AS sucursal' , 'inventario.fecha', 'inventario.created_at AS hora', 'ppe.estado', 'inventario.codigoSAP AS codsap')
			->where('inventario.idInventario','LIKE',$query)
			->where('inventario.fecha','LIKE',$fechaq)
			->where('sucursales.nombre','LIKE',$sucursalq)
			->orderBy('inventario.idInventario', 'DESC')
			->skip($start)->take($length)
			->get();
		}
		
		return  response()->json([
			'draw' => $draw,
			'recordsTotal' => count($allitems),
			'recordsFiltered' => count($allitems),
			'data' => $items
		]);
	}
	
	public function create()
	{
		$PDRole = session('PDRole');
		$sucursales = session('sucursales');
		$strValSucursales = "";
		
		if($PDRole != 1)
			if(!empty($sucursales))
				$strValSucursales = " IN (".$sucursales.") ";
			else
				$strValSucursales = " IN (29,30,31) ";
			
		$sucs = DB::select("SELECT sucursales.id AS idAlmacen, sucursales.nombre almacen FROM sucursales INNER JOIN pedidos_sucursal_config ON pedidos_sucursal_config.idSucursal = sucursales.id WHERE estado = 1 ".($PDRole != 1 ? "AND id ".$strValSucursales : "" )." GROUP BY id, nombre ORDER BY nombre;");	
		
		if(count($sucs) == 1)
			$selected= $sucs[0]->idAlmacen;
		else
			$selected=0;
		
		$suggestedDate = date('Y-m-d');

		return view('inventario.newInventoryRequest', ['role' =>session('PDRole'),'almacenes' => $sucs, 'selected' => $selected, 'suggestedDate' => $suggestedDate ]);
	}

	public function edit($id=0)
	{
		$inventario= array();
		$items= array();
		if(!empty($id))
		{
			$sql = "SELECT inventario.*, sucursales.nombre sucursal  FROM inventario INNER JOIN sucursales ON sucursales.id = inventario.idSucursal WHERE inventario.idInventario = ?;";
			$inventario = DB::select($sql,[$id]); 
			$sql = "SELECT A.idPartida, A.idArticulo ,B.Descripcion name, A.cantidad cant, A.unidad unit , B.CodPrigo cod , A.precioPromedio, A.totalPartida FROM inventario_partida A INNER JOIN pedidos_articulo B ON A.idArticulo = B.idArticulo WHERE A.idInventario= ? ";
			$items = DB::select($sql,[$id]); 
		}
	
		return view('inventario.'.( $inventario[0]->estado>1?'showInventory':'editInventory'), ['role' =>session('PDRole'), "inventario" =>$inventario, "items" => $items, "nitems" => $nitems = count($items)]);
	}

	public function print($id=0)
	{

		$inventario= array();
		$items= array();
		if(!empty($id))
		{
			$sql = "SELECT inventario.*, sucursales.nombre sucursal  FROM inventario INNER JOIN sucursales ON sucursales.id = inventario.idSucursal WHERE inventario.idInventario = ?;";
			$inventario = DB::select($sql,[$id]); 
			$sql = "SELECT A.idPartida, A.idArticulo ,B.Descripcion name, A.cantidad cant, A.unidad unit , B.CodPrigo cod FROM inventario_partida A INNER JOIN pedidos_articulo B ON A.idArticulo = B.idArticulo WHERE A.idInventario= ? ";
			$items = DB::select($sql,[$id]); 
			$pdf = PDF::loadView('inventario.pdf', ["inventario" => $inventario, "items"=>$items]);
			return $pdf->download('INV_'.$inventario[0]->codigoSAP.'_'.$inventario[0]->fecha.'_.pdf');
		}
	
		return "<h3>Error, Inventario no encontrado</h3>";
	
	}

	public function save(Request $request)
	{
		DB::enableQueryLog();
		$user = Auth::user();
		$idUsuario = $user->id;
		$uemail = $user->email;
		$uname = $user->name;
		$sucursal = $request->input('almacen');		
		$ids = $request->input('id');
		$cantidades = $request->input('cantidad');
		$precios = $request->input('precio');

		$estado = empty($request->input('cerrado'))? 1 : 2;
		$fechaRequerida = $request->input('fechaRequerida');
		$narts = $request->input('narts');
		
		$guardados = 0;
		$suc = DB::select("SELECT idSap FROM sucursales WHERE id = ? ;",[$sucursal]);
		$almacenTO = $suc[0]->idSap;
		
	
		if(!empty($almacenTO) && !empty($ids) && !empty($cantidades) )
		{
			if(count($ids) == count($cantidades))
			{
				$comentario = empty($comentario)?"":$comentario;
				DB::insert('insert into inventario (idSucursal, idUsuario, codigoSAP, fecha, created_at, closed_at, estado) values ( ?, ?, ?, ?, ?, ?, ?)', [$sucursal, $idUsuario, $almacenTO, $fechaRequerida, date("Y-m-d H:i:s"), ($estado == 2 ? date("Y-m-d H:i:s"):"1970-01-01 00:00:00") ,$estado]);
				$lid = DB::getPdo()->lastInsertId();
				
				if(!empty($lid))
				{
					$sql = "";
					foreach($ids as $id=>$value)
					{
						
						$items = DB::table('pedidos_articulo')	
						->select('UnidadPrg')
						->where('idArticulo', '=', $value)
						->get();
						
						$unidad = $items[0]->UnidadPrg;
						
						if(!empty($sql))
							$sql .= ", ";
						
						$precios[$id]= empty($precios[$id])?0:$precios[$id];	

						$sql .= "( ".$lid." ,".$value.", ".$cantidades[$id].", '".$unidad."',".$precios[$id].",".($cantidades[$id]*$precios[$id])." ,1)";
						$guardados++;
					}
					if(!empty($sql))
					{
						try {
							DB::insert('insert into inventario_partida (idInventario, idArticulo, cantidad, unidad, precioPromedio, totalPartida, estado) values '.$sql);
						} catch (\Exception $e) {
							$guardados = 0;
						}
						
						$hora = date("H:i");
						
						$txtAdicional = "";
						
						if($narts != count($ids) || $narts != $guardados)
							$txtAdicional = "<br> Se guardaron $guardados articulos";
						
						return response()->json([
							'success' => true,
							'guardados' => $guardados,
							'msg' => "Inventario #".$lid." ".$hora. " " . $txtAdicional
						]);
					}
					else
					{
						return "{ 'success': false, 'msg': 'Error al guardar las partidas!'}";
					}
				}
				else
				{
					return "{ 'success': false, 'msg': 'Error al guardar los datos!'}";
				}
			}
			else
			{
				return "{ 'success': false, 'msg': 'Informaci&oacute;n incompleta, ".count($ids)." != ".count($cantidades)."'}";
			}
		}
		else
		{
			return "{ 'success': false, 'msg': 'Informaci&oacute;n incompleta, verifique los datos capturados'}";
		}		
		
		
		return "{ 'success': true }";
	}

	public function update(Request $request)
	{
		DB::enableQueryLog();
		$user = Auth::user();
		$idUsuario = $user->id;
		$uemail = $user->email;
		$uname = $user->name;	
		$ids = $request->input('id');
		$idPs = $request->input('idP');
		$cantidades = $request->input('cantidad');
		$precios = $request->input('precio');
		$estado = empty($request->input('cerrado'))? 1 : 2;
		$narts = $request->input('narts');
		$guardados = 0;
		$txtAdicional = "";


		$sqlInsert ="";
		
		$lid = $request->input('idInventario');

		if( !empty($lid) && !empty($ids) && !empty($cantidades) )
		{
			if($estado==2)
			{
				$sql = "UPDATE inventario SET closed_at = ?, estado = ?  WHERE idInventario = ?;";
				DB::update($sql, [ date("Y-m-d H:i:s"), $estado,$lid]);
			}
				

			foreach($ids as $id=>$value)
			{
				
				 $items = DB::table('pedidos_articulo')	
				->select('UnidadPrg')
				->where('idArticulo', '=', $value)
				->get();
				
				$unidad = $items[0]->UnidadPrg;
				
				if(!empty($sqlInsert))
					$sqlInsert .= ", ";

				$precios[$id]= empty($precios[$id])?0:$precios[$id];
				
				if(empty($idPs[$id]))
					$sqlInsert .= "( NULL, ".$lid." ,".$value.", ".$cantidades[$id].", '".$unidad."',".$precios[$id].",".($cantidades[$id]*$precios[$id])." ,1)";
				else
					$sqlInsert .= "( ".$idPs[$id]." , ".$lid." ,".$value.", ".$cantidades[$id].", '".$unidad."',".$precios[$id].",".($cantidades[$id]*$precios[$id])." ,1)";

				$guardados++;
			}
			if(!empty($sqlInsert))
			{
				try {
					$sql = 'insert into inventario_partida (idPartida, idInventario, idArticulo, cantidad, unidad, precioPromedio, totalPartida, estado) values '.$sqlInsert.' ON DUPLICATE KEY UPDATE cantidad=VALUES(cantidad), totalPartida=VALUES(cantidad)*VALUES(precioPromedio);';
					DB::insert($sql);
				} catch (\Exception $e) {
					$guardados = 0;
				}
				
				$hora = date("H:i");
				
				$txtAdicional = "";
				
				if($narts != count($ids) || $narts != $guardados)
					$txtAdicional = "<br> Se guardaron $guardados articulos.";
				
				return response()->json([
					'success' => true,
					'msg' => "Inventario #".$lid." ".$hora. " " . $txtAdicional,
					'sql' => $sql
				]);
			}
			else
			{
				return "{ 'success': false, 'msg': 'Error al guardar las partidas!'}";
			}

		}
		else
		{
			return "{ 'success': false, 'msg': 'Error al guardar los datos!'}";
		}

	}
	public function removeItem($id)
	{

		if(!empty($id))
		{
			$sql = "DELETE FROM inventario_partida WHERE idPartida = ?";
			DB::delete($sql,[$id]);
			return response()->json([
				'success' => true,
				'msg' => "Se elimino la partida correctamente"
			]);
		}

		return "{ 'success': false, 'msg': 'Error al guardar las partidas!'}";

	}

	public function processFormat(Request $request)
	{
		
		if($request->hasFile('xlsInv') && $request->file('xlsInv')->isValid())
		{
			$file = $request->file('xlsInv');

			if($file->getMimeType() == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
			{

				$inputFileName = $file->getRealPath();
				
				$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
				
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
				
				$reader->setReadDataOnly(true);

				$spreadsheet = $reader->load($inputFileName);
				
				$sheets = $spreadsheet->getSheetNames();
				
				$items = array();
				$noitems = array();

				$sn = "Inventario";
				$codLetra ="B";
				$desLetra ="C";
				$unidadLetra ="D";
				$cantLetra ="E";

				$sheetData = $spreadsheet->getSheetByName($sn);

				for($i=2;!empty($sheetData->getCell($codLetra.$i)->getValue());$i++ )
				{
					$cantidad = $sheetData->getCell($cantLetra.$i)->getValue();
					
					if(is_numeric($cantidad) AND !empty($cantidad))
					{
						$CodPrigo = $sheetData->getCell($codLetra.$i)->getValue();
						
						$item = DB::table('pedidos_articulo')
						->select('idArticulo AS id' ,'CodPrigo AS cod' , 'Descripcion AS name', 'UnidadPrg AS unit', 'UnidadFood AS unitfood', 'Conversion as conv','Estado', 'precioPromedio')
						->Where('CodPrigo', '=', $CodPrigo)
						->Where('Estado', '>', 0)
						->take(1)
						->get();
						$tmpitem = new \stdClass();
						if(!empty($item[0]))
						{
							$tmpitem = $item[0];
							$tmpitem->cant = $cantidad;
							$tmpitem->totalPartida = (empty($cantidad)?0:$cantidad) * $item[0]->precioPromedio;
							$items[] = $tmpitem;
						}
						else
						{
							$tmpitem->cod =  $sheetData->getCell($codLetra.$i)->getValue();
							$tmpitem->name = $sheetData->getCell($desLetra.$i)->getValue();
							$tmpitem->unit = $sheetData->getCell($unidadLetra.$i)->getValue();
							$tmpitem->cant = $cantidad;
							$tmpitem->cantprg = $cantidad;
							$noitems[] = $tmpitem;
						}
						$tmpitem =null;	
					}
				}
				if(!empty($noitems) || !empty($items))
				{
					return response()->json([
						'success' => true,
						'items' => $items,
						'noitems' => $noitems,
						'msg' => "Articulos cargados [".count($items)."], Articulos no reconocidos [".count($noitems)."]"
					]);
				}
				else
				{
					return response()->json([
						'success' => false,
						'msg' => "Error no se ha reconocido ningun articulo!"
					]);
				}

			}
			else
			{
				return response()->json([
					'success' => false,
					'msg' => "Error Formato de archivo no valido solo se aceptan archivos XLSX!"
				]);
			}

		}
		else
		{
			return response()->json([
				'success' => false,
				'msg' => "Error al cargar el archivo intentelo mas tarde!"
			]);

		}
	}

	public function getInventoryFormat($id="1")
	{
		$suc = DB::select("SELECT * FROM sucursales WHERE id= ?;",[$id]);
		
		$sql = "SELECT GROUP_CONCAT(idProveedor) provs FROM pedidos_sucursal_proveedor WHERE idSucursal= ? GROUP BY idSucursal; ";
		$provs =  DB::select($sql,[$id]);
		$suc = $id;
		//$id = $suc[0]->idSap;

		/*
		$sql = "SELECT ItemCode FROM OITW WHERE OnHand >0 AND WhsCode = ? ;";
		$items = DB::connection('sqlsrv')->select($sql,[$id]);
		
		$selItems = "";
		foreach($items AS $item)
		{
			if($selItems)
				$selItems .= ", ";
			$selItems .= "'".$item->ItemCode."'";
		}
		*/




		//SELECT CodPrigo, MAX(Descripcion) AS Descripcion, MAX(UnidadPrg) AS UnidadPrg FROM pedidos_articulo WHERE CodPrigo IN ($selItems) GROUP BY CodPrigo ORDER BY Descripcion;
		//$sql = "SELECT CodPrigo, MAX(Descripcion) AS Descripcion, MAX(UnidadPrg) AS UnidadPrg, categoria.nombre AS Categoria FROM pedidos_articulo INNER JOIN pedidos_categoria_art categoria ON categoria.idCategoria = pedidos_articulo.idCategoria WHERE NOT(CodPrigo IS NULL) AND CodPrigo != '' AND idProveedor IN (".$provs[0]->provs.") AND pedidos_articulo.Estado IN (1,2,3) AND pedidos_articulo.idCompania = ".$suc[0]->idEmpresa." GROUP BY prigo_intranet.categoria.nombre, CodPrigo ORDER BY Categoria, Descripcion ;";
		$sql = "SELECT PA.idArticulo  AS id, PA.CodPrigo , PA.Descripcion, PA.UnidadPrg, PCA.nombre AS Categoria FROM ( SELECT * FROM ( SELECT idArticulo FROM pedidos_traslado_partida PTP INNER JOIN pedidos_traslado PT ON PT.idTraslado = PTP.idTraslado WHERE PT.idSucursal = $suc GROUP BY PTP.idArticulo UNION ALL SELECT idArticulo FROM pedidos_pedido_partida PPP INNER JOIN pedidos_pedido PP ON PP.idPedido = PPP.idPedido WHERE PP.idSucursal = $suc GROUP BY PPP.idArticulo UNION ALL SELECT idArticulo FROM inventario_partida IP INNER JOIN inventario INV ON IP.idInventario = INV.idInventario WHERE INV.idSucursal = $suc GROUP BY IP.idArticulo) AS ARTSG GROUP BY idArticulo ) ARTS INNER JOIN pedidos_articulo PA ON ARTS.idArticulo = PA.idArticulo LEFT JOIN pedidos_categoria_art PCA ON PCA.idCategoria = PA.idCategoria WHERE PA.Estado IN (1,2,3) AND NOT(PA.idProveedor IN (1)) ORDER BY PCA.nombre, PA.Descripcion ;";
		
		$intraItems = DB::select($sql);

		$spreadsheet = new Spreadsheet();

		$sugestedItems = $spreadsheet->getActiveSheet();
		$sugestedItems->setTitle('Inventario');
		$sugestedItems->setCellValue('A1', 'Categoria');
		$sugestedItems->setCellValue('B1', 'Codigo');
		$sugestedItems->setCellValue('C1', 'Descripcion');
		$sugestedItems->setCellValue('D1', 'Unidad');
		$sugestedItems->setCellValue('E1', 'Cantidad');

		$sugestedItems->getColumnDimension('A')->setWidth(12);
		$sugestedItems->getColumnDimension('B')->setWidth(12);
		$sugestedItems->getColumnDimension('C')->setWidth(40);
		$sugestedItems->getColumnDimension('D')->setWidth(12);
		$sugestedItems->getColumnDimension('E')->setWidth(12);

		$row =1;

						
		foreach($intraItems as $item)
		{
			$row++;
			$sugestedItems->setCellValueExplicit('A'.$row, $item->Categoria,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
			$sugestedItems->setCellValueExplicit('B'.$row, $item->CodPrigo,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
			$sugestedItems->setCellValue('C'.$row, $item->Descripcion);
			$sugestedItems->setCellValue('D'.$row, $item->UnidadPrg);
			$sugestedItems->setCellValue('E'.$row, 0);
			$sugestedItems->getStyle('A'.$row.':E'.$row)
			->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		}

		/*$sugestedItems->getStyle("A:A")
		->getNumberFormat()
		->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT );
*/
		$sugestedItems->getStyle('A1:E1')
		->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('A1:E'.$row)
		->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('A1:E'.$row)
		->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('A1:E'.$row)
		->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('A1:E'.$row)
		->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('B1:E'.$row)
		->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('C1:E'.$row)
		->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
		$sugestedItems->getStyle('A1:E'.$row)
		->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				
		$sugestedItems->getProtection()->setSheet(true);
		$spreadsheet->getDefaultStyle()->getProtection()->setLocked(false);
		$sugestedItems->getStyle('E1:E'.$row)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="INV_'.$id.'_'.date("Ymd").'.xlsx"');
		$writer->save("php://output");


/*
		$intraItems = DB::select($sql);
		echo "<h3>$id</h3>";
		foreach($intraItems as $item)
		{
			echo "<br>".$item->CodPrigo." - ".$item->Descripcion." - ".$item->UnidadPrg;
		}
*/
	}

	public function getPendingTransfer()
	{
		
		$sql = "SELECT C.idTransferencia, COUNT(C.idTransferencia) Partidas  FROM inventario_transferencia C INNER JOIN inventario_transferencia_partida A ON A.idTransferencia = C.idTransferencia INNER JOIN pedidos_articulo B ON A.idArticulo = B.idArticulo INNER JOIN sucursales O ON O.id =C.idSucursalO INNER JOIN sucursales D ON D.id = C.idSucursalD WHERE C.fecha>='2021-03-01'  AND C.estado =2 GROUP BY C.idTransferencia HAVING COUNT(C.idTransferencia) >0 ORDER BY C.idTransferencia LIMIT 0,1;";

		$arrpedidos = DB::select($sql);
		
		if(count($arrpedidos))
		{

			$idTraslados = $arrpedidos[0]->idTransferencia;
			
			$sql = "SELECT C.idTransferencia, C.fecha, O.idSap AS WhsFrom, D.idSap AS WhsTo,U.name Usuario, C.codigoSAP FROM inventario_transferencia C INNER JOIN sucursales O ON O.id =C.idSucursalO INNER JOIN sucursales D ON D.id = C.idSucursalD INNER JOIN users AS U ON U.id = C.idUsuario LEFT JOIN users AS U2 ON U2.id = C.idUsuarioAutoriza WHERE C.estado =2 AND C.idTransferencia = ? ;";
			
			$pedidos = DB::select($sql,[$idTraslados]);

			$objs = array();

			foreach($pedidos AS $pedido)
			{
				$object = new \stdClass();
				
				$object->Id = $pedido->idTransferencia;
				
				$object->CreationDate = $pedido->fecha ;
				
				$object->RequiredDate = $pedido->fecha ;
				
				$object->Creator = $pedido->Usuario;
				
				$object->WareHouse = $pedido->WhsTo ;
				
				$object->FromWareHouse = $pedido->WhsFrom ;
				
				$object->Comment = " Creado por ".$pedido->Usuario;

				$sql = "SELECT B.CodPrigo AS ItemId, A.idPartida AS IdArticulo, A.cantidad AS Quantity FROM inventario_transferencia_partida A INNER JOIN pedidos_articulo B ON A.idArticulo = B.idArticulo WHERE A.idTransferencia = ?;";
				
				$partidas = DB::select($sql,[$pedido->idTransferencia]);
				
				$object->Lines = count($partidas) ;
				
				$object->Partidas = $partidas;
				
				$objs[] = $object;

			}

			return response()->json([
				'Total' => count($pedidos),
				'Transferencias' => $objs
			]);
		}
		
		else
		{
			return response()->json([
				'Total' => 0,
				'Transferencias' => $arrpedidos
			]);
		}
	}

	public function confirmTransfer(Request $request)
	{
		if(!empty($request->input("idPedido")))
		{
			if(!empty($request->input("DocEntry")))
			{
				$contents = $request->input("DocEntry") . "|" . $request->input("idTransferencia") ;
				Storage::put('peticion.txt', $contents);
				
				$pedido = DB::select("SELECT idTransferencia,codigoSAP FROM inventario_transferencia WHERE idTransferencia = ? ;",[$request->input("idPedido")]);
				
				if(empty($pedido))
				{
					return "El pedido".$request->input("idPedido")." no existe";
				}
				else
				{
					$codigoSAP = (empty($pedido[1]->codigoSAP)? $request->input("DocEntry"): $pedido[1]->codigoSAP.",".$request->input("DocEntry") );
					DB::table('inventario_transferencia')
					->where('idTransferencia', $request->input("idPedido"))
					->update([ 'estado' => 3, 'codigoSAP' => $codigoSAP]);

					if(!empty($request->input("Parciales")))
					{
						$arrpar = explode("|",$request->input("Parciales"));

						foreach($arrpar as $par)
						{
							$pararr = explode(":", $par);
							if(!empty($pararr[0]))
							{
								DB::table('inventario_transferencia_partida')
								->where('idPartida', $pararr[0])
								->update([ 'estado' => 3, 'pendiente' => $pararr[1]]);
							}

						}

					}
					
					if(!empty($request->input("Nodisponibles")))
					{
						$arrpar = explode("|",$request->input("Nodisponibles"));
						foreach($arrpar as $par)
						{
							$pararr = explode(":", $par);
							if(!empty($pararr[0]))
							{
								DB::table('inventario_transferencia_partida')
								->where('idPartida', $pararr[0])
								->update([ 'estado' => 4, 'pendiente' => $pararr[1]]);
							}

						}
					}
				}
			}
			else
			{
				return 2;
			}			
		}
		else
		{
			return 0;
		}
		
		return 1;
	}

	public function venta()
	{
		$PDRole = session('PDRole');
		$sucursales = session('sucursales');
		$strValSucursales = "";
		
		if($PDRole != 1)
			if(!empty($sucursales))
				$strValSucursales = " IN (".$sucursales.") ";
			else
				$strValSucursales = " IN (29,30,31) ";
			
		$sucs = DB::select("SELECT sucursales.id AS idSucursal, sucursales.nombre FROM sucursales INNER JOIN pedidos_sucursal_config ON pedidos_sucursal_config.idSucursal = sucursales.id WHERE estado = 1 ".($PDRole != 1 ? "AND id ".$strValSucursales : "" )." GROUP BY id, nombre ORDER BY nombre;");	
		
		if(count($sucs) == 1)
			$selected= $sucs[0]->idSucursal;
		else
			$selected=0;

		$suc = DB::select("SELECT * FROM sucursales WHERE estado = 1 AND idEmpresa = 1 ;");

		return view('inventario.venta', [ 'sucursales' => $suc, 'sucursales' => $sucs, 'selected' => $selected ]);
	}

	public function costo()
	{

		$PDRole = session('PDRole');
		$sucursales = session('sucursales');
		$strValSucursales = "";
		
		if($PDRole != 1)
			if(!empty($sucursales))
				$strValSucursales = " IN (".$sucursales.") ";
			else
				$strValSucursales = " IN (29,30,31) ";
			
		$sucs = DB::select("SELECT sucursales.id AS idSucursal, sucursales.nombre FROM sucursales INNER JOIN pedidos_sucursal_config ON pedidos_sucursal_config.idSucursal = sucursales.id WHERE estado = 1 ".($PDRole != 1 ? "AND id ".$strValSucursales : "" )." GROUP BY id, nombre ORDER BY nombre;");	
		
		if(count($sucs) == 1)
			$selected= $sucs[0]->idSucursal;
		else
			$selected=0;

		$suc = DB::select("SELECT * FROM sucursales WHERE estado = 1 AND idEmpresa = 1 ;");

		return view('inventario.costo', [ 'sucursales' => $suc, 'sucursales' => $sucs, 'selected' => $selected ]);

	}

	public function ventaRead(Request $request)
	{
		if(empty($request->input('fechaIni')) && empty($request->input('fechaFin')))
		{
			$idSucursal = $request->input('sucursal');
			$draw = !empty($request->input('draw'))?$request->input('draw'):1;
			$start = !empty($request->input('start'))?$request->input('start'):0;
			$length = !empty($request->input('length'))?$request->input('length'):10;
			$queryarr = !empty($request->input('search'))?$request->input('search'):array();

			$ffin = date("W",strtotime(date("Y-m-d")));

			$fini = date("W",strtotime(date("Y-m-d"). "-4 WEEKS"));
			
			if(!empty($idSucursal))
				$sql = "SELECT semanal.itemNumber AS ItemCode, m.itemName AS Descripcion, GROUP_CONCAT(semanal.cantidad) AS semanas,MAX(semanal.cantidad) maximo, MIN(semanal.cantidad) minimo, AVG(semanal.cantidad) promedio, STDDEV(semanal.cantidad) stddev FROM (SELECT WEEK(d.fecha, 1) semana, d.idSucursal, itemNumber, SUM(countLine) cantidad FROM cheque_dia_detalle d WHERE d.idSucursal = ? AND d.fecha BETWEEN ADDDATE(NOW(), INTERVAL -4 WEEK) AND ADDDATE(NOW(), INTERVAL -(DAYOFWEEK(NOW())-1) DAY) GROUP BY itemNumber, d.idSucursal , WEEK(fecha, 1) ORDER BY d.idSucursal, itemNumber, WEEK(fecha, 1)) AS semanal INNER JOIN sucursales s ON semanal.idSucursal = s.id LEFT JOIN micros_producto m ON m.idItemMicros = semanal.itemNumber GROUP BY idSucursal, itemNumber, m.itemName ORDER BY m.itemName;";
			else
				$sql = "SELECT semanal.itemNumber AS ItemCode, m.itemName AS Descripcion, GROUP_CONCAT(semanal.cantidad) AS semanas,MAX(semanal.cantidad) maximo, MIN(semanal.cantidad) minimo, AVG(semanal.cantidad) promedio, STDDEV(semanal.cantidad) stddev FROM (SELECT WEEK(d.fecha, 1) semana, itemNumber, SUM(countLine) cantidad FROM cheque_dia_detalle d WHERE d.fecha BETWEEN ADDDATE(NOW(), INTERVAL -4 WEEK) AND ADDDATE(NOW(), INTERVAL -(DAYOFWEEK(NOW())-1) DAY) GROUP BY itemNumber, WEEK(fecha, 1) ORDER BY itemNumber, WEEK(fecha, 1)) AS semanal LEFT JOIN micros_producto m ON m.idItemMicros = semanal.itemNumber GROUP BY itemNumber, m.itemName ORDER BY m.itemName;";

			$reporte = DB::select($sql,[$idSucursal]);

			return response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte,
				'sql' => $sql
			]);

		}
		else
		{
			$fini = date("W",strtotime($request->input('fechaIni')));
			$ffin = date("W",strtotime($request->input('fechaFin')));
		}

		$sql = "SELECT WEEK('".$request->input('fechaIni')."', 1) sem1, WEEK('".$request->input('fechaFin')."', 1) sem2 ;";
		$sems = DB::select($sql);
		return $fini."|".$ffin."<br>".$sems[0]->sem1."|".$sems[0]->sem2;
	}

	public function costoRead(Request $request)
	{
		$idUsuario = Auth::id();
		$draw = !empty($request->input('draw'))?$request->input('draw'):1;
		$start = !empty($request->input('start'))?$request->input('start'):0;
		$length = !empty($request->input('length'))?$request->input('length'):10;
		$queryarr = !empty($request->input('search'))?$request->input('search'):array();
		$query = !empty($queryarr["value"]) ? $queryarr["value"] : "" ;
		$detalle = !empty($request->input('detalle'))?1:0;
		$sucursalq = "%%" ;
		$fechaq = "%%" ;
		$reporte = array();
		$allitems = array();

		$data = $request->all();

		if(!empty($data['sucursal']) && !empty($data['fecha']) )
		{

			$sucursal = DB::select("SELECT * FROM sucursales WHERE id = ?;",[$data['sucursal']]);
			
			$SucMicros = $sucursal[0]->idMicros;
			$SucSap= $sucursal[0]->idSap;
			$fecha= $data["fecha"];
			$fechaIni = date("Y-m", strtotime($fecha))."-01";

			if($detalle)
				$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , INMD.AvgPrice, INMD.InvInicial, INMD.InvInicialCosto, INMD.CompraDirecta, INMD.CompraDirectaCosto, INMD.TransferIn, INMD.TransferInCosto, INMD.Merma, INMD.MermaCosto, INMD.Comida, INMD.ComidaCosto, INMD.TransferOut, INMD.TransferOutCosto, INMD.CostoVenta, INMD.CostoVentaCosto, INMD.InvFinal, INMD.InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ) AS VentaTeorica, (IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )* INMD.AvgPrice) AS VentaTeoricaCosto, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) InvFinalTeorico, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) * INMD.AvgPrice InvFinalTeoricoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) * INMD.AvgPrice AS ConteoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			else
				$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, ROUND(INMD.InvInicial,2) InvInicial, INMD.CompraDirecta, INMD.TransferIn, INMD.Merma, INMD.Comida, ROUND(INMD.TransferOut,2) TransferOut , ROUND(INMD.CostoVenta,2) CostoVenta, ROUND(INMD.InvFinal,2) InvFinal, IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
				#$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, INMD.InvInicial, ROUND(INMD.InvInicialCosto,2) InvInicialCosto, (INMD.CompraDirecta + INMD.TransferIn) entradas, (INMD.Merma + INMD.Comida + INMD.TransferOut) salidas, ROUND(INMD.CostoVenta,2) CostoVenta, INMD.InvFinal, ROUND(INMD.InvFinalCosto,2) InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			
			$reporte = DB::select($sql);

			return  response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte,
				'sql' => $sql
			]);
		}
		else if(!empty($data['fecha']))
		{
			$fecha= $data["fecha"];
			$fechaIni = date("Y-m", strtotime($fecha))."-01";

			if($detalle)
				$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , INMD.AvgPrice, INMD.InvInicial, INMD.InvInicialCosto, INMD.CompraDirecta, INMD.CompraDirectaCosto, INMD.TransferIn, INMD.TransferInCosto, INMD.Merma, INMD.MermaCosto, INMD.Comida, INMD.ComidaCosto, INMD.TransferOut, INMD.TransferOutCosto, INMD.CostoVenta, INMD.CostoVentaCosto, INMD.InvFinal, INMD.InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ) AS VentaTeorica, (IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )* INMD.AvgPrice) AS VentaTeoricaCosto, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) InvFinalTeorico, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) * INMD.AvgPrice InvFinalTeoricoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) * INMD.AvgPrice AS ConteoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			else
				$sql= "SELECT INMD.ItemCode, ARTS.Descripcion , ROUND(AVG(INMD.AvgPrice),2) AvgPrice, ROUND(SUM(INMD.InvInicial),2) InvInicial, SUM(INMD.CompraDirecta) CompraDirecta , SUM(INMD.TransferIn) TransferIn, SUM(INMD.Merma) Merma, SUM(INMD.Comida) Comida, ROUND(SUM(INMD.TransferOut),2) TransferOut , ROUND(SUM(INMD.CostoVenta),2) CostoVenta, ROUND(SUM(INMD.InvFinal),2) InvFinal, IF(SUM(VENTAM.cantidad) IS NULL, 0, ROUND(SUM(VENTAM.cantidad),2) ) AS VentaTeorica, ROUND(SUM(INMD.InvInicial) + SUM(INMD.CompraDirecta) + SUM(INMD.TransferIn)  + SUM(INMD.EntDev) - SUM(INMD.Merma) - SUM(INMD.Comida) - SUM(INMD.TransferOut) - IF(SUM(VENTAM.cantidad) IS NULL, 0,SUM(VENTAM.cantidad)),2) InvFinalTeorico, IF(SUM(INVCONT.cantidad) IS NULL, 0,SUM(INVCONT.cantidad)) AS Conteo, IF(SUM(INVCONT.cantidad) IS NULL, 0,SUM(INVCONT.cantidad))- IF(SUM(INMD.InvFinal) IS NULL, 0 ,SUM(INMD.InvFinal)) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE  VMPM.fecha='$fecha' GROUP BY RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente) LEFT JOIN (SELECT PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE  INV.fecha = '$fecha' GROUP BY PA.CodPrigo) INVCONT ON (INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' GROUP BY  INMD.ItemCode, ARTS.Descripcion ORDER BY INMD.ItemCode";
				#$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, INMD.InvInicial, ROUND(INMD.InvInicialCosto,2) InvInicialCosto, (INMD.CompraDirecta + INMD.TransferIn) entradas, (INMD.Merma + INMD.Comida + INMD.TransferOut) salidas, ROUND(INMD.CostoVenta,2) CostoVenta, INMD.InvFinal, ROUND(INMD.InvFinalCosto,2) InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			
			$reporte = DB::select($sql);

			return  response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte,
				'sql' => $sql
			]);
		}
		else
		{

			return  response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte
			]);

		}
	}

	public function costoXls(Request $request)
	{
		$idUsuario = Auth::id();
		$queryarr = !empty($request->input('search'))?$request->input('search'):array();
		$query = !empty($queryarr["value"]) ? $queryarr["value"] : "" ;
		$detalle = !empty($request->input('detalle'))?1:0;
		$sucursalq = "%%" ;
		$fechaq = "%%" ;
		$reporte = array();
		$allitems = array();

		$data = $request->all();

		if(!empty($data['fecha']) )
		{

			if(!empty($data['sucursal']))
			{

				$sucursal = DB::select("SELECT * FROM sucursales WHERE id = ?;",[$data['sucursal']]);
				
				$SucMicros = $sucursal[0]->idMicros;
				$SucSap= $sucursal[0]->idSap;
				$fecha= $data["fecha"];
				$fechaIni = date("Y-m", strtotime($fecha))."-01";

				if($detalle)
					$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , INMD.AvgPrice, INMD.InvInicial, INMD.InvInicialCosto, INMD.CompraDirecta, INMD.CompraDirectaCosto, INMD.TransferIn, INMD.TransferInCosto, INMD.Merma, INMD.MermaCosto, INMD.Comida, INMD.ComidaCosto, INMD.TransferOut, INMD.TransferOutCosto, INMD.CostoVenta, INMD.CostoVentaCosto, INMD.InvFinal, INMD.InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ) AS VentaTeorica, (IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )* INMD.AvgPrice) AS VentaTeoricaCosto, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) InvFinalTeorico, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) * INMD.AvgPrice InvFinalTeoricoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) * INMD.AvgPrice AS ConteoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
				else
					$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(paph.precio,2) AvgPrice, ROUND(INMD.InvInicial,2) InvInicial, INMD.CompraDirecta, INMD.TransferIn, INMD.Merma, INMD.Comida, ROUND(INMD.TransferOut,2) TransferOut , ROUND(INMD.CostoVenta,2) CostoVenta, ROUND(INMD.InvFinal,2) InvFinal, IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN pedidos_articulo_precio_historico paph ON (MONTH(INMD.fecha) = paph.mes AND YEAR(INMD.fecha) = paph.anio AND INMD.ItemCode = paph.ItemCode ) INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
					#$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, INMD.InvInicial, ROUND(INMD.InvInicialCosto,2) InvInicialCosto, (INMD.CompraDirecta + INMD.TransferIn) entradas, (INMD.Merma + INMD.Comida + INMD.TransferOut) salidas, ROUND(INMD.CostoVenta,2) CostoVenta, INMD.InvFinal, ROUND(INMD.InvFinalCosto,2) InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
				
				$reporte = DB::select($sql);

				$spreadsheet = new Spreadsheet();

				$sugestedItems = $spreadsheet->getActiveSheet();
				$sugestedItems->setTitle('Costo_'.$SucSap);
				$sugestedItems->setCellValue('A1', 'Codigo');
				$sugestedItems->setCellValue('B1', 'Descripcion');
				$sugestedItems->setCellValue('C1', 'Precio Promedio');
				$sugestedItems->setCellValue('D1', 'Inv Inicial');
				$sugestedItems->setCellValue('E1', 'Compras');
				$sugestedItems->setCellValue('F1', 'Transferencia In');
				$sugestedItems->setCellValue('G1', 'Merma');
				$sugestedItems->setCellValue('H1', 'Comida Personal');
				$sugestedItems->setCellValue('I1', 'Transferencia Out');
				$sugestedItems->setCellValue('J1', 'Costo de Venta');
				$sugestedItems->setCellValue('K1', 'Venta Teorica');
				$sugestedItems->setCellValue('L1', 'Inv Final');
				$sugestedItems->setCellValue('M1', 'Conteo');
				$sugestedItems->setCellValue('N1', 'Inv Teorico');
		
				$sugestedItems->getColumnDimension('A')->setWidth(12);
				$sugestedItems->getColumnDimension('B')->setWidth(40);
				$sugestedItems->getColumnDimension('C')->setWidth(12);
				$sugestedItems->getColumnDimension('D')->setWidth(12);
				$sugestedItems->getColumnDimension('E')->setWidth(12);
				$sugestedItems->getColumnDimension('F')->setWidth(12);
				$sugestedItems->getColumnDimension('G')->setWidth(12);
				$sugestedItems->getColumnDimension('H')->setWidth(12);
				$sugestedItems->getColumnDimension('I')->setWidth(12);
				$sugestedItems->getColumnDimension('J')->setWidth(12);
				$sugestedItems->getColumnDimension('K')->setWidth(12);
				$sugestedItems->getColumnDimension('L')->setWidth(12);
				$sugestedItems->getColumnDimension('M')->setWidth(12);
				$sugestedItems->getColumnDimension('N')->setWidth(12);

				$row =1;

							
				foreach($reporte as $item)
				{
					$row++;
					$sugestedItems->setCellValueExplicit('A'.$row, $item->ItemCode,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
					$sugestedItems->setCellValueExplicit('B'.$row, $item->Descripcion,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
					$sugestedItems->setCellValue('C'.$row, $item->AvgPrice);
					$sugestedItems->setCellValue('D'.$row, $item->InvInicial);
					$sugestedItems->setCellValue('E'.$row, $item->CompraDirecta);
					$sugestedItems->setCellValue('F'.$row, $item->TransferIn);
					$sugestedItems->setCellValue('G'.$row, $item->Merma);
					$sugestedItems->setCellValue('H'.$row, $item->Comida);
					$sugestedItems->setCellValue('I'.$row, $item->TransferOut);
					$sugestedItems->setCellValue('J'.$row, $item->CostoVenta);
					$sugestedItems->setCellValue('K'.$row, $item->VentaTeorica);
					$sugestedItems->setCellValue('L'.$row, $item->InvFinal);
					$sugestedItems->setCellValue('M'.$row, $item->Conteo);
					$sugestedItems->setCellValue('N'.$row, $item->InvFinalTeorico);
					$sugestedItems->getStyle('A'.$row.':N'.$row)
					->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
				}

			}
			else
			{

				$fecha= $data["fecha"];
				$fechaIni = date("Y-m", strtotime($fecha))."-01";
				$sucursales = DB::select("SELECT idMicros, idSap FROM sucursales WHERE estado = 1 AND idEmpresa = 1;",[]);
				$spreadsheet = new Spreadsheet();
				$sugestedItems = $spreadsheet->getActiveSheet();
				$sugestedItems->setTitle('Costo');

				$sugestedItems->setCellValue('A1', 'Sucursal');
				$sugestedItems->setCellValue('B1', 'Codigo');
				$sugestedItems->setCellValue('C1', 'Descripcion');
				$sugestedItems->setCellValue('D1', 'Precio Promedio');
				$sugestedItems->setCellValue('E1', 'Inv Inicial');
				$sugestedItems->setCellValue('F1', 'Compras');
				$sugestedItems->setCellValue('G1', 'Transferencia In');
				$sugestedItems->setCellValue('H1', 'Merma');
				$sugestedItems->setCellValue('I1', 'Comida Personal');
				$sugestedItems->setCellValue('J1', 'Transferencia Out');
				$sugestedItems->setCellValue('K1', 'Costo de Venta');
				$sugestedItems->setCellValue('L1', 'Venta Teorica');
				$sugestedItems->setCellValue('M1', 'Inv Final');
				$sugestedItems->setCellValue('N1', 'Conteo');
				$sugestedItems->setCellValue('O1', 'Inv Teorico');
		
				$sugestedItems->getColumnDimension('A')->setWidth(12);
				$sugestedItems->getColumnDimension('B')->setWidth(12);
				$sugestedItems->getColumnDimension('C')->setWidth(40);
				$sugestedItems->getColumnDimension('D')->setWidth(12);
				$sugestedItems->getColumnDimension('E')->setWidth(12);
				$sugestedItems->getColumnDimension('F')->setWidth(12);
				$sugestedItems->getColumnDimension('G')->setWidth(12);
				$sugestedItems->getColumnDimension('H')->setWidth(12);
				$sugestedItems->getColumnDimension('I')->setWidth(12);
				$sugestedItems->getColumnDimension('J')->setWidth(12);
				$sugestedItems->getColumnDimension('K')->setWidth(12);
				$sugestedItems->getColumnDimension('L')->setWidth(12);
				$sugestedItems->getColumnDimension('M')->setWidth(12);
				$sugestedItems->getColumnDimension('N')->setWidth(12);
				$sugestedItems->getColumnDimension('O')->setWidth(12);

				$row =1;

				foreach($sucursales as $sucursal)
				{
					$SucMicros = $sucursal->idMicros;
					$SucSap= $sucursal->idSap;
	
					if($detalle)
						$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , INMD.AvgPrice, INMD.InvInicial, INMD.InvInicialCosto, INMD.CompraDirecta, INMD.CompraDirectaCosto, INMD.TransferIn, INMD.TransferInCosto, INMD.Merma, INMD.MermaCosto, INMD.Comida, INMD.ComidaCosto, INMD.TransferOut, INMD.TransferOutCosto, INMD.CostoVenta, INMD.CostoVentaCosto, INMD.InvFinal, INMD.InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ) AS VentaTeorica, (IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )* INMD.AvgPrice) AS VentaTeoricaCosto, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) InvFinalTeorico, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) * INMD.AvgPrice InvFinalTeoricoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) * INMD.AvgPrice AS ConteoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
					else
						$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(paph.PRECIO,2) AvgPrice, ROUND(INMD.InvInicial,2) InvInicial, INMD.CompraDirecta, INMD.TransferIn, INMD.Merma, INMD.Comida, ROUND(INMD.TransferOut,2) TransferOut , ROUND(INMD.CostoVenta,2) CostoVenta, ROUND(INMD.InvFinal,2) InvFinal, IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN	pedidos_articulo_precio_historico paph ON (MONTH(INMD.fecha) = paph.mes AND YEAR(INMD.fecha) = paph.anio AND INMD.ItemCode = paph.ItemCode ) INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
						#$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, INMD.InvInicial, ROUND(INMD.InvInicialCosto,2) InvInicialCosto, (INMD.CompraDirecta + INMD.TransferIn) entradas, (INMD.Merma + INMD.Comida + INMD.TransferOut) salidas, ROUND(INMD.CostoVenta,2) CostoVenta, INMD.InvFinal, ROUND(INMD.InvFinalCosto,2) InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
					
					$reporte = DB::select($sql);		
								
					foreach($reporte as $item)
					{
						$row++;
						$sugestedItems->setCellValueExplicit('A'.$row, $item->idSucSap,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
						$sugestedItems->setCellValueExplicit('B'.$row, $item->ItemCode,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
						$sugestedItems->setCellValueExplicit('C'.$row, $item->Descripcion,	\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
						$sugestedItems->setCellValue('D'.$row, $item->AvgPrice);
						$sugestedItems->setCellValue('E'.$row, $item->InvInicial);
						$sugestedItems->setCellValue('F'.$row, $item->CompraDirecta);
						$sugestedItems->setCellValue('G'.$row, $item->TransferIn);
						$sugestedItems->setCellValue('H'.$row, $item->Merma);
						$sugestedItems->setCellValue('I'.$row, $item->Comida);
						$sugestedItems->setCellValue('J'.$row, $item->TransferOut);
						$sugestedItems->setCellValue('K'.$row, $item->CostoVenta);
						$sugestedItems->setCellValue('L'.$row, $item->VentaTeorica);
						$sugestedItems->setCellValue('M'.$row, $item->InvFinal);
						$sugestedItems->setCellValue('N'.$row, $item->Conteo);
						$sugestedItems->setCellValue('O'.$row, $item->InvFinalTeorico);
						$sugestedItems->getStyle('A'.$row.':O'.$row)
						->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
					}
	
				}
				$SucSap = "Global";
			}
			
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="COSTO_'.$SucSap.'_'.$fecha.'.xlsx"');
			$writer->save("php://output");

		}
		else if(!empty($data['fecha']))
		{
			$fecha= $data["fecha"];
			$fechaIni = date("Y-m", strtotime($fecha))."-01";

			if($detalle)
				$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , INMD.AvgPrice, INMD.InvInicial, INMD.InvInicialCosto, INMD.CompraDirecta, INMD.CompraDirectaCosto, INMD.TransferIn, INMD.TransferInCosto, INMD.Merma, INMD.MermaCosto, INMD.Comida, INMD.ComidaCosto, INMD.TransferOut, INMD.TransferOutCosto, INMD.CostoVenta, INMD.CostoVentaCosto, INMD.InvFinal, INMD.InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ) AS VentaTeorica, (IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )* INMD.AvgPrice) AS VentaTeoricaCosto, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) InvFinalTeorico, (INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad )) * INMD.AvgPrice InvFinalTeoricoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) * INMD.AvgPrice AS ConteoCosto, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			else
				$sql= "SELECT INMD.ItemCode, ARTS.Descripcion , ROUND(AVG(INMD.AvgPrice),2) AvgPrice, ROUND(SUM(INMD.InvInicial),2) InvInicial, SUM(INMD.CompraDirecta) CompraDirecta , SUM(INMD.TransferIn) TransferIn, SUM(INMD.Merma) Merma, SUM(INMD.Comida) Comida, ROUND(SUM(INMD.TransferOut),2) TransferOut , ROUND(SUM(INMD.CostoVenta),2) CostoVenta, ROUND(SUM(INMD.InvFinal),2) InvFinal, IF(SUM(VENTAM.cantidad) IS NULL, 0, ROUND(SUM(VENTAM.cantidad),2) ) AS VentaTeorica, ROUND(SUM(INMD.InvInicial) + SUM(INMD.CompraDirecta) + SUM(INMD.TransferIn)  + SUM(INMD.EntDev) - SUM(INMD.Merma) - SUM(INMD.Comida) - SUM(INMD.TransferOut) - IF(SUM(VENTAM.cantidad) IS NULL, 0,SUM(VENTAM.cantidad)),2) InvFinalTeorico, IF(SUM(INVCONT.cantidad) IS NULL, 0,SUM(INVCONT.cantidad)) AS Conteo, IF(SUM(INVCONT.cantidad) IS NULL, 0,SUM(INVCONT.cantidad))- IF(SUM(INMD.InvFinal) IS NULL, 0 ,SUM(INMD.InvFinal)) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE  VMPM.fecha='$fecha' GROUP BY RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente) LEFT JOIN (SELECT PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE  INV.fecha = '$fecha' GROUP BY PA.CodPrigo) INVCONT ON (INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' GROUP BY  INMD.ItemCode, ARTS.Descripcion ORDER BY INMD.ItemCode";
				#$sql= "SELECT INMD.idSucSap , INMD.ItemCode, ARTS.Descripcion , ROUND(INMD.AvgPrice,2) AvgPrice, INMD.InvInicial, ROUND(INMD.InvInicialCosto,2) InvInicialCosto, (INMD.CompraDirecta + INMD.TransferIn) entradas, (INMD.Merma + INMD.Comida + INMD.TransferOut) salidas, ROUND(INMD.CostoVenta,2) CostoVenta, INMD.InvFinal, ROUND(INMD.InvFinalCosto,2) InvFinalCosto , IF(VENTAM.cantidad IS NULL, 0, ROUND(VENTAM.cantidad,2) ) AS VentaTeorica, ROUND(INMD.InvInicial + INMD.CompraDirecta + INMD.TransferIn  + INMD.EntDev - INMD.Merma - INMD.Comida - INMD.TransferOut - IF(VENTAM.cantidad IS NULL, 0,VENTAM.cantidad ),2) InvFinalTeorico, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad ) AS Conteo, IF(INVCONT.cantidad IS NULL, 0,INVCONT.cantidad )- IF(INMD.InvFinal IS NULL, 0 ,INMD.InvFinal) Diferencia FROM inventario_mensual_detalle INMD INNER JOIN sucursales ON sucursales.idSap = INMD.idSucSap LEFT JOIN ( SELECT pedidos_articulo.CodPrigo, MAX(pedidos_articulo.Descripcion) Descripcion FROM pedidos_articulo GROUP BY pedidos_articulo.CodPrigo ) ARTS ON (ARTS.CodPrigo = INMD.ItemCode) LEFT JOIN (SELECT Cantidades.idSucMicros ,Cantidades.idIngrediente,PEDA.Descripcion, Cantidades.cantidad, PEDA.precioPromedio * Cantidades.cantidad AS Costo FROM (SELECT VMPM.idSucMicros ,RPI.idIngrediente, SUM(VMPM.cantidad * RPI.cantidadSucia) cantidad FROM (SELECT idSucMicros, idItemMicros, '$fecha' AS fecha,SUM(cantidad) cantidad  FROM venta_mes_producto_micros WHERE idSucMicros='$SucMicros' AND  fecha BETWEEN '$fechaIni' AND '$fecha' GROUP BY  idSucMicros , idItemMicros ) AS VMPM INNER JOIN recetas_platillo RP ON RP.nombre = VMPM.idItemMicros INNER JOIN recetas_platillo_ingrediente RPI ON RPI.idPlatillo = RP.idPlatillo WHERE VMPM.idSucMicros='$SucMicros' AND VMPM.fecha='$fecha' GROUP BY VMPM.idSucMicros, RPI.idIngrediente ) Cantidades INNER JOIN pedidos_articulo PEDA ON PEDA.CodPrigo = Cantidades.idIngrediente WHERE PEDA.idProveedor = 5 AND PEDA.idCompania IN (1,3) ) VENTAM ON (INMD.ItemCode = VENTAM.idIngrediente AND VENTAM.idSucMicros = sucursales.idMicros ) LEFT JOIN (SELECT SUCS.idMicros, PA.CodPrigo, SUM(INVP.cantidad) cantidad FROM inventario INV INNER JOIN inventario_partida INVP ON INV.idInventario = INVP.idInventario INNER JOIN pedidos_articulo PA ON PA.idArticulo= INVP.idArticulo INNER JOIN sucursales SUCS ON SUCS.id = INV.idSucursal WHERE SUCS.idSap = '$SucSap' AND INV.fecha = '$fecha'  GROUP BY SUCS.idMicros ,PA.CodPrigo) INVCONT ON (INVCONT.idMicros = sucursales.idMicros AND INVCONT.CodPrigo = INMD.ItemCode ) WHERE INMD.idSucSap = '$SucSap' AND INMD.fecha = '$fecha' AND ARTS.Descripcion LIKE '%".$query."%' ORDER BY INMD.idSucSap , INMD.ItemCode";
			
			$reporte = DB::select($sql);

			return  response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte,
				'sql' => $sql
			]);
		}
		else
		{

			return  response()->json([
				'draw' => $draw,
				'recordsTotal' => count($reporte),
				'recordsFiltered' => count($reporte),
				'data' => $reporte
			]);

		}
	}

}