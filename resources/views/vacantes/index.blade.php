@extends('layouts.app')
@include('menu.vacantes', ['seccion' => 'index'])
@section('content')
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_turned_in</i>
                    </div>
                    <h5 class="card-title">Plantilla Autorizada</h5>
                </div>
                <div class="card-body">
                    <h3><a href="{{-- route('plantilla') --}}">{{ $autorizados }}</a></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-warning card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_late</i>
                    </div>
                    <h5 class="card-title">Vacantes</h5>
                </div>
                <div class="card-body">
                    <h3>{{ $diferencia }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-info card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_ind</i>
                    </div>
                    <h5 class="card-title">Plantilla Actual</h5>
                </div>
                <div class="card-body">
                    <h3>150{{ $actuales }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-info card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment</i>
                    </div>
                    <h5 class="card-title">Solicitudes abiertas</h5>
                </div>
                <div class="card-body">
                    <h3><a href="{{-- route('consultavacantes') --}}">{{ $abiertas }}</a></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-warning card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_late</i>
                    </div>
                    <h5 class="card-title">Solicitudes retrasadas</h5>
                </div>
                <div class="card-body">
                    <h3><a href="{{-- route('showRetrasadas') --}}">{{ $retrasadas }}</a></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_turned_in</i>
                    </div>
                    <h5 class="card-title">Solicitudes en tiempo</h5>
                </div>
                <div class="card-body">
                    <h3><a href="{{-- route('showEnTiempo') --}}">120{{ $entiempo }}</a></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="card">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">assignment_turned_in</i>
                    </div>
                    <h5 class="card-title">Contrataciones del mes</h5>
                </div>
                <div class="card-body">
                    <h3>{{ $cerradas }}</h3>
                </div>
            </div>
        </div>
        {{-- @if ($role == 1) --}}
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">assignment_turned_in</i>
                        </div>
                        <h5 class="card-title">Efectividad al dia</h5>
                    </div>
                    <div class="card-body">
                         @if (count($efectividad) == 0)
                            <h3>0%</h3>
                        @else
                            <h3>{{ $efectividad[0]->perbien }} %</h3>
                        @endif 
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">assignment_turned_in</i>
                        </div>
                        <h5 class="card-title">Atraso al dia</h5>
                    </div>
                    <div class="card-body">
                         @if (count($efectividad) == 0)
                            <h3>0%</h3>
                        @else
                            <h3>{{ $efectividad[0]->peratraso }} %</h3>
                        @endif 
                    </div>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10">
                <div class="card">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">assignment_turned_in</i>
                        </div>
                        <h5 class="card-title">Efectividad Reclutadores</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>Reclutador</th>
                                    <th>Efectividad</th>
                                    <th>Solicitudes</th>
                                    <th>Atrasadas</th>
                                    <th>En tiempo</th>
                                    <th>E. Actual</th>
                                    <th>En tiempo</th>
                                    <th>Atrasadas</th>
                                    <th>E. Anterior</th>
                                </tr>
                                </head>
                            <tbody>
                                 @foreach ($efectividadReclutador as $dato)
                                    <tr>
                                        <td>{{ $dato->nombre }}</td>
                                        <td class="text-right">{{ $dato->perbien }} %</td>
                                        <td class="text-right">{{ $dato->total }}</td>
                                        <td class="text-right">{{ $dato->atraso }}</td>
                                        <td class="text-right">{{ $dato->bien }}</td>
                                        <td class="text-right">{{ $dato->actual }}</td>
                                        <td class="text-right">{{ $dato->bienActual }}</td>
                                        <td class="text-right">{{ $dato->atrasoActual }}</td>
                                        <td class="text-right">{{ $dato->anterior }}</td>
                                    </tr>
                                @endforeach 
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {{-- @endif --}}
    </div>
@endsection
@section('aditionalScripts')
    <script>
        $().ready(function() {

        });
    </script>
@endsection
