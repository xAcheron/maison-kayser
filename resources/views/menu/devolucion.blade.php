@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: #ff9800;
	}
</style>
<li class="nav-item active">
	<a class="nav-link" href="{{ route('devolucion') }}" aria-expanded="true">
		<i class="material-icons">assignment_return</i>
		<p> Devoluciones <b class="caret"></b> </p>
    </a>
	<div class="collapse show" style="">
		<ul class="nav">
			@if(!empty($role) && ($role != '10') || ($role =='1'))
			<li class="nav-item @if($seccion == 'nuevaDevolucion') active @endif">
				<a class="nav-link" href="{{ route('nuevaDevolucion') }}">
					<i class="material-icons">shopping_basket</i>
					<p> Crear Solicitud</p>
				</a>
			</li>

			<li class="nav-item @if($seccion == 'consultaMisDevoluciones') active @endif">
				<a class="nav-link" href="{{ route('misDevoluciones') }}">
					<i class="material-icons">view_list</i>
					<p> Consultar </p>
				</a>
			</li> 
			@endif          
			{{-- <li class="nav-item @if($seccion == 'consultaDevoluciones') active @endif">
				<a class="nav-link" href="{{ route('consultaManto') }}">
					<i class="material-icons">view_list</i>
					<p> Autorizaciones </p>
				</a>
			</li>  --}}
			
			@if(!empty($role) && ($role == '10' ))           
			<li class="nav-item @if($seccion == 'consultaDevolucionesAlmacen') active @endif">
				<a class="nav-link" href="{{ route('consultaAlmacen') }}">
					<i class="material-icons">domain</i>
					<p> Almacén </p>
				</a>
			</li>
			@endif
		</ul>
	</div>
</li>
@endsection
