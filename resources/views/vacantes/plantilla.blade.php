@extends('layouts.app')
@include('menu.vacantes', ['seccion' => 'plantilla'])
@section('content')
<div class="row" style="margin-bottom: 10px;">
    <div id="dashop-panel-5" class="col-md-12">
        <div class="card ">
            <div class="card-header card-header-success card-header-icon">
                <div class="card-icon">
                    <i class="material-icons"></i>
                </div>
                <h4 class="card-title">Plantilla PRIGO</h4>
            </div>
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Autorizados</th>
                                    <th>Empleados</th>
                                    <th>Vacantes</th>
                                    <th>Contrataciones</th>
                                    <th>Bajas < 90 dias</th>
                                    <th>Bajas > 90 dias</th>
                                    <th></th>
                                </tr>
                                </head>
                            <tbody>
                                @php
                                $totalPlantilla = 0;
                                $totalContratados = 0;
                                $totalSolicitudes = 0;
                                $totalAtraso = 0;
                                $totalBien = 0;
                                @endphp
                                @foreach ($plantilla as $dato)
                                <tr>
                                    <td onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->oficina }}
                                    </td>
                                    <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->autorizado }}
                                    </td>
                                    <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->empleados }}
                                    </td>
                                    <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->autorizado - $dato->empleados > 0 ? $dato->autorizado - $dato->empleados : 0 }}
                                    </td>
                                     <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->contrataciones }}
                                    </td>
                                     <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->bajaMenor }}
                                    </td>
                                     <td class="text-right" onclick="getDetPlantilla('{{ $dato->idSucursal }}', '{{ $dato->oficina }}' )">
                                        {{ $dato->bajaMayor }}
                                    </td>
                                    <td class="td-actions text-right">
                                        <a class="btn btn-secondary" href="{{ route('plantillaDetail', [$dato->oficina, $dato->idSucursal]) }}" target="_blank">
                                            @if (empty($dato->autorizado))
                                            N / R
                                            @else
                                            @if ($dato->autorizado == $dato->empleados)
                                            <i class="material-icons text-success">
                                                done
                                            </i>
                                            @else
                                            <i class="material-icons @if ($dato->autorizado > $dato->empleados) text-warning
                                                                @else
                                                                text-danger @endif">warning</i>
                                            @endif
                                            @endif
                                        </a>
                                        <a type="button" class="btn btn-white text-dark" href="{{ route('editPlantilla', ['id' => $dato->idSucursal]) }}">
                                            <i class="material-icons">edit</i>
                                        </a>
                                        <a target="_blank" class="btn btn-info" href="{{ route('xlsPlantilla', $dato->idSucursal) }}">
                                            <i class="material-icons">get_app</i>
                                        </a>
                                    </td>
                                </tr>
                                @php
                                $totalPlantilla += $dato->autorizado;
                                $totalContratados += $dato->empleados;
                                $totalSolicitudes += $dato->contrataciones;
                                $totalAtraso += $dato->bajaMenor;
                                $totalBien += $dato->bajaMayor;
                                @endphp
                                @endforeach
                                <tr>
                                    <td>Total</td>
                                    <td class="text-right">{{ $totalPlantilla }}</td>
                                    <td class="text-right">{{ $totalContratados }}</td>
                                    <td class="text-right">
                                        {{ $totalPlantilla - $totalContratados > 0 ? $totalPlantilla - $totalContratados : 0 }}
                                    </td>
                                    <td class="text-right">{{ $totalSolicitudes }}</td>
                                    <td class="text-right">{{ $totalAtraso }}</td>
                                    <td class="text-right">{{ $totalBien }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row hidden" id="kpiventasHoraTab">
                    <div id="kpiventasHoraTabContent" class="col-md-12 ml-auto mr-auto">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->

<div class="modal fade" id="detModal" tabindex="-1" role="dialog" aria-labelledby="detModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="detModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div id="detModalTabContent" class="col-md-12 ml-auto mr-auto" style="height: 300px !important; overflow-y: scroll;">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('aditionalScripts')
<style>
    .loader {
        border: 16px solid #f3f3f3;
        border-radius: 50%;
        border-top: 16px solid #3498db;
        width: 120px;
        height: 120px;
        -webkit-animation: spin 2s linear infinite;
        /* Safari */
        animation: spin 2s linear infinite;
    }
</style>
<script>
    $().ready(function() {

    });


    function getDetPlantilla(ids, nom) {
        $(".loader").remove();
        $("#detModalLabel").text("Plantilla " + nom);
        $('#detModal').modal('show');
        $("#detModalTabContent").append("<div class='loader'></div>");

        var params = {
            "ids": ids,
            "_token": "{{ csrf_token() }}"
        };
        $.ajax({
            type: "POST",
            url: "{{ route('detPlantillaTable') }}",
            data: params,
            success: function(msg) {
                $("#detModalTabContent").empty();
                $("#detModalTabContent").append(msg);
                $(".loader").remove();
            },
            error: function() {
                console.log("error");
            }
        });
    }
</script>
@endsection