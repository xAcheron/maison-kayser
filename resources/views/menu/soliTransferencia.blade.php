@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('soliTransferencia') }}" aria-expanded="true">
            <i class="material-icons">receipt</i>
            <p> Solicitud Transferencia <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                <li class="nav-item @if ($seccion == 'main') active @endif ">
                    <a class="nav-link" href="{{ route('soliTransferencia') }}">
                        <i class="material-icons">list</i>
                        <p>Consulta Solicitudes</p>
                    </a>
                </li>
                <li class="nav-item @if ($seccion == 'add') active @endif ">
                    <a class="nav-link" href="{{ route('agregarSolicitudTrans') }}">
                        <i class="material-icons">add_circle</i>
                        <p>Generar Solicitud</p>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endsection
