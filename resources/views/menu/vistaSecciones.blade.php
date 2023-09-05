@extends('layouts.pro')
@include('menu.menuAdminBibliotecaView', ['seccion' => 'lectorBiblioteca'])
<div class="sidebar">
    <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item">
                Barra
            </li>
        </ul>
    </div>
</div>
@section('content')
    <div class="row">
        <div class="container">
            <div class="row justify-content-right">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if (!empty($ruta))
                            @for ($i = count($ruta) - 1; $i >= 0; $i--)
                                @if ($i == 0)
                                    <li class="breadcrumb-item active">{{ $ruta[$i]['nombre'] }}</li>
                                @else
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('verSeccionGet', $ruta[$i]['id']) }}">{{ $ruta[$i]['nombre'] }}</a>
                                    </li>
                                @endif
                            @endfor
                        @endif

                    </ol>
                </nav>
            </div>
            @foreach ($datos as $item)
                @if (!empty($secciones) || !empty($subsecciones))
                    <div class="row justify-content-center">
                        <div class="col-md-3 col-sm-12">
                            <a href="" class="btn btn-secondary col-12 mt-0 mb-1" data-toggle="modal"
                                data-target="#indiceModal">Ver indice</a>
                            @if (empty($subsecciones))
                                @foreach ($secciones as $seccion)
                                    <a href="{{ route('verSeccionGet', $seccion->id) }}"
                                        class="btn @if ($seccion->id != $idSeccion) btn-secondary @endif col-12 mt-0 mb-1">
                                        {{ $seccion->nombre }}
                                    </a>
                                @endforeach
                            @else
                                @foreach ($subsecciones as $seccion)
                                    <a href="{{ route('verSeccionGet', $seccion->id) }}"
                                        class="btn @if ($seccion->id != $idSeccion) btn-secondary @endif col-12 mt-0 mb-1">
                                        {{ $seccion->nombre }}
                                    </a>
                                @endforeach
                            @endif
                        </div>
                @endif
                <div class="col-md-9 col-sm-12">
                    <div class="card my-0">
                        <div class="card-title">
                            <h4 class="text-center card-title mt-2 text-uppercase ">{{ $item->nombre }}</h4>
                        </div>
                        <hr />
                        <div class="card-body">
                            @php
                                if (file_exists(base_path() . '/storage/app/public/biblioteca-admin/archivos/' . $item->idArchivo . '/' . $item->id . '.html')) {
                                    $html_file = base_path() . '/storage/app/public/biblioteca-admin/archivos/' . $item->idArchivo . '/' . $item->id . '.html';
                                } else {
                                    $html_file = base_path() . '/storage/app/public/biblioteca-admin/default.html';
                                }
                                
                                include $html_file;
                            @endphp
                        </div>
                    </div>
                    <div class="row p-0 m-0 mt-2 justify-content-center ">
                        @if ($idPadre != 0)
                            <div class="col-6 p-0 pr-1">
                                <a class="btn btn-secondary col-12 mt-0 mb-1" onclick="anterior()">
                                    Anterior
                                </a>
                            </div>
                        @endif
                        @if ($secciones[count($secciones) - 1]->subSecciones[count($secciones[count($secciones) - 1]->subSecciones) - 1]->id != $idSeccion)
                            <div class="col-6 p-0 pl-1">
                                <a class="btn btn-secondary col-12 mt-0 mb-1" onclick="siguiente()">
                                    Siguiente
                                </a>
                            </div>
                        @endif
                    </div>
                </div>


        </div>
        @endforeach
    </div>
    </div>

    <div class="modal fade" id="indiceModal" tabindex="-1" aria-labelledby="indiceLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="indiceLabel">Indice</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul>
                        @foreach ($secciones as $seccion)
                            <li>
                                <a href="{{ route('verSeccionGet', $seccion->id) }}">
                                    <h4>{{ $seccion->nombre }}</h4>
                                </a>
                            </li>
                            <ul>
                                @foreach ($seccion->subSecciones as $subSeccion)
                                    <li>
                                        <a href="{{ route('verSeccionGet', $subSeccion->id) }}">
                                            <h4>{{ $subSeccion->nombre }}</h4>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('aditionalScripts')
    <script type="text/javascript">
        function siguiente() {
            var obj = {!! json_encode($secciones) !!}
            var idSeccion = {{ $idSeccion }}
            var idPadre = {{ $idPadre }}
            var index = 0;


            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                const subSecciones = obj[i].subSecciones;
                if (element.id == idSeccion || element.id == idPadre) {
                    for (let e = 0; e < subSecciones.length; e++) {
                        const subSeccion = subSecciones[e];
                        if (idSeccion != subSecciones[subSecciones.length - 1].id) {
                            if (subSeccion.id == idSeccion) {
                                index = subSecciones[e + 1].id;
                                break;
                            } else {
                                index = subSecciones[0].id;
                            }
                        } else {
                            break;
                        }
                    }
                    if (index == 0) {
                        index = obj[i + 1].id;
                    }
                    break;
                } else {
                    if (idPadre == 0) {
                        index = obj[0].id;
                    }
                }


            }

            var ruta = "{{ route('verSeccionGet', ':id') }}";
            ruta = ruta.replace(':id', index);

            window.location.href = ruta;
        }

        function anterior() {
            var obj = {!! json_encode($secciones) !!}
            var idSeccion = {{ $idSeccion }}
            var idPadre = {{ $idPadre }}
            var index = 0;
            for (let i = 0; i < obj.length; i++) {
                const element = obj[i];
                const subSecciones = obj[i].subSecciones;
                if (element.id == idSeccion) {
                    if (index == 0 && Math.sign(i - 1) != -1) {
                        index = obj[i - 1].subSecciones[obj[i - 1].subSecciones.length - 1].id;
                    }
                    break;
                } else if (idPadre == element.id) {
                    for (let e = 0; e < subSecciones.length; e++) {
                        const subSeccion = subSecciones[e];
                        if (idSeccion != subSecciones[0].id) {
                            if (subSeccion.id == idSeccion) {
                                index = subSecciones[e - 1].id;
                                break;
                            }
                        } else {
                            break;
                        }
                    }
                } else {
                    if (idPadre == 0) {
                        index = obj[0].id;
                    }
                }


            }

            var ruta = "{{ route('verSeccionGet', ':id') }}";
            if (index != 0) {
                ruta = ruta.replace(':id', index);
            } else {
                ruta = ruta.replace(':id', idPadre);
            }
            window.location.href = ruta;
        }
    </script>
@endsection
