@extends('layouts.app')

@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" href="{{ route('vacantes') }}" aria-expanded="true">
		<i class="material-icons">transfer_within_a_station</i>
		<p> Vacantes RH <b class="caret"></b> </p>
    </a>
	<div class="collapse show">
		<ul class="nav">
			<li class="nav-item">
				<a class="nav-link" href="{{ route('nuevavacante') }}">
					<i class="material-icons">book</i>
					<p> Solicitud de Personal </p>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="{{ route('consultavacantes') }}">
					<i class="material-icons">view_list</i>
					<p> Consulta de solicitudes </p>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="{{ route('consultacontratados') }}">
					<i class="material-icons">view_list</i>
					<p> Consulta de Contrataciones</p>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="{{ route('plantilla') }}">
					<i class="material-icons">assignment</i>
					<p>Plantilla</p>
				</a>
			</li>
		</ul>
	</div>
</li>
@endsection

@section('content')
<div class="row">
  <label class="col-sm-1 col-form-label">Puesto</label>
  <div class="col-sm-3">
  		<div class="form-group bmd-form-group">
		  <input id="findPuesto" type="text" class="form-control" >
		</div>
  </div>
  <label class="col-sm-1 col-form-label">Sucursal</label>
  <div class="col-sm-3">
  		<div class="form-group bmd-form-group">
		  <input id="findSucursal" type="text" class="form-control">
		</div>
  </div>
    <div class="col-sm-1">
	  <button id="findVacantebtn" class="btn btn-white btn-round btn-just-icon">
		<i class="material-icons">search</i>
		<div class="ripple-container"></div>
	  </button>
	</div>
</div>
<div class="row">
<table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
	<thead>
	  <tr>
		  <th>Solicitud</th>
		  <th>Fecha</th>
		  <th>Sucursal</th>		  
		  <th>Puesto</th>
		  <th>Estado</th>
		  <th>Reclutador</th>
		  <th>Estado</th>
		  <th>Acci&oacute;n</th>
	  </tr>
	</thead>
	<tfoot>
	  <tr>
		  <th>Solicitud</th>
		  <th>Fecha</th>
		  <th>Sucursal</th>		  
		  <th>Puesto</th>
		  <th>Estado</th>
		  <th>Reclutador</th>
		  <th>Estado</th>
		  <th>Acci&oacute;n</th>
	  </tr>
	</tfoot>
	<tbody>
	</tbody>
</table>
</div>
@endsection
@section('aditionalScripts')
  <script type="text/javascript">

$(document).ready(function() {
    $('#datatables').DataTable({
        "responsive": true,	
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ route('getSolicitudesC') }}",
            "type": "POST",
			"data": function ( d ) {
                d._token = "{{ csrf_token() }}";
            }
        },
        "columns": [
            { "data": "idSolicitud" },
            { "data": "fechaCrea" },
            { "data": "sucursal" },
            { "data": "puesto" },
            { "data": "solicitud" },
            { "data": "reclutador" },
            { "data": "estado" },
			{ "render": function (data, type, row, meta) {
				return "<a href=\"{{ route('detallevacante') }}/"+row.idSolicitud+"\" class=\"btn btn-link btn-info btn-just-icon like\"><i class=\"material-icons\">open_in_new</i></a>";
				}
			}
        ]
    });

    var table = $('#datatables').DataTable();
 
	$('#findVacantebtn').on( 'click', function () {
		if($("#findSucursal").val() != "")
			table.column(2).search( $("#findSucursal").val() );
		else
			table.column(2).search("");
		if($("#findPuesto").val() != "")
			table.column(3).search( $("#findPuesto").val() );
		else
			table.column(3).search("");
		if($("#findSucursal").val() != "" || $("#findPuesto").val() != "")
			table.draw();
	} );
	$("#datatables_filter").hide();
});

</script>

@endsection