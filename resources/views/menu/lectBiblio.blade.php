@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: #ff9800;
	}   
</style>
<li class="nav-item active">
	<a class="nav-link"  aria-expanded="true">
		<i class="material-icons">groups</i>
		@if(!empty($role) && ($role == '1'))
		<p> Admin biblioteca</p>
		@else
		<p> Biblioteca</p>
		@endif
    </a>
	<div class="collapse show">
		<ul class="nav">
            <li class="nav-item @if($seccion == 'visualizarArchivos') active @endif">
				<a class="nav-link"  href="{{ route('verArchivosAdminBiblioteca') }}">
					<i class="material-icons">book</i>
					<p>Biblioteca</p>
				</a>
            </li>
        </ul>
		@if(!empty($role) && ($role == '1'))
        <ul class="nav">
            <li class="nav-item @if($seccion == 'agregarArchivos') active @endif">
				<a class="nav-link"  href="{{ route('agregarArchivosAdminBiblioteca') }}">
					<i class="material-icons">create_new_folder</i>
					<p>Agregar archivos</p>
				</a>
            </li>
		</ul>
		@endif
	</div>
</li>
@endsection


@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" href="#" aria-expanded="true" >
		<i class="material-icons">menu_book</i>
		<p> Biblioteca <b class="caret"></b> </p>
    </a>
	<div class="collapse show" id="pagesExamples" style="">
		<ul class="nav">
			<li class="nav-item @if($seccion == 'lectorBivlioteca') active @endif ">
				<a class="nav-link" href="{{ route('recetaslista') }}">
					<i class="material-icons">book</i>
					<p> Procesos</p>
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
			
			
		</ul>
	</div>
</li>
@endsection