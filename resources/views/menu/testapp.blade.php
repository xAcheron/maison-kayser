@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: #ff9800;
	}   
</style>
<li class="nav-item active">
	<a class="nav-link"  aria-expanded="true">
		<i class="material-icons">assignment</i>
		<p> Test Apps Intranet <b class="caret"></b> </p>
    </a>
	<div class="collapse show" style="">
		<ul class="nav">
			<li class="nav-item @if($seccion == 'nuevoMenuTest') active @endif">
				<a class="nav-link" href="{{ route('nuevoMenuTest') }}">
					<i class="material-icons">view_list</i>
					<p> Agregar Menú </p>
				</a>
			</li>

			<li class="nav-item @if($seccion == 'nuevoPlatilloTest') active @endif">
				<a class="nav-link" href="{{ route('nuevoPlatilloTest') }}">
					<i class="material-icons">view_list</i>
					<p> Agregar Platillo  </p>
				</a>
            </li> 

            <li class="nav-item @if($seccion == 'nuevaAsignacionTest') active @endif">
				<a class="nav-link" href="{{ route('nuevaAsignacionTest') }}">
					<i class="material-icons">view_list</i>
					<p> Asignar Menú - Sucursal </p>
				</a>
            </li> 
		</ul>
	</div>
</li>
@endsection
