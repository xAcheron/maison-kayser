@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" aria-expanded="true">
            <i class="material-icons">group</i>
            <p> Administrador Personal <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                <li class="nav-item @if ($seccion == 'index') active @endif">
                    <a class="nav-link" href="{{ route('adminPersonal') }}">
                        <i class="material-icons">confirmation_number</i>
                        <p>Descuentos</p>
                    </a>
                </li>
                <li class="nav-item @if ($seccion == 'listaUsuario') active @endif">
                    <a class="nav-link" href="{{ route('listaUsuario') }}">
                        <i class="material-icons">list</i>
                        <p>Listado de usuarios</p>
                    </a>
                </li>
                <li class="nav-item @if ($seccion == 'altaUsuarios') active @endif">
                    <a class="nav-link" href="{{ route('altaUsuarios') }}">
                        <i class="material-icons">person_add</i>
                        <p> Alta de usuarios </p>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endsection
