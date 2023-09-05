@section('appmenu')
    <style>
        .sidebar .nav li.active>a,
        .sidebar .nav li.active>a i {
            color: #ff9800;
        }
    </style>

    <hr />
    <div class="container text-center">
        <h3>Indice</h3>
    </div>


    @if (!empty($secciones))
        <ul class="list-group pl-5" style="list-style-type: none;">
            @foreach ($secciones[0]->subSecciones as $seccion)
                <a href="{{ route('verSeccionGet', $seccion->id) }}" class="h4">
                    <li>
                        {{ $seccion->nombre }}
                    </li>
                </a>
                <ul class="list-group-item" style="list-style-type: '> ';">
                    @foreach ($seccion->subSecciones as $subSeccion)
                        <a href="{{ route('verSeccionGet', $subSeccion->id) }}" class="h6 list-group-item p-0 pb-2">
                            <li>
                                {{ $subSeccion->nombre }}
                            </li>
                        </a>
                    @endforeach
                </ul>
            @endforeach
        </ul>
    @endif

@endsection
