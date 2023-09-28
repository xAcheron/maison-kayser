@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'DayPart'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-info">
                    <div class="card-icon">
                        <i class="material-icons">timeline</i>
                    </div>
                    <h4 class="card-title">Venta Semanal Producto
                        <small> - Filters</small>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            Producto:<br>
                            <select id="producto" name="producto[]" title="Seleccione un producto" tabindex="-98"
                                style="width: 100%" multiple>
                                <option>Por favor seleccione un producto</option>
                            </select>
                        </div>
                        <div class="col-2">
                            Business Dates:<br>
                            <input type="text" class="filter-components" style="width:100%;" name="daterange"
                                id="daterange" value="{{ date('Y-m-d') }} - {{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-3">
                            Location:<br>
                            <select class="select2-item" id="location" data-size="7" style="width:100%;" title="Location">
                                <option value="0" disabled selected>Select a location</option>
                                @if (!empty($hierachy))
                                    @foreach ($hierachy as $location)
                                        <option value="{{ $location->id }}" data-type="{{ $location->tipo }}">
                                            {{ $location->nombre }}</option>
                                    @endforeach
                                @else
                                    <option value="tzuco">Tzuco</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-2">
                            Segmento:<br>
                            <select class="select2-item" id="tier" data-size="7" style="width:100%;" title="Tier">
                                <option value="0" disabled selected>Select a Tier</option>
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier->idTier }}" data-type="">{{ $tier->tier }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            Cajón I:<br>
                            <input type="checkbox" id="detailBox">
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Venta Semanal <span id="txtRangeDate"></span></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 60vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead id="prodHead">
                                        </head>
                                    <tbody id="prodTable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>Grafica Venta Semanal</h4>
                </div>
                <div class="card-body">
                    <canvas id="reportChart1"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div id="formsarea"></div>
@endsection
@section('aditionalScripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/chart.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/Chart.min.css" rel="stylesheet">
    <style>
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
    <script src="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
    <script type="text/javascript">
        var ctx = [];
        var reportChart = [];

        ctx[0] = document.getElementById('reportChart1');

        const chartCfg = [{
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: ''
                    }
                }
            },
        }];


        $(function() {

            $('input[name="daterange"]').daterangepicker({
                opens: 'right',
                minYear: 2019,
                maxYear: {{ date('Y') }},
                maxSpan: {
                    days: 7
                },
                locale: {
                    format: 'YYYY-MM-DD',
                    firstDay: 1
                },
                startDate: moment().startOf('isoWeek'),
                endDate: moment(),
                ranges: {
                    'This Week': [moment().startOf('isoWeek'), moment()],
                    'Last Week': [moment().startOf('isoWeek').subtract(7, 'days'), moment().startOf(
                        'isoWeek').subtract(1, 'days')],
                    '2 Weeks': [moment().startOf('isoWeek').subtract(14, 'days'), moment().startOf(
                        'isoWeek').subtract(8, 'days')],
                    '3 Weeks': [moment().startOf('isoWeek').subtract(21, 'days'), moment().startOf(
                        'isoWeek').subtract(15, 'days')],
                    '4 Weeks': [moment().startOf('isoWeek').subtract(28, 'days'), moment().startOf(
                        'isoWeek').subtract(22, 'days')]
                }
            });

            $('.select2-item').select2({
                templateResult: formatState
            });

            $('#producto').select2({
                ajax: {
                    url: "{{ route('getMicrosItem') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 3,
                placeholder: 'Search for a repository',
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: formatRepoProd,
                templateSelection: formatRepoSelectionProd
            });

            function formatRepoProd(repo) {
                if (repo.loading) {
                    return repo.text;
                }

                var markup = "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'>" + repo.id + " - " + repo.name + "</div>" +
                    "</div></div>";
                return markup;
            }

            function formatRepoSelectionProd(repo) {
                return repo.id || repo.name;
            }

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
                    let url = "{{ route('getReport', ['id' => 12, 'format' => 'xlsx']) }}";
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

                    var tier = document.createElement('input');
                    tier.setAttribute('type', 'hidden');
                    tier.setAttribute('name', 'tier');
                    tier.setAttribute('value', $("#tier").val());
                    myForm.appendChild(tier);

                    var detailBox = document.createElement('input');
                    detailBox.setAttribute('type', 'hidden');
                    detailBox.setAttribute('name', 'detailBox');
                    detailBox.setAttribute('value', $("#detailBox").prop('checked'));
                    myForm.appendChild(detailBox);

                    var producto = document.createElement('input');
                    producto.setAttribute('type', 'hidden');
                    producto.setAttribute('name', 'producto');
                    producto.setAttribute('value', $("#producto").val());
                    myForm.appendChild(producto);

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
                        producto: $("#producto").val(),
                        location: $("#location").val(),
                        tier: $("#tier").val(),
                        detailBox: $("#detailBox").prop('checked'),
                        _token: "{{ csrf_token() }}"
                    };

                    $.ajax({
                        type: "POST",
                        url: "{{ route('getReport', ['id' => 12, 'format' => 'json']) }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success == true) {
                                if (msg.data != undefined && msg.data != null) {
                                    makeTable(msg.data);
                                    makeGraph1(msg.data.prodGraph);
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

        function makeGraph1(data) {
            if (reportChart[0]) {
                reportChart[0].destroy()
            }
            var id = 0;
            var dataset = [];
            var labels = [];
            var i = 0;
            chartCfg[0].data.datasets = []
            data.forEach((element, index) => {
                if (element.id != id) {
                    if (index > 0) {
                        i++;
                    }
                    dataset = []
                    id = element.id
                }
                dataset.push(element.cantidad)
                if (element.semana > labels.length && !
                    labels.includes(element.semana)) {
                    labels.push(`Sem ${element.semana}`)
                }
                chartCfg[0].data.datasets[i] = {
                    label: id,
                    data: dataset,
                }
            });

            chartCfg[0].data.labels = labels
            reportChart[0] = new Chart(
                ctx[0],
                chartCfg[0]
            );
        }

        function loadingData() {
            Swal.showLoading();
        }

        function endLoadingData() {
            Swal.close();
        }

        function makeTable(data) {
            clearTable();

            let htmlTemplate =
                "<td class=\"text-left\">:producto</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:vlun</td><td class=\"text-right\">:clun</td><td class=\"text-right\">:vmar</td><td class=\"text-right\">:cmar</td><td class=\"text-right\">:vmie</td><td class=\"text-right\">:cmie</td><td class=\"text-right\">:vjue</td><td class=\"text-right\">:cjue</td><td class=\"text-right\">:vvie</td><td class=\"text-right\">:cvie</td><td class=\"text-right\">:vsab</td><td class=\"text-right\">:csab</td><td class=\"text-right\">:vdom</td><td class=\"text-right\">:cdom</td><td class=\"text-right\">:venta</td><td class=\"text-right\">:cantidad</td>";
            if (data.detailBox)
                htmlTemplate += "<td class=\"text-right\">:cantidadCajon</td>";
            let htmlHeaders =
                "<th>Producto</th><th>Sucursal</th><th>Lun $</th><th>Lun #</th><th>Mar $</th><th>Mar #</th><th>Mie $</th><th>Mie #</th><th>Jue $</th><th>Jue #</th><th>Vie $</th><th>Vie #</th><th>Sab $</th><th>Sab #</th><th>Dom $</th><th>Dom #</th><th>Venta Semanal</th><th>Cantidad Semanal</th>";
            if (data.detailBox)
                htmlHeaders += "<th>Cajon I.</td>";
            let domTarget = '#prodHead';
            let tr = document.createElement('tr');
            let target = document.querySelector(domTarget);
            let innerHTML = "";

            innerHTML = htmlHeaders;
            tr.innerHTML = innerHTML;
            target.appendChild(tr);

            tr = document.createElement('tr');
            target = document.querySelector(domTarget);
            domTarget = '#prodTable';
            innerHTML = "";

            var formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            var formatter2 = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            let chartValues = [];
            let chartLabels = [];
            var i = 0;
            if (data.prods.length > 0) {
                for (var i = 0; i < data.prods.length; i++) {
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':producto', "<b>" + data.prods[i].idItemMicros + "</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.prods[i].idSucMicros + "</b>");
                    innerHTML = innerHTML.replace(':vlun', formatter2.format(data.prods[i].vlun));
                    innerHTML = innerHTML.replace(':clun', data.prods[i].clun);
                    innerHTML = innerHTML.replace(':vmar', formatter2.format(data.prods[i].vmar));
                    innerHTML = innerHTML.replace(':cmar', data.prods[i].cmar);
                    innerHTML = innerHTML.replace(':vmie', formatter2.format(data.prods[i].vmie));
                    innerHTML = innerHTML.replace(':cmie', data.prods[i].cmie);
                    innerHTML = innerHTML.replace(':vjue', formatter2.format(data.prods[i].vjue));
                    innerHTML = innerHTML.replace(':cjue', data.prods[i].cjue);
                    innerHTML = innerHTML.replace(':vvie', formatter2.format(data.prods[i].vvie));
                    innerHTML = innerHTML.replace(':cvie', data.prods[i].cvie);
                    innerHTML = innerHTML.replace(':vsab', formatter2.format(data.prods[i].vsab));
                    innerHTML = innerHTML.replace(':csab', data.prods[i].csab);
                    innerHTML = innerHTML.replace(':vdom', formatter2.format(data.prods[i].vdom));
                    innerHTML = innerHTML.replace(':cdom', data.prods[i].cdom);
                    innerHTML = innerHTML.replace(':venta', formatter2.format(data.prods[i].venta));
                    innerHTML = innerHTML.replace(':cantidad', data.prods[i].cantidad);
                    if (data.detailBox)
                        innerHTML = innerHTML.replace(':cantidadCajon', data.prods[i].cantidadCajon);

                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);
                }
            }
            endLoadingData();
        }

        function clearTable() {
            var tabla = document.getElementById("prodTable");
            tabla.innerHTML = "";
            tabla = document.getElementById("prodHead");
            tabla.innerHTML = "";
        }

        function validParams() {
            if ($("#daterange").val() != "" && $("#producto").val() != "" && $("#location").val() != "" && $("#daterange")
                .val() != null && $("#location").val() != null)
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
