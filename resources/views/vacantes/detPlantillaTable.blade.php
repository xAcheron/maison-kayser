<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Empleado</th>
            <th>Puesto</th>
            @if ($type == 'page')
                {{-- <th>Area</th> --}}
                <th></th>
            @endif
        </tr>
        </head>
    <tbody>
        @foreach ($detalle as $dato)
            <tr
                @if ($dato->excedente == 1 && $type != 'page') style="background: #cc0000;" @elseif($dato->excedente == 2 && $type != 'page') style="background: #0099ff;" @elseif($dato->excedente == 3 && $type != 'page') style="background: #33cc33;" @endif>
                <td class="text-left">{{ $loop->iteration }}</td>
                <td class="text-left">{{ $dato->nombre }}</td>
                <td class="text-left">{{ $dato->puesto }}</td>
                @if ($type == 'page')
                    {{-- <td class="text-left">{{ $dato->area }}</td> --}}
                    <td class="text-left">
                        @if ($dato->excedente != 3 AND $dato->excedente != 2 AND $dato->excedente != 1)
                            <a class="btn btn-secondary btn-just-icon btn-sm m-0"
                                onclick="detailEmployee('{{ $dato->idEmpleado }}')">
                                <i class="material-icons">
                                    person
                                </i>
                            </a>
                            <a class="btn btn-info btn-just-icon btn-sm m-0" href="{{route('formNewEmployee', [$dato->idEmpleado])}}">
                                <i class="material-icons text-white">
                                    edit
                                </i>
                            </a>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
