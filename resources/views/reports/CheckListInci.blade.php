@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'reports'])
@section('content')
    <style>
        .filter-components {
            background-color: #fff !important;
            border: 1px solid #aaa !important;
            border-radius: 4px !important;
            color: #444 !important;
            line-height: 24px !important;
            padding-left: 0.5rem;
        }

        .table-condensed>tbody>tr>td,
        .table-condensed>tbody>tr>th,
        .table-condensed>tfoot>tr>td,
        .table-condensed>tfoot>tr>th,
        .table-condensed>thead>tr>td,
        .table-condensed>thead>tr>th {
            text-align: left;
        }
    </style>
    <div class="row">
        <div class="card">
            <div class="card-body row align-items-center">
                <div class="form-group col-lg-2">
                    <label for="mes" style="position: unset;">Fecha</label>
                    <input type="text" name="daterange" id="daterange" class="filter-components" style="width: 100%">
                </div>
                <div class="form-group col-lg-3">
                    <label for="">Sucursal</label>
                    <select name="sucursal" id="location" class="select2-item" style="width:100%;">
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
                <div class="form-group col-lg-3">
                    <label for="">Evaluación</label>
                    <select name="sucursal" id="evaluacion" class="select2-item" style="width:100%;">
                        <option value="" disabled selected>Seleccione Evaluación</option>
                    </select>
                </div>
                <div class="form-group col-lg-1">
                    <label for="">Cantidad</label>
                    <select name="cantidad" id="cantidad" class="select2-item" style="width: 100%">
                        <option value="10" data-type="2" selected>Top 10</option>
                        <option value="20" data-type="2">Top 20</option>
                        <option value="30" data-type="2">Top 30</option>
                        <option value="40" data-type="2">Top 40</option>
                        <option value="50" data-type="2">Top 50</option>
                        <option value="All" data-type="2">Todos</option>
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

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap">
                <div class="col-lg-6 col-sm-12">
                    <h4>Preguntas con mas incidencias</h4>
                    <canvas id="chartBars"></canvas>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <h4>Distribucion por seccion</h4>
                    <canvas id="chartPie"></canvas>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <h4>Historico de incidencias</h4>
                    <canvas id="chartLine"></canvas>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th>Pregunta</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLegends">

                        </tbody>
                    </table>
                </div>
                <div class="col-lg-12 col-sm-12">
                    <div style="overflow: auto">
                        <table class="table">
                            <thead id="theadSuc"></thead>
                            <tbody id="tbodySuc"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('aditionalScripts')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        var chartBars, chartLine, chartPie;

        $('#runReport').on("click", function(e) {
            if (validParams()) {
                loadingData();

                var params = {
                    daterange: $("#daterange").val(),
                    location: $("#location").val(),
                    typeLoc: $('#location :selected').data('clas'),
                    idEvaluacion: $('#evaluacion').val(),
                    cantidad: $('#cantidad').val(),
                    _token: "{{ csrf_token() }}",
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('getReport', ['id' => 27, 'format' => 'json']) }}",
                    data: params,
                    success: function(msg) {
                        if (msg.success == true) {
                            if (msg.data != undefined && msg.data != null) {
                                getChartBar(msg.data.inciPregunta);
                                getChartLine(msg.data.inciMonth);
                                getChartPie(msg.data.inciSec);
                                tableSuc(msg.data.pregSuc);
                                // tableCollapse(msg.data.pregCol);
                                endLoadingData();
                            } else {
                                chartBars.destroy();
                                chartLine.destroy();
                                chartPie.destroy();
                            }
                        }
                    },
                    error: function() {
                        chartBars.destroy();
                        chartLine.destroy();
                        chartPie.destroy();
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

        function tableCollapse(data, index) {
            if (data != undefined) {
                var target;
                const tdCol = document.createElement('td');
                const table = document.createElement('table');
                const thead = document.createElement('thead');
                const tbody = document.createElement('tbody');
                const trHead = document.createElement('tr');
                const thSuc = document.createElement('th');
                const thCount = document.createElement('th');

                thSuc.innerHTML = 'Sucursal';
                thCount.innerHTML = 'Cantidad';

                tdCol.setAttribute('colspan', 2);

                trHead.appendChild(thSuc);
                trHead.appendChild(thCount);

                thead.appendChild(trHead)
                table.appendChild(thead);
                table.classList = 'table table-condensed';

                table.style.textAlign = 'left';

                target = $(`#${index}`)
                data.suc.forEach((element, index) => {
                    const tr = document.createElement('tr');
                    var tdSuc = document.createElement('td');
                    var tdCount = document.createElement('td');
                    tdSuc.innerHTML = element;
                    tdCount.innerHTML = data.count[index];
                    tr.appendChild(tdSuc);
                    tr.appendChild(tdCount);

                    tbody.appendChild(tr);
                });

                table.appendChild(tbody);
                tdCol.appendChild(table);
                target.append(tdCol)
            }
        }

        function tableSuc(data) {
            const tbHeader = document.getElementById('theadSuc');
            const tbBody = document.getElementById('tbodySuc');
            tbHeader.innerHTML = '';
            tbBody.innerHTML = '';
            const trHead = document.createElement('tr');

            data.headers.forEach(element => {
                const thHead = document.createElement('th');
                thHead.innerHTML = element;
                trHead.appendChild(thHead);
            });
            tbHeader.appendChild(trHead);

            data.body.forEach((element) => {
                const trBody = document.createElement('tr');
                element.forEach((element, index) => {
                    const td = document.createElement('td');
                    if (element != 0) {
                        td.innerHTML = element;
                    }
                    if (index == 0) {
                        td.style.width = '100%'
                    }
                    trBody.appendChild(td);
                });
                tbBody.appendChild(trBody);
            });
        }

        function getChartPie(data) {
            const ctx = document.getElementById('chartPie');
            if (!chartPie) {
                chartPie = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Numero de incidencias',
                            data: data.data,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            } else {
                chartPie.data.labels = data.labels;
                chartPie.data.datasets[0].data = data.data;
                chartPie.update()
            }
        }

        function getChartLine(data) {


            const ctx = document.getElementById('chartLine');
            if (!chartLine) {
                chartLine = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets,
                    },
                    options: {
                        plugins: {
                            htmlLegend: {
                                containerID: 'legend-container',
                            },
                            legend: {
                                display: false,
                                position: 'bottom',
                            }
                        },
                    },
                    plugins: [htmlLegendPlugin],
                });
            } else {
                chartLine.data.labels = data.labels;
                chartLine.data.datasets = data.datasets;
                chartLine.update()
            }
        }

        function getChartBar(data) {
            const ctx = document.getElementById('chartBars');
            if (!chartBars) {
                chartBars = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Numero de incidencias',
                            data: data.data,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        aspectRatio: 3 / 2,
                        responsive: true,
                        indexAxis: 'y',
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                chartBars.data.labels = data.labels;
                chartBars.data.datasets[0].data = data.data;
                chartBars.update()
            }
        }

        function validParams() {
            if ($("#daterange").val() != "" && $("#location").val() != "" && $("#daterange").val() != null && $("#location")
                .val() != null && $('#evaluacion').val() != "" && $('#evaluacion').val() != null)
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

        $('#location').on("change", function() {
            var evalucacionSelect = document.getElementById('evaluacion');
            evalucacionSelect.innerHTML = '<option value="" disabled selected>Seleccione Evaluación</option>'

            var params = {
                idSucursal: $('#location').val(),
                api: "1",
                _token: "{{ csrf_token() }}"
            };
            $.ajax({
                type: "POST",
                data: params,
                url: "{{ route('getListEvaluacionesWeb') }}",
                success: function(msg) {

                    if (msg.length > 0) {

                        msg.forEach(element => {
                            evalucacionSelect.appendChild(option(element));
                        });

                    } else {

                    }

                },
                error: function() {

                }
            });
        })

        const getOrCreateLegendList = (chart, id) => {
            const legendContainer = document.getElementById('tbodyLegends');

            return legendContainer;
        };

        const htmlLegendPlugin = {
            id: 'htmlLegend',
            afterUpdate(chart, args, options) {
                const ul = getOrCreateLegendList(chart, options.containerID);

                // Remove old legend items
                while (ul.firstChild) {
                    ul.firstChild.remove();
                }

                // Reuse the built-in legendItems generator
                const items = chart.options.plugins.legend.labels.generateLabels(chart);
                const datasets = chart.data.datasets;

                items.forEach((item, index) => {
                    const trCol = document.createElement('tr');
                    trCol.classList.add('collapse');
                    trCol.id = index;
                    // data-toggle="collapse" href="#collapseExample"

                    const li = document.createElement('tr');
                    li.style.alignItems = 'center';
                    li.style.cursor = 'pointer';
                    li.style.marginLeft = '10px';

                    li.onclick = () => {
                        if ($(`#${index}`).hasClass('show')) {
                            $(`#${index}`).collapse('hide');
                        } else {
                            $(`#${index}`).collapse('show');
                        }
                    };

                    // Color box
                    const tdBox = document.createElement('td');
                    const boxSpan = document.createElement('span');
                    boxSpan.style.background = item.fillStyle;
                    boxSpan.style.borderColor = item.strokeStyle;
                    boxSpan.style.borderWidth = item.lineWidth + 'px';
                    boxSpan.style.display = 'inline-block';
                    boxSpan.style.height = '20px';
                    boxSpan.style.marginRight = '10px';
                    boxSpan.style.width = '20px';

                    // Text
                    const tdText = document.createElement('td');
                    const textContainer = document.createElement('p');
                    textContainer.style.color = item.fontColor;
                    textContainer.style.margin = 0;
                    textContainer.style.padding = 0;
                    textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

                    const text = document.createTextNode(item.text);
                    textContainer.appendChild(text);

                    tdBox.appendChild(boxSpan);
                    li.appendChild(tdBox);
                    tdText.appendChild(textContainer);
                    li.appendChild(tdText);
                    ul.appendChild(li);
                    ul.appendChild(trCol);
                    tableCollapse(datasets[index].dataPreg, index);
                });
            }
        };

        function option(data) {
            var option = document.createElement("option");
            option.setAttribute("value", data.idEvaluacion);
            option.setAttribute("data-type", "");
            option.innerHTML = data.nombre;

            return option;
        }

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
                    return $('<span class="' + (tipo == 2 ? "ml-2" : "font-weight-bold") + '">' + state.text +
                        '</span>');
                } else {
                    return
                    state.text;
                }
            } else {
                return state.text;
            }
        }
    </script>
@endsection
