@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: white;
	}   
</style>
<li class="nav-item active">
	<a class="nav-link"  aria-expanded="true">
		<i class="material-icons">assignment</i>
		<p> Menú Sucursales <b class="caret"></b> </p>
    </a>
	<div class="collapse show" style="">
		<ul class="nav">
			<li class="nav-item @if($seccion == 'nuevoMenu') active @endif">
				<a class="nav-link" href="{{ route('nuevoMenu') }}">
					<i class="material-icons">view_list</i>
					<p> Agregar Menú</p>
				</a>
			</li>

			<li class="nav-item @if($seccion == 'nuevaSeccion') active @endif">
				<a class="nav-link" href="{{ route('nuevaSeccion') }}">
					<i class="material-icons">view_list</i>
					<p> Agregar Sección</p>
				</a>
            </li> 

            <li class="nav-item @if($seccion == 'nuevaSubseccion') active @endif">
				<a class="nav-link" href="{{ route('nuevaSubseccion') }}">
					<i class="material-icons">view_list</i>
					<p> Agregar Subsección</p>
				</a>
            </li> 

            <li class="nav-item @if($seccion == 'nuevoPlatillo') active @endif">
				<a class="nav-link" href="{{ route('nuevoPlatillo') }}" ">
					<i class="material-icons">view_list</i>
					<p> Agregar platillos</p>
				</a>
            </li> 

            <li class="nav-item @if($seccion == 'asignMenu') active @endif">
				<a class="nav-link" href="{{ route('asignMenu') }}" ">
					<i class="material-icons">view_list</i>
					<p> Asignar Menú - Sucursal</p>
				</a>
            </li> 
		</ul>
	</div>
</li>
@endsection
