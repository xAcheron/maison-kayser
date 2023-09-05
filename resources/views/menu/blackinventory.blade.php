@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: #ff9800;
	}   
</style>
@if(!empty(session('BIRole')) && (session('BIRole') == 1 ))
<li class="nav-item active">
	<a class="nav-link"  aria-expanded="true">
		<i class="material-icons">swap_vertical_circle</i>
		<p> Inventario Negro <b class="caret"></b> </p>
    </a>
	<div class="collapse show">
		<ul class="nav">
            <li class="nav-item @if($seccion == 'agregarEntradaSalida') active @endif">
				<a class="nav-link"  href="{{ route('formRequest') }}">
					<i class="material-icons">import_export</i>
					<p> Agregar Entrada / Salida</p>
				</a>
            </li>
            <li class="nav-item @if($seccion == 'consultarReportes') active @endif">
				<a class="nav-link"  href="{{ route('formReports') }}">
					<i class="material-icons">content_paste</i>
					<p> Consultas / Reportes</p>
				</a>
			</li>  
			<li class="nav-item @if($seccion == 'agregarMaterial') active @endif">
				<a class="nav-link" href="{{ route('formMaterial') }}">
					<i class="material-icons">move_to_inbox</i>
					<p> Agregar Material</p>
				</a>
			</li> 
			<li class="nav-item @if($seccion == 'agregarTpMaterial') active @endif">
				<a class="nav-link" href="{{ route('formTpMat') }}">
					<i class="material-icons">playlist_add</i>
					<p> Agregar Tipo de Material</p>
				</a>
			</li>
            <li class="nav-item @if($seccion == 'agregarUnidadMedida') active @endif">
				<a class="nav-link"  href="{{ route('formUnit') }}">
					<i class="material-icons">edit_attributes</i>
					<p> Agregar UM</p>
				</a>
            </li>
			<li class="nav-item @if($seccion == 'agregarAlmacen') active @endif">
				<a class="nav-link" href="{{ route('formWarehouse') }}">
					<i class="material-icons">house_siding</i>
					<p> Agregar Almacén</p>
				</a>
            </li> 
            <li class="nav-item @if($seccion == 'agregarUbicacion') active @endif">
				<a class="nav-link" href="{{ route('formUbication') }}">
					<i class="material-icons">location_on</i>
					<p> Agregar Ubicación</p>
				</a>
            </li> 
		</ul>
	</div>
</li>
@elseif(!empty(session('BIRole')) && (session('BIRole') == 2 ))
<li class="nav-item active">
	<li class="nav-item @if($seccion == 'consultarReportes') active @endif">
		<a class="nav-link"  href="{{ route('formConsultMats') }}">
			<i class="material-icons">content_paste</i>
			<p> Consulta</p>
		</a>
	</li>  
</li>
@endif
@endsection
