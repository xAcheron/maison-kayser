@extends('layouts.app')
@include('menu.vacantes', ['seccion' => 'consultavacantes'])
@section('content')
<div class="row">
	<div class="card">
		<div class="card-header">
			<h4 class="card-title">Solicitud # {{ $solicitud->idSolicitud }}</h4>
			<div class="row">
				<label class="col-sm-2 col-form-label"><b>Solicita</b></label>
				<div class="col-sm-2 col-form-label">{{ $solicitud->nombre }}</div>			
				<label class="col-sm-2 col-form-label"><b>Fecha</b></label>				
				<div class="col-sm-2 col-form-label">{{ $solicitud->fecha }}</div>			
			</div>
			<div class="row">
					<label class="col-sm-2 col-form-label"><b>Comentario</b></label>
					<div class="col-sm-6 col-form-label">{{ $solicitud->comentario }}</div>	
				</div>
        </div>
		<div class="card-body ">
			<div class="row">
				<div class="col-sm-8">
				@foreach($partidas as $partida)
					<div class="row" style="margin-top: 5px;">
						<label class="col-sm-2 col-form-label">Sucursal</label>				
						<div class="col-sm-2 col-form-label">{{ $partida->sucursal }}</div>
						<label class="col-sm-1 col-form-label">Puesto</label>				
						<div class="col-sm-3 col-form-label">{{ $partida->puesto }}</div>
						<label class="col-sm-2 col-form-label">Solicitud</label>				
						<div class="col-sm-2 col-form-label">{{ $partida->solicitud }}</div>
						<label class="col-sm-2 col-form-label">Referencia</label>				
						<div class="col-sm-2 col-form-label">{{ $partida->nombre }}</div>
						@if(!empty($partida->contratado))
						<label class="col-sm-2 col-form-label">Contratado</label>				
						<div class="col-sm-2 col-form-label">{{ $partida->contratado }}</div>
						@endif
						<label class="col-sm-1 col-form-label">Estado</label>				
						<div class="col-sm-3 col-form-label">{{ $partida->estado }}</div>	
					</div>
					<div class="row" style="border-bottom: #000 1px solid;padding-bottom: 5px;">
					<label class="col-sm-2 col-form-label">Actualizar</label>
						<div class="col-sm-4">
							@if($partida->idEstado != 5 && $partida->idEstado != 6 )
							<select id="accion_{{ $partida->idPartida }}" onchange="validAccion({{ $partida->idPartida }},this.value)"  name="accion_{{ $partida->idPartida }}" class="selectpicker" data-style="btn select-with-transition" title="Seleccione un estado" data-size="7" tabindex="-98">
								@if($role !=3 && $role != 4)
								<option value="1">Pendiente</option>
								<option value="2">Citado</option>
								<option value="3">Entrega Documentos</option>
								<option value="4">Contratado</option>
								<option value="5">Cancelado</option>
								@endif
								@if($partida->idEstado == 9 && ($role==3 || $role==1 ))
								<option value="6">Confirmado</option>
								<option value="7">No se presento</option>
								@elseif($partida->idEstado == 4 && ($role==4 || $role==1 ))
								<option value="9">Confirmado Inducción</option>
								<option value="7">No se presento</option>
								@endif
							</select>
							@endif
						</div>
						<div class="col-sm-4">
						</div>
						<div class="col-sm-2">
							<button type="button" onclick="guardaEstado({{ $partida->idPartida }});" id="saveBtn_{{ $partida->idPartida }}" class="btn btn-info btn-sm">Guardar</button>
						</div>
						<label style="display: none" class="col-sm-2 col-form-label" id="lbl-reem-{{ $partida->idPartida }}">Empleado</label>
						<div  style="display: none" class="col-sm-6" id="reem-{{ $partida->idPartida }}"><input type="text" class="form-control" /></div>

						<label style="display: none" class="col-sm-2 col-form-label" id="lbl-fecent-{{ $partida->idPartida }}">Fecha Ingreso</label>
						<div  style="display: none" class="col-sm-6" id="fecent-{{ $partida->idPartida }}"><input type="text" class="form-control datepicker" value="<?php echo date("Y-m-d"); ?>"/></div>
					</div>		
					@endforeach
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('aditionalScripts')
<script type="text/javascript">
	$('.datepicker').datetimepicker({
		format: 'YYYY-MM-DD',
		icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down",
				previous: 'fa fa-chevron-left',
				next: 'fa fa-chevron-right',
				today: 'fa fa-screenshot',
				clear: 'fa fa-trash',
				close: 'fa fa-remove'
		}
	});
function guardaEstado(partida){
	var accion = $("#accion_"+partida).val();
	var solicitud = "{{ $solicitud->idSolicitud }}";
	var _token ="{{ csrf_token() }}";
	if(accion==''){
		swal({
		  type: 'error',
		  title: 'Oops...',
		  text: 'Seleccione un estado a aplicar!',
		  footer: 'Problemas? sit@prigo.com.mx',
		});
	}
	else
	{
		swal({
			title: "Estas segur@?",
			text: "Se aplicara el estado a esta vacante!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			allowOutsideClick: false,
			confirmButtonText: 'Si, actualizar!',
			cancelButtonText: 'No, cancelar!'
		}).then((result) => {
		  if (result) {
			  $('.button').prop('disabled', true);
			swal({
			  title: 'Guardando...',
			  allowEscapeKey: false,
			  allowOutsideClick: false,
			  showCancelButton: false,
			  showConfirmButton: false,
			  text: 'Espere un momento...'
			});
			
			$.ajax({
				type: "POST",
				url: "{{ route('guardavacante') }}",
				data: { "_token": _token ,"accion": accion, "partida": partida, "solicitud": solicitud, "empleado": $("#reem-"+partida+" .form-control").val(), "fingreso": $("#fecent-"+partida+" .form-control").val()},
				success: function(msg){
					swal({
						type: 'success',
						title: 'Tu vacante se ha actualizado!'
					});
					
					$('.button').prop('disabled', false);
					
				},
				error: function(){
					swal({
					  type: 'error',
					  title: 'Oops...',
					  text: 'Algo ha salido mal!',
					  footer: 'Problemas? sit@prigo.com.mx	',
					});
					$('.button').prop('disabled', false);
				}
			});	
			
		  }
		});
	}
}

function validAccion(partida,valor)
{

	$("#reem-"+partida+" .form-control").val('');
	
	if(valor == 4)
	{
		$("#lbl-reem-"+partida).show();
		$("#reem-"+partida).show();
	}
	else if(valor == 9)
	{
		$("#lbl-fecent-"+partida).show();
		$("#fecent-"+partida).show();
	}
	else
	{
		$("#lbl-reem-"+partida).hide();
		$("#reem-"+partida).hide();
		$("#lbl-fecent-"+partida).hide();
		$("#fecent-"+partida).hide();
	}
}
</script>
@endsection