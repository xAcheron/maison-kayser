@section('appmenu')
    <style>
        .sidebar .nav li.active>a,
        .sidebar .nav li.active>a i {
            color: #ff9800;
        }
    </style>
    <li class="nav-item active">
        <a class="nav-link" aria-expanded="true">
            <i class="material-icons">tablet</i>
            <p> Checklist Operativo</p>
        </a>
        <div class="collapse show">
            <ul class="nav">
                {{-- <li class="nav-item @if ($seccion == 'agregarCheckList') active @endif">
				<a class="nav-link"  href="{{ route('formCheckListOperative') }}">
					<i class="material-icons">playlist_add_check</i>
					<p>Agregar Checklist</p>
				</a>
            </li>
            <li class="nav-item @if ($seccion == 'agregarSecciones') active @endif">
				<a class="nav-link"  href="{{ route('formSucCheckListOperative') }}">
					<i class="material-icons">fact_check</i>
					<p>Agregar Secciones</p>
				</a>
			</li>
			<li class="nav-item @if ($seccion == 'agregarItems') active @endif">
				<a class="nav-link"  href="{{ route('fromItemsChecks') }}">
					<i class="material-icons">list</i>
					<p>Agregar Items </p>
				</a>
			</li> --}}
                <li class="nav-item @if ($seccion == 'visualizarChecklist') active @endif">
                    <a class="nav-link" href="{{ route('formViewChecklistOperative') }}">
                        <i class="material-icons">preview</i>
                        <p>Visualizar Checklist </p>
                    </a>
                </li>
                {{-- <li class="nav-item @if ($seccion == 'evaluaciones') active @endif">
                    <a class="nav-link" href="{{ route('evaluaciones') }}">
                        <i class="material-icons">groups</i>
                        <p>Evaluaciones</p>
                    </a>
                </li> --}}
                {{-- <li class="nav-item @if ($seccion == 'visualizarResultados') active @endif">
				<a class="nav-link"  href="{{ route('formResultsChecks') }}">
					<i class="material-icons">description</i>
					<p>Visualizar Resultados </p>
				</a>
            </li> --}}
            </ul>
        </div>
    </li>
@endsection
