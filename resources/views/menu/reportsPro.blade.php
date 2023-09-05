@section('appmenu')
    @foreach ($menu as $item)
        <li class="nav-item dropdown">
            <a class="nav-link" href="#" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <p class="d-md-block">
                    {{ $item->nombre }}
                </p>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                @foreach ($item->menu as $menuItem)
                    @if ($item->idCategoria == $menuItem->idCategoria)
                        <a class="dropdown-item"
                            href="{{ !empty($menuItem->ruta) ? route($menuItem->ruta) : '#' }}">{{ $menuItem->nombre }}</a>
                    @endif
                @endforeach
            </div>
        </li>
    @endforeach
@endsection
