@section('appmenu')
    <li class="nav-item active">
        <a class="nav-link" data-toggle="collapse" href="#pagesExamples" aria-expanded="true">
            <i class="material-icons">store</i>
            <p> Pedidos <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">
                @if (!empty($role) && $role != '5')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('nuevopedido') }}">
                            <i class="material-icons">shopping_basket</i>
                            <p> Solicitud de Pedido </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consultapedido') }}">
                            <i class="material-icons">view_list</i>
                            <p> Consulta de Pedido </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consultarticulos') }}">
                            <i class="material-icons">image_search</i>
                            <p> Consulta de Articulos </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consultapedidoprov') }}">
                            <i class="material-icons">local_grocery_store</i>
                            <p> Solicitud a Proveedor </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transferencia') }}">
                            <i class="material-icons">import_export</i>
                            <p> Transferencias </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('merma') }}">
                            <i class="material-icons">delete_sweep</i>
                            <p> Merma</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('inventario') }}">
                            <i class="material-icons">ballot</i>
                            <p> Inventario</p>
                        </a>
                    </li>
                @endif
                @if (!empty($role) && $role == '1')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consultapedidohielo') }}">
                            <i class="material-icons">local_drink</i>
                            <p> Hielo y Agua</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('costoVenta') }}">
                            <i class="material-icons">attach_money</i>
                            <p> Costo</p>
                        </a>
                    </li>
                @endif
                @if (!empty($role) && ($role == '1' || $role == '5'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('nuevoTraslado') }}">
                            <i class="material-icons">import_export</i>
                            <p> Solicitud de Traslado </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consultatraslado') }}">
                            <i class="material-icons">view_list</i>
                            <p> Consulta de Traslados </p>
                        </a>
                    </li>
                @endif
                @if (!empty($espPerm) && $espPerm == 1)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contactosPage') }}">
                            <i class="material-icons">contacts</i>
                            <p> Contactos </p>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('incidenciasScreen') }}">
                        <i class="material-icons">forum</i>
                        <p> Incidencias </p>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endsection
