@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'ProductMix'])
@section('content')
    <style>
        .filter-components {
            backgMath.round-color: #fff !important;
            border: 1px solid #aaa !important;
            border-radius: 4px !important;
            color: #444 !important;
            line-height: 24px !important;
            padding-left: 0.5rem;
        }
    </style>
    <div class="row">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
                <div class="card-icon">
                    <i class="material-icons">build</i>
                </div>
                <h4 class="card-title">Mantenimiento
                    <small> - Filters</small>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        Fecha:</br>
                        <input type="text" name="daterange" id="daterange" class="filter-components" style="width: 100%">
                    </div>
                    <div class="col-3">
                        Sucursal: </br>
                        <select name="sucursal" id="sucursal" class="select2-item" style="width:100%;">
                            <option value="" disabled selected>Seleccione Sucursal</option>
                            @if (!empty($hierachy))
                                @foreach ($hierachy as $location)
                                    <option value="{{ $location->id }}" data-type="{{ $location->tipo }}"
                                        data-clas="{{ $location->clas }}">
                                        {{ $location->nombre }}</option>
                                @endforeach
                            @else
                                <option value="tzuco">Tzuco</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-3">
                        Tecnico: </br>
                        <select name="tecnico" id="tecnico" class="select2-item" style="width: 100%">
                            @if (!empty($tecnicos))
                                @if (count($tecnicos) > 1)
                                    <option value="All" data-type="2">Todos</option>
                                @endif
                                @foreach ($tecnicos as $item)
                                    <option value="{{ $item->idUsuario }}" data-type="2">{{ $item->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <div class="btn-group">
                            <button id="runReport" class="btn btn-white btn-just-icon">
                                <i class="material-icons">search</i>
                                <div class="ripple-container"></div>
                            </button>
                            {{-- <button id="exportReport" class="btn btn-white btn-just-icon">
                            <i class="material-icons">table_view</i>
                            <div class="ripple-container"></div>
                        </button> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="row justify-content-between col-lg-6 p-0 mr-0">
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="por24"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconPor24"></i>
                    </div>
                </div>
                <div class="card-footer">Porcentaje resuelto (24 hrs)</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="por48"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconPor48"></i>
                    </div>
                </div>
                <div class="card-footer" id="footerMensual">Porcentaje resuelto (48 hrs)</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="por72"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconPor72"></i>
                    </div>
                </div>
                <div class="card-footer">Porcentaje resuelto (72 hrs)</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="por72M"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconPor72M"></i>
                    </div>
                </div>
                <div class="card-footer">Porcentaje resuelto (Mas 72 hrs)</div>
            </div>
        </div>
        <div class="card col-lg-6 mb-0">
            <div class="card-header h4">Desempeño mensual</div>
            <canvas id="chart"></canvas>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Tecnicos</h4>
                </div>
                <div class="card-body">
                    <table class="table table-condensed" id="tbTecnicos">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Sucursales</h4>
                </div>
                <div class="card-body">
                    <table class="table table-condensed" id="tbSucursales">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Distritales</h4>
                </div>
                <div class="card-body">
                    <table class="table table-condensed" id="tbDistritales">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('aditionalScripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.29.2/sweetalert2.all.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        var chart;

        $('#runReport').on('click', function() {
            var idSucursal = $('#sucursal').val();
            var daterange = $('#daterange').val();
            var tecnico = $('#tecnico').val();

            if (validParams()) {
                var params = {
                    location: idSucursal,
                    typeLoc: $('#sucursal :selected').data('clas'),
                    daterange: daterange,
                    tecnico: tecnico,
                    _token: "{{ csrf_token() }}"
                };
                loadingData();
                $.ajax({
                    type: "POST",
                    data: params,
                    url: "{{ route('getReport', ['id' => 28, 'format' => 'json']) }}",
                    success: function(msg) {
                        const tBodyTec = $('#tbTecnicos tbody');
                        const tHeadTec = $('#tbTecnicos tbody');
                        const tBodySuc = $('#tbSucursales tbody');
                        const tHeadSuc = $('#tbSucursales tbody');
                        const tBodyDis = $('#tbDistritales tbody');
                        const tHeadDis = $('#tbDistritales tbody');

                        tBodyTec.empty();
                        tHeadTec.empty();
                        tBodySuc.empty();
                        tHeadSuc.empty();
                        tBodyDis.empty();
                        tHeadDis.empty();

                        $('#por24').text('');
                        $('#por48').text('');
                        $('#por72').text('');
                        $('#por72M').text('');

                        if (msg.success == true) {
                            const data = msg.data;

                            $('#por24').text(data.porRes[24])
                            $('#por48').text(data.porRes[48])
                            $('#por72').text(data.porRes[72])
                            $('#por72M').text(data.porRes['72Mas'])

                            const tecnicos = data.tecnicos;
                            const headersTec = data.headersTec;

                            genTable(headersTec, tecnicos, tBodyTec, tHeadTec);

                            const sucursales = data.sucursales;
                            const headersSuc = data.headersSuc;

                            genTable(headersSuc, sucursales, tBodySuc, tHeadSuc);

                            const distritales = data.distritales;
                            const headersDis = data.headersDis;

                            genTable(headersDis, distritales, tBodyDis, tHeadDis);

                            genGraph(data.dataGraph, 'line');

                        }

                        endLoadingData();
                    },
                    error: function() {

                    }
                });
            } else {
                swal({
                    title: "Error",
                    text: 'All filter params are mandatory!',
                    type: 'error',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            }

        })

        function genGraph(data, type) {


            if (chart == undefined) {
                const ctx = document.getElementById('chart');
                chart = new Chart(ctx, {
                    type: type,
                    data: data,
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMin: 100,
                                suggestedMax: 100,
                            }
                        }
                    }
                });
            } else {
                chart.data = data;
                chart.update();
            }
        }

        function genTable(headers, data, tbody, thead) {
            trHead = document.createElement('tr');

            headers.forEach(header => {
                const th = document.createElement('th');
                th.innerHTML = header;

                trHead.appendChild(th);
            });

            thead.append(trHead);

            data.forEach(sucursal => {

                var tr = getRow(sucursal);

                tbody.append(tr);
            });
        }

        function getRow(params) {
            const tr = document.createElement('tr');

            tr.appendChild(getTd(params.nombre ?? 'Sin Asignacion'))
            tr.appendChild(getTd(params.res24));
            tr.appendChild(getTd(
                `${Math.round(params.res24 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.res48));
            tr.appendChild(getTd(
                `${Math.round(params.res48 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.res72));
            tr.appendChild(getTd(
                `${Math.round(params.res72 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.res72M));
            tr.appendChild(getTd(
                `${Math.round(params.res72M * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.atr24));
            tr.appendChild(getTd(
                `${Math.round(params.atr24 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.atr48));
            tr.appendChild(getTd(
                `${Math.round(params.atr48 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.atr72));
            tr.appendChild(getTd(
                `${Math.round(params.atr72 * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.atr72M));
            tr.appendChild(getTd(
                `${Math.round(params.atr72M * 100 / params.total)}%`
            ));
            tr.appendChild(getTd(params.total));

            return tr;
        }

        function getTd(text) {
            const td = document.createElement('td');
            td.innerHTML = text;
            if (Number.isInteger(parseInt(text))) {
                td.classList.add('text-right');
            }
            return td;
        }

        function validParams() {
            if ($("#daterange").val() != "" && $("#sucursal").val() != "" && $("#daterange").val() != null && $("#sucursal")
                .val() != null && $('#sucursal').val() != "" && $('#sucursal').val() != null)
                return true;
            return false;
        }

        $('input[name="daterange"]').daterangepicker({
            opens: 'right',
            minYear: 2019,
            maxYear: {{ date('Y') }},
            locale: {
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')]
            }
        });

        $('.select2-item').select2({
            templateResult: formatState
        });

        function loadingData() {
            Swal.showLoading();
        }

        function endLoadingData() {
            Swal.close();
        }

        function formatState(state) {
            if (state.disabled == false && (state.id > 0 || state.id != "")) {
                if (state.element.attributes['data-type'] != null && state.element.attributes['data-type'] !=
                    undefined) {
                    tipo = state.element.attributes['data-type'].value
                    return $('<span class="' + (tipo == 2 ? "ml-2" : "font-weight-bold") + '">' + state.text + '</span>');
                } else {
                    return state.text;
                }
            } else {
                return state.text;
            }
        }
    </script>
@endsection
