@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" aria-expanded="true">
            <i class="material-icons">edit_note</i>
            <p> Productos <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                @if ($role == 1 || $role == 3)
                    <li class="nav-item @if ($seccion == 'consulSoliProd') active @endif">
                        <a class="nav-link" href="{{ route('consulSoliProd') }}">
                            <i class="material-icons">view_list</i>
                            <p> Consultar Solicitudes </p>
                        </a>
                    </li>
                @endif
                @if ($role == 1 || $role == 2)
                    <li class="nav-item @if ($seccion == 'solicitudProducto') active @endif ">
                        <a class="nav-link" href="{{ route('altaProducto') }}">
                            <i class="material-icons">file_upload</i>
                            <p>Solicitud de alta</p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'solicitudProductoBaja') active @endif ">
                        <a class="nav-link" href="{{ route('soliproductobaja') }}">
                            <i class="material-icons">file_download</i>
                            <p>Solicitud de baja</p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'soliAddDescuentos') active @endif ">
                        <a class="nav-link" href="{{ route('soliAddDescuentos') }}">
                            <i class="material-icons">assignment_turned_in</i>
                            <p>Asignar Descuentos</p>
                        </a>
                    </li>
                    <li class="nav-item @if ($seccion == 'soliAddPaquetes') active @endif ">
                        <a class="nav-link" href="{{ route('soliAddPaquetes') }}">
                            <i class="material-icons">inventory_2</i>
                            <p>Asignar Paquetes</p>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </li>
@endsection
