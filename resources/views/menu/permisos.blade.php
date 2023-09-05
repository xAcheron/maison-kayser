@section('appmenu')
    <style>
        .sidebar .nav li.active>[data-toggle="collapse"] {
            background-color: #ff9800;
            color: #3C4858;
            box-shadow: none;
        }
    </style>
    <li class="nav-item active">
        <a class="nav-link" data-toggle="collapse" href="#pagesExamples" aria-expanded="true">
            <i class="material-icons">manage_accounts</i>
            <p> Permisos <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('permisos') }}">
                        <i class="material-icons">admin_panel_settings</i>
                        <p> Administrador de usuarios </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('permisosReportes') }}">
                        <i class="material-icons">summarize</i>
                        <p>Permisos reportes</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('ligarUsersEmp') }}">
                        <i class="material-icons">summarize</i>
                        <p>Users - Empleados</p>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endsection
