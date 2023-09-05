@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" href="{{-- route('vacantes') --}}" aria-expanded="true">
            <i class="material-icons">transfer_within_a_station</i>
            <p> Vacantes RH <b class="caret"></b> </p>
        </a>
        <div class="collapse show">
            <ul class="nav">
                {{-- @if ($role != 5) --}}
                    <li class="nav-item @if ($seccion == 'nuevavacante') active @endif ">
                        <a class="nav-link" href="{{-- route('nuevavacante') --}}">
                            <i class="material-icons">book</i>
                            <p> Solicitud de Personal </p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'consultavacantes') active @endif ">
                        <a class="nav-link" href="{{-- route('consultavacantes') --}}">
                            <i class="material-icons">view_list</i>
                            <p> Consulta de solicitudes </p>
                        </a>
                    </li>
                {{-- @endif
                @if ($role == 1 || $role == 4) --}}
                    <li class="nav-item @if ($seccion == 'getContratados') active @endif ">
                        <a class="nav-link" href="{{-- route('getContratados') --}}">
                            <i class="material-icons">view_list</i>
                            <p> Consulta de Contrataciones</p>
                        </a>
                    </li>
                {{-- @endif
                @if ($role == 1 || $role == 3) --}}
                    <li class="nav-item @if ($seccion == 'getCapacitados') active @endif ">
                        <a class="nav-link" href="{{-- route('getCapacitados') --}}">
                            <i class="material-icons">view_list</i>
                            <p> Por Confirmar</p>
                        </a>
                    </li>
                {{-- @endif
                @if ($role == 1 || $role == 5) --}}
                    <li class="nav-item @if ($seccion == 'getBajas') active @endif ">
                        <a class="nav-link" href="{{-- route('getBajas') --}}">
                            <i class="material-icons">view_list</i>
                            <p> Bajas</p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'empleados') active @endif ">
                        <a class="nav-link" href="{{-- route('empleados') --}}">
                            <i class="material-icons">supervisor_account</i>
                            <p>Empleados</p>
                        </a>
                    </li>
                {{-- @endif --}}
                <li class="nav-item @if ($seccion == 'plantilla') active @endif ">
                    <a class="nav-link" href="{{-- route('plantilla') --}}">
                        <i class="material-icons">assignment</i>
                        <p>Plantilla</p>
                    </a>
                </li>
                <li class="nav-item @if ($seccion == 'gestionPuestos') active @endif ">
                    <a class="nav-link" href="{{-- route('gestionPuestos') --}}">
                        <i class="material-icons">manage_accounts</i>
                        <p>Gestion Puestos</p>
                    </a>
                </li>
                <li class="nav-item @if ($seccion == 'micros') active @endif ">
                    <a class="nav-link" href="{{-- route('micros') --}}">
                        <i class="material-icons">manage_accounts</i>
                        <p>Micros</p>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endsection
