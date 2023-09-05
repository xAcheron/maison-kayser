@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" data-toggle="collapse" href="{{ route('recetas') }}" aria-expanded="true" >
		<i class="material-icons">local_dining</i>
		<p> Recetario </p>
    </a>
	<div class="collapse show">
		<ul class="nav">
			<li class="nav-item @if($seccion == 'receta') active @endif ">
				<a class="nav-link" href="{{ route('recetaslista') }}">
					<i class="material-icons">shopping_basket</i>
					<p> Recetas</p>
				</a>
			</li>
			<li class="nav-item @if($seccion == 'localizar') active @endif">
				<a class="nav-link" href="{{ route('localizar') }}">
					<i class="material-icons">view_list</i>
					<p> Localizar </p>
				</a>
			</li>
			<li class="nav-item @if($seccion == 'autorizar') active @endif">
				<a class="nav-link" href="{{ route('autorizar') }}">
					<i class="material-icons">done_all</i>
					<p> Autorizar / Editar</p>
				</a>
			</li>
			<li class="nav-item @if($seccion == 'ingredientes') active @endif">
				<a class="nav-link" href="{{ route('consultaringredientes') }}">
					<i class="material-icons">image_search</i>
					<p> Consultar ingredientes </p>
				</a>
			</li>
			<li class="nav-item @if($seccion == 'linkMicros') active @endif">
				<a class="nav-link" href="{{ route('linkMicros') }}">
					<i class="material-icons">add_link</i>
					<p> Link Micros </p>
				</a>
			</li>
			<li class="nav-item @if($seccion == 'extras') active @endif">
				<a class="nav-link" href="{{ route('consultarextras') }}">
					<i class="material-icons">note_add</i>
					<p> Extras </p>
				</a>
			</li>			
		</ul>
	</div>
</li>
@endsection