@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('manto') }}" aria-expanded="true">
            <i class="material-icons">build</i>
            <p> Mantenimiento <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                @if (session('MantoRole') != 3)
                    <li class="nav-item @if ($seccion == 'solicitudManto') active @endif ">
                        <a class="nav-link" href="{{ route('solicitudManto') }}">
                            <i class="material-icons">assignment</i>
                            <p> Agregar</p>
                        </a>
                    </li>
                @endif
                <li class="nav-item @if ($seccion == 'consultaManto') active @endif">
                    <a class="nav-link" href="{{ route('consultaManto') }}">
                        <i class="material-icons">view_list</i>
                        <p> Consultar </p>
                    </a>
                </li>
                @if (session('MantoRole') == 1)
                    <li class="nav-item @if ($seccion == 'adminTec') active @endif">
                        <a class="nav-link" href="{{ route('adminTecnicosScreen') }}">
                            <i class="material-icons">manage_accounts</i>
                            <p> Administrador Tecnicos </p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'reporte') active @endif">
                        <a class="nav-link" href="{{ route('MantoReport') }}">
                            <i class="material-icons">dashboard</i>
                            <p> Reportes </p>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </li>
@endsection
