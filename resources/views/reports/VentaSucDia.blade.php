@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'GuestWeek'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-info">
                    <div class="card-icon">
                        <i class="material-icons">timeline</i>
                    </div>
                    <h4 class="card-title">Venta sucursal
                        <small> - Filtros</small>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            Fechas:<br>
                            <input type="text" class="filter-components" style="width:100%;" name="daterange"
                                id="daterange" value="{{ date('Y-m-d') }} - {{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-3">
                            Sucursal:<br>
                            <select class="select2-item" id="location" data-size="7" style="width:100%;" title="Location">
                                <option value="0" disabled selected>Select a location</option>
                                <option value="K Arcos" data-type="2">K Arcos</option>
                                <option value="Carmela y Sal" data-type="2">Carmela y Sal</option>
                                {{-- @if (!empty($hierachy))
                                    @foreach ($hierachy as $location)
                                        <option value="{{ $location->id }}" data-type="{{ $location->tipo }}">
                                            {{ $location->nombre }}</option>
                                    @endforeach
                                @else
                                    <option value="tzuco">Tzuco</option>
                                @endif --}}
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                            <div class="btn-group">
                                <button id="runReport" class="btn btn-white btn-just-icon">
                                    <i class="material-icons">search</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button id="exportReport" class="btn btn-white btn-just-icon">
                                    <i class="material-icons">table_view</i>
                                    <div class="ripple-container"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta sucursal</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow-x:auto !important;">
                                <table class="table table-condensed table-striped">
                                    <thead id="baseHeadGlobal">
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        </head>
                                    <tbody id="baseTableGlobal"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta sucursal por Dia</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow-x:auto !important;">
                                <table class="table table-condensed table-striped">
                                    <thead id="baseHeadRvc">

                                        </head>
                                    <tbody id="baseTableRvc">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta por Semana</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow-x:auto !important;">
                                <table class="table table-condensed table-striped">
                                    <thead id="weekHeadRvc">

                                    </head>
                                    <tbody id="weekTableRvc">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <canvas id="reportChart1"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-condensed table-striped">
                                <thead><tr><th>Semana</th><th>Actual</th><th>Anterior</th></tr></thead>
                                <tbody id="tblreportChart1"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Visitas vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <canvas id="reportChart2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Visitas vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-condensed table-striped">
                                <thead><tr><th>Semana</th><th>Actual</th><th>Anterior</th></tr></thead>
                                <tbody id="tblreportChart2"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cheque Promedio vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <canvas id="reportChart3"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cheque Promedio vs Año anterior</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-condensed table-striped">
                                <thead><tr><th>Semana</th><th>Actual</th><th>Anterior</th></tr></thead>
                                <tbody id="tblreportChart3"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <div id="formsarea"></div>
@endsection
@section('aditionalScripts')
    <style>
        .growColor {
            background-color: #00CC66;
            font-weight: bold;
        }

        .downColor {
            background-color: #E53935;
            font-weight: bold;
        }

        .ct-chart .ct-series-d .ct-slice-pie {
            fill: #4caf50 !important;
        }

        .ct-chart .ct-series-e .ct-slice-pie {
            fill: #9c27b0 !important;
        }

        .ct-chart .ct-series-f .ct-slice-pie {
            fill: #E67E22 !important;
        }


        .ct-chart .ct-series-g .ct-slice-pie {
            fill: #E6B0AA !important;
        }


        .ct-chart .ct-series-h .ct-slice-pie {
            fill: #AED6F1 !important;
        }


        .ct-chart .ct-series-i .ct-slice-pie {
            fill: #F1948A !important;
        }


        .ct-chart .ct-series-j .ct-slice-pie {
            fill: #F4D03F !important;
        }


        .ct-chart .ct-series-k .ct-slice-pie {
            fill: #17A589 !important;
        }


        .ct-chart .ct-series-l .ct-slice-pie {
            fill: #C0392B !important;
        }


        .ct-chart .ct-series-m .ct-slice-pie {
            fill: #ABB2B9 !important;
        }

        .ct-chart .ct-series-n .ct-slice-pie {
            fill: #C39BD3 !important;
        }

        .ct-chart .ct-series-o .ct-slice-pie {
            fill: #F9E79F !important;
        }

        .ct-chart .ct-series-p .ct-slice-pie {
            fill: #512E5F !important;
        }

        .ct-chart .ct-series-q .ct-slice-pie {
            fill: #7DCEA0 !important;
        }

        .ct-chart .ct-series-r .ct-slice-pie {
            fill: #0E6251 !important;
        }

        .ct-chart .ct-series-s .ct-slice-pie {
            fill: #A9DFBF !important;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.29.2/sweetalert2.all.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/Chart.min.css" rel="stylesheet">
    </link>

    <script type="text/javascript">
        const weekday = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"];
        // var ctx = [];
        // ctx[0] = document.getElementById('reportChart1');
        // ctx[1] = document.getElementById('reportChart2');
        // ctx[2] = document.getElementById('reportChart3');
        // var reportChart = [];

        // const chartCfg = [{
        //     type: 'line',
        //     data: {
        //         labels: [],
        //         datasets: []
        //     },
        //     options: {
        //         responsive: true,
        //         plugins: {
        //             legend: {
        //                 position: 'top',
        //             },
        //             title: {
        //                 display: false,
        //                 text: ''
        //             }
        //         }
        //     },
        // }, {
        //     type: 'line',
        //     data: {
        //         labels: [],
        //         datasets: []
        //     },
        //     options: {
        //         responsive: true,
        //         plugins: {
        //             legend: {
        //                 position: 'top',
        //             },
        //             title: {
        //                 display: false,
        //                 text: ''
        //             }
        //         }
        //     },
        // }, {
        //     type: 'line',
        //     data: {
        //         labels: [],
        //         datasets: []
        //     },
        //     options: {
        //         responsive: true,
        //         plugins: {
        //             legend: {
        //                 position: 'top',
        //             },
        //             title: {
        //                 display: false,
        //                 text: ''
        //             }
        //         }
        //     },
        // }];

        $(function() {
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


            function formatState(state) {
                if (state.disabled == false && (state.id > 0 || state.id != "")) {
                    if (state.element.attributes['data-type'] != null && state.element.attributes['data-type'] !=
                        undefined) {
                        tipo = state.element.attributes['data-type'].value
                        return $('<span class="' + (tipo == 2 ? "ml-2" : "font-weight-bold") + '">' + state.text +
                            '</span>');
                    } else {
                        return state.text;
                    }
                } else {
                    return state.text;
                }

            }

            $("#exportReport").on("click", function(e) {

                if (validParams()) {
                    let url = "{{ route('getReport', ['id' => 23, 'format' => 'xlsx']) }}";
                    var myForm = document.createElement('form');
                    myForm.setAttribute('action', url);
                    myForm.setAttribute('method', 'post');
                    myForm.setAttribute('hidden', 'true');
                    myForm.setAttribute('target', '_blank');

                    var daterange = document.createElement('input');
                    daterange.setAttribute('type', 'hidden');
                    daterange.setAttribute('name', 'daterange');
                    daterange.setAttribute('value', $("#daterange").val());
                    myForm.appendChild(daterange);

                    var location = document.createElement('input');
                    location.setAttribute('type', 'hidden');
                    location.setAttribute('name', 'location');
                    location.setAttribute('value', $("#location").val());
                    myForm.appendChild(location);

                    var token = document.createElement('input');
                    token.setAttribute('type', 'hidden');
                    token.setAttribute('name', '_token');
                    token.setAttribute('value', "{{ csrf_token() }}");
                    myForm.appendChild(token);
                    document.getElementById("formsarea").appendChild(myForm);
                    myForm.submit();
                    document.getElementById("formsarea").innerHTML = "";
                } else {
                    swal({
                        title: "Error",
                        text: 'All filter params are mandatory!',
                        type: 'error',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                }

            });

            $('#runReport').on("click", function(e) {
                if (validParams()) {
                    loadingData();

                    var params = {
                        daterange: $("#daterange").val(),
                        location: $("#location").val(),
                        _token: "{{ csrf_token() }}"
                    };

                    $.ajax({
                        type: "POST",
                        url: "{{ route('getReport', ['id' => 23, 'format' => 'json']) }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success == true) {
                                if (msg.data != undefined && msg.data != null) {
                                    makeTable(msg.data);
                                } else {
                                    clearTable();
                                }
                            }
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
            });
        });

        function loadingData() {
            Swal.showLoading();
        }

        function endLoadingData() {
            Swal.close();
        }

        function makeTable(data) {

            clearTable();

            var formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
                maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
            });

            var formatter2 = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });

            let target, innerHTML, subInnerHTML, domTarget, htmlTemplate, tr;

            domTarget = '#baseTableGlobal';
            target = document.querySelector(domTarget);
            tr = document.createElement('tr');
            innerHTML = "<td>Venta Neta</td><td class='text-right'>" + formatter2.format(data.ventaGlobal[0].ventaNeta) +
                "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            tr = document.createElement('tr');
            innerHTML = "<td>Impuesto</td><td class='text-right'>" + formatter2.format(data.ventaGlobal[0].impuesto) +
                "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            tr = document.createElement('tr');
            innerHTML = "<td>Venta Total</td><td class='text-right'>" + formatter2.format(data.ventaGlobal[0].ventaBruta) +
                "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            tr = document.createElement('tr');
            innerHTML = "<td>Servicio (Propina)</td><td class='text-right'>" + formatter2.format(data.ventaGlobal[0]
                .servicio) + "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            tr = document.createElement('tr');
            innerHTML = "<td>Descuentos</td><td class='text-right'>" + formatter2.format(data.ventaGlobal[0].descuentos) +
                "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            domTarget = '#baseHeadRvc';
            tr = document.createElement('tr');
            target = document.querySelector(domTarget);
            innerHTML = "<th colspan='2'>Fecha</th>";
            subInnerHTML = "<th>Dia</th><th>Fecha</th>";
            innerHTML += "<th colspan='5'>Total General</th>";
            subInnerHTML += "<th>Venta Neta</th><th>Venta Bruta</th><th>Impuesto</th><th>Descuento</th>";
            // for (var i = 0; i < data.rvcnames.length; i++) {
            // innerHTML += "<th colspan='5'>" + data.rvcnames[i].rvc + "</th>";
            // subInnerHTML += "<th>Venta</th><th>Visitantes</th><th>Cheque Prom.</th><th>%LW</th><th>%LY</th>";
            // }
            /*tr.innerHTML = innerHTML;
            target.appendChild(tr);*/
            tr = document.createElement('tr');
            tr.innerHTML = subInnerHTML;
            target.appendChild(tr);
            domTarget = '#baseTableRvc';
            tr = document.createElement('tr');
            innerHTML = "";
            let dtmp = new Date();
            let dayName = weekday[dtmp.getDay()];
            for (let venta in data.venta) {
                dtmp = new Date(data.venta[venta].fecha + " 18:00:00");
                dayName = weekday[dtmp.getDay()];
                innerHTML += "<td>" + dayName + "</td><td>" + data.venta[venta].fecha + "</td>";
                innerHTML += "<td class='text-right'>" + formatter2.format(data.venta[venta].ventaNeta) + "</td>";
                innerHTML += "<td class='text-right'>" + formatter2.format(data.venta[venta].ventaBruta) + "</td>";
                innerHTML += "<td class='text-right'>" + formatter2.format(data.venta[venta].impuesto) + "</td>";
                innerHTML += "<td class='text-right'>" + formatter2.format(data.venta[venta].descuentos) + "</td>";
                tr.innerHTML = innerHTML;
                target.appendChild(tr);
                tr = document.createElement('tr');
                innerHTML = "";
            }

            innerHTML += "<td></td><td><b>Total</b></td>";
            innerHTML += "<td class='text-right'>" + formatter2.format(data.ventaGlobal[0].ventaNeta) + "</td>";
            innerHTML += "<td class='text-right'>" + formatter2.format(data.ventaGlobal[0].ventaBruta) + "</td>";
            innerHTML += "<td class='text-right'>" + formatter2.format(data.ventaGlobal[0].impuesto) + "</td>";
            innerHTML += "<td class='text-right'>" + formatter2.format(data.ventaGlobal[0].descuentos) + "</td>";
            tr.innerHTML = innerHTML;
            target.appendChild(tr);
            tr = document.createElement('tr');
            innerHTML = "";

            // domTarget = '#weekHeadRvc';
            // tr = document.createElement('tr');
            // target = document.querySelector(domTarget);
            // innerHTML = "<th>Promedio Sem</th><th>Semana</th><th>Total</th><th>%LY</th>";
            // for (var i = 0; i < data.rvcnames.length; i++) {
            //     innerHTML += "<th>" + data.rvcnames[i].rvc + "</th><th>%LY</th>";
            // }
            // tr.innerHTML = innerHTML;
            // target.appendChild(tr);

            // var datasets = [];
            // datasets[0] = [{
            //     label: 'Actual',
            //     "data": []
            // }, {
            //     label: 'Anterior',
            //     "data": []
            // }, {
            //     label: '2 Años',
            //     "data": []
            // }];
            // datasets[1] = [{
            //     label: 'Actual',
            //     "data": []
            // }, {
            //     label: 'Anterior',
            //     "data": []
            // }, {
            //     label: '2 Años',
            //     "data": []
            // }];
            // datasets[2] = [{
            //     label: 'Actual',
            //     "data": []
            // }, {
            //     label: 'Anterior',
            //     "data": []
            // }, {
            //     label: '2 Años',
            //     "data": []
            // }];

            // var labels = []
            // for (let semana in data.weekSales) {
            //     if (data.weekSales[semana][0]) {
            //         tr = document.createElement('tr');
            //         datasets[0][0].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].actual[0]
            //         });
            //         datasets[0][1].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].anterior[0]
            //         });
            //         datasets[0][2].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].tanios[0]
            //         });
            //         datasets[1][0].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].actual[1]
            //         });
            //         datasets[1][1].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].anterior[1]
            //         });
            //         datasets[1][2].data.push({
            //             "x": "Sem " + semana,
            //             "y": data.weekSales[semana][0].tanios[1]
            //         });
            //         datasets[2][0].data.push({
            //             "x": "Sem " + semana,
            //             "y": Math.round(data.weekSales[semana][0].actual[2])
            //         });
            //         datasets[2][1].data.push({
            //             "x": "Sem " + semana,
            //             "y": Math.round(data.weekSales[semana][0].anterior[2])
            //         });
            //         datasets[2][2].data.push({
            //             "x": "Sem " + semana,
            //             "y": Math.round(data.weekSales[semana][0].tanios[2])
            //         });

            //         labels.push("Sem " + semana);

            //         innerHTML = "<td>" + formatter2.format(data.weekSales[semana][0].actual[0] / 7) + "</td><td>Sem " +
            //             semana + "</td><td>" + formatter2.format(data.weekSales[semana][0].actual[0]) + "</td><td class='" +
            //             (data.weekSales[semana][-1] >= 1 ? "text-success" : "text-danger") + "'>" + formatter2.format(data
            //                 .weekSales[semana][-1] * 100) + "</td>";
            //         for (let rvcId in data.weekSales[semana]) {
            //             if (rvcId > 0) {
            //                 innerHTML += "<td>" + formatter2.format(data.weekSales[semana][rvcId][0].actual) + "</td>";
            //                 innerHTML += "<td class='" + (data.weekSales[semana][rvcId][3] >= 1 ? "text-success" :
            //                         "text-danger") + "'>" + formatter2.format(data.weekSales[semana][rvcId][3] * 100) +
            //                     "</td>";
            //             }
            //         }
            //         tr.innerHTML = innerHTML;
            //         target.appendChild(tr);
            //     }
            // }

            // chartCfg[0].data.datasets = datasets[0];
            // chartCfg[0].data.labels = labels;

            // if (reportChart[0]) {
            //     reportChart[0].destroy();
            // }
            // reportChart[0] = new Chart(
            //     ctx[0],
            //     chartCfg[0]
            // );

            // domTarget = '#tblreportChart1';
            // tr = document.createElement('tr');
            // innerHTML = "";
            // target = document.querySelector(domTarget);
            // target.innerHTML = innerHTML;
            // for (let datos in datasets[0][0].data) {
            //     tr = document.createElement('tr');
            //     innerHTML = "<td>" + datasets[0][0].data[datos].x + "</td><td>" + formatter2.format(datasets[0][0].data[
            //         datos].y) + "</td><td>" + formatter2.format(datasets[0][1].data[datos].y) + "</td>";
            //     tr.innerHTML = innerHTML;
            //     target.appendChild(tr);
            // }


            // chartCfg[1].data.datasets = datasets[1];
            // chartCfg[1].data.labels = labels;

            // if (reportChart[1]) {
            //     reportChart[1].destroy();
            // }
            // reportChart[1] = new Chart(
            //     ctx[1],
            //     chartCfg[1]
            // );

            // domTarget = '#tblreportChart2';
            // tr = document.createElement('tr');
            // innerHTML = "";
            // target = document.querySelector(domTarget);
            // target.innerHTML = innerHTML;
            // for (let datos in datasets[1][0].data) {
            //     tr = document.createElement('tr');
            //     innerHTML = "<td>" + datasets[1][0].data[datos].x + "</td><td>" + formatter2.format(datasets[1][0].data[
            //         datos].y) + "</td><td>" + formatter2.format(datasets[1][1].data[datos].y) + "</td>";
            //     tr.innerHTML = innerHTML;
            //     target.appendChild(tr);
            // }

            // chartCfg[2].data.datasets = datasets[2];
            // chartCfg[2].data.labels = labels;

            // if (reportChart[2]) {
            //     reportChart[2].destroy();
            // }
            // reportChart[2] = new Chart(
            //     ctx[2],
            //     chartCfg[2]
            // );


            // domTarget = '#tblreportChart3';
            // tr = document.createElement('tr');
            // innerHTML = "";
            // target = document.querySelector(domTarget);
            // target.innerHTML = innerHTML;
            // for (let datos in datasets[2][0].data) {
            //     tr = document.createElement('tr');
            //     innerHTML = "<td>" + datasets[2][0].data[datos].x + "</td><td>" + formatter2.format(datasets[2][0].data[
            //         datos].y) + "</td><td>" + formatter2.format(datasets[2][1].data[datos].y) + "</td>";
            //     tr.innerHTML = innerHTML;
            //     target.appendChild(tr);
            // }


            endLoadingData();
        }

        function makeTableOld(data) {
            clearTable();


            let htmlTemplate =
                "<td class=\"text-left\">:RvcName</td><td class=\"text-right\">:gb</td><td class=\"text-right\">:gl</td><td class=\"text-right\">:gd</td><td class=\"text-right\">:gn</td><td class=\"text-right\">:guests</td><td class=\"text-right\">:nsb</td><td class=\"text-right\">:nsl</td><td class=\"text-right\">:nsd</td><td class=\"text-right\">:nsn</td><td class=\"text-right\">:netSales</td><td class=\"text-right\">:avgb</td><td class=\"text-right\">:avgl</td><td class=\"text-right\">:avgd</td><td class=\"text-right\">:avgn</td><td class=\"text-right\">:avgCheck</td>";

            let domTarget = '#baseTableRvc';
            let tr = document.createElement('tr');
            let target = document.querySelector(domTarget);
            let innerHTML = "";
            var formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
                maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
            });
            var formatter2 = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            let chartValues = [];
            let chartLabels = [];

            for (var i = 0; i < data.rvcs.length; i++) {
                tr = document.createElement('tr');
                innerHTML = htmlTemplate;
                /*
                tr.id = "MjGrp"+data.rvcs[i].idMajor;
                tr.dataset.open = 0;
                */

                innerHTML = innerHTML.replace(':RvcName', "<b>" + data.rvcs[i].RvcName + "</b>");
                innerHTML = innerHTML.replace(':gb', formatter2.format(data.rvcs[i].gb));
                innerHTML = innerHTML.replace(':gl', formatter2.format(data.rvcs[i].gl));
                innerHTML = innerHTML.replace(':gd', formatter2.format(data.rvcs[i].gd));
                innerHTML = innerHTML.replace(':gn', formatter2.format(data.rvcs[i].gn));
                innerHTML = innerHTML.replace(':guests', formatter2.format(data.rvcs[i].guests));
                innerHTML = innerHTML.replace(':nsb', formatter2.format(data.rvcs[i].nsb));
                innerHTML = innerHTML.replace(':nsl', formatter2.format(data.rvcs[i].nsl));
                innerHTML = innerHTML.replace(':nsd', formatter2.format(data.rvcs[i].nsd));
                innerHTML = innerHTML.replace(':nsn', formatter2.format(data.rvcs[i].nsn));
                innerHTML = innerHTML.replace(':netSales', formatter2.format(data.rvcs[i].netSales));
                innerHTML = innerHTML.replace(':avgb', formatter2.format(data.rvcs[i].avgb));
                innerHTML = innerHTML.replace(':avgl', formatter2.format(data.rvcs[i].avgl));
                innerHTML = innerHTML.replace(':avgd', formatter2.format(data.rvcs[i].avgd));
                innerHTML = innerHTML.replace(':avgn', formatter2.format(data.rvcs[i].avgn));
                innerHTML = innerHTML.replace(':avgCheck', formatter2.format(data.rvcs[i].avgCheck));

                tr.innerHTML = innerHTML;
                target.appendChild(tr);
            }

            htmlTemplate =
                "<td class=\"text-left\">:location</td><td class=\"text-right\">:gb</td><td class=\"text-right\">:gl</td><td class=\"text-right\">:gd</td><td class=\"text-right\">:gn</td><td class=\"text-right\">:guests</td><td class=\"text-right\">:nsb</td><td class=\"text-right\">:nsl</td><td class=\"text-right\">:nsd</td><td class=\"text-right\">:nsn</td><td class=\"text-right\">:netSales</td><td class=\"text-right\">:avgb</td><td class=\"text-right\">:avgl</td><td class=\"text-right\">:avgd</td><td class=\"text-right\">:avgn</td><td class=\"text-right\">:avgCheck</td>";
            domTarget = '#baseTableLocation';
            tr = document.createElement('tr');
            target = document.querySelector(domTarget);
            innerHTML = "";

            for (var i = 0; i < data.locations.length; i++) {
                tr = document.createElement('tr');
                innerHTML = htmlTemplate;

                innerHTML = innerHTML.replace(':location', "<b>" + data.locations[i].location + "</b>");
                innerHTML = innerHTML.replace(':gb', formatter2.format(data.locations[i].gb));
                innerHTML = innerHTML.replace(':gl', formatter2.format(data.locations[i].gl));
                innerHTML = innerHTML.replace(':gd', formatter2.format(data.locations[i].gd));
                innerHTML = innerHTML.replace(':gn', formatter2.format(data.locations[i].gn));
                innerHTML = innerHTML.replace(':guests', formatter2.format(data.locations[i].guests));
                innerHTML = innerHTML.replace(':nsb', formatter2.format(data.locations[i].nsb));
                innerHTML = innerHTML.replace(':nsl', formatter2.format(data.locations[i].nsl));
                innerHTML = innerHTML.replace(':nsd', formatter2.format(data.locations[i].nsd));
                innerHTML = innerHTML.replace(':nsn', formatter2.format(data.locations[i].nsn));
                innerHTML = innerHTML.replace(':netSales', formatter2.format(data.locations[i].netSales));
                innerHTML = innerHTML.replace(':avgb', formatter2.format(data.locations[i].avgb));
                innerHTML = innerHTML.replace(':avgl', formatter2.format(data.locations[i].avgl));
                innerHTML = innerHTML.replace(':avgd', formatter2.format(data.locations[i].avgd));
                innerHTML = innerHTML.replace(':avgn', formatter2.format(data.locations[i].avgn));
                innerHTML = innerHTML.replace(':avgCheck', formatter2.format(data.locations[i].avgCheck));

                tr.innerHTML = innerHTML;
                target.appendChild(tr);
            }


            let dataPreferences = {
                labels: chartLabels,
                series: chartValues
            };

            let optionsPreferences = {
                height: '260px',
                chartPadding: 15,
                labelOffset: 60,
                labelDirection: 'explode'
            };
            Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);
            endLoadingData();
        }

        function clearTable() {
            var tabla = document.getElementById("baseTableRvc");
            tabla.innerHTML = "";
            tabla = document.getElementById("baseHeadRvc");
            tabla.innerHTML = "";
            tabla = document.getElementById("baseTableGlobal");
            tabla.innerHTML = "";
            // tabla = document.getElementById("weekTableRvc");
            // tabla.innerHTML = "";
            // tabla = document.getElementById("weekHeadRvc");
            // tabla.innerHTML = "";
        }

        function validParams() {
            if ($("#daterange").val() != "" && $("#location").val() != "" && $("#daterange").val() != null && $("#location")
                .val() != null)
                return true;
            return false;
        }
    </script>
    <style>
        .filter-components {
            background-color: #fff !important;
            border: 1px solid #aaa !important;
            border-radius: 4px !important;
            color: #444 !important;
            line-height: 24px !important;
        }

        .table th {
            min-width: 80px !important;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #fff;
        }

        .table td {
            min-width: 80px !important;
            white-space: nowrap;
        }
    </style>
@endsection
