@section('appmenu')
    <style>
        .sidebar .nav li.active>a,
        .sidebar .nav li.active>a i {
            color: #ff9800;
        }
    </style>
    <li class="nav-item active">
        <a class="nav-link" aria-expanded="true">
            <i class="material-icons">groups</i>
            @if (!empty($role) && $role == '1')
                <p> Admin biblioteca</p>
            @else
                <p> Biblioteca</p>
            @endif
        </a>
        <div class="collapse show">
            <ul class="nav">
                <li class="nav-item @if ($seccion == 'visualizarArchivos') active @endif">
                    <a class="nav-link" href="{{ route('verArchivosAdminBiblioteca') }}">
                        <i class="material-icons">book</i>
                        <p>Biblioteca</p>
                    </a>
                </li>
            </ul>
            @if (!empty($role) && $role == '1')
                <ul class="nav">
                    <li class="nav-item @if ($seccion == 'agregarArchivos') active @endif">
                        <a class="nav-link" href="{{ route('agregarArchivosAdminBiblioteca') }}">
                            <i class="material-icons">create_new_folder</i>
                            <p>Agregar archivos</p>
                        </a>
                    </li>
                </ul>
            @endif
        </div>
    </li>
@endsection
