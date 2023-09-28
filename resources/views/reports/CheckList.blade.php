@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'reports'])
@section('content')
    <style>
        td {
            text-align: right !important
        }

        .table>thead>tr>th {
            font-size: 0.95rem;
            font-weight: 500;
            border-top-width: 0;
            border-bottom-width: 1px;
        }
    </style>
    <div class="row">
        <div class="card">
            <div class="card-body row align-items-center">
                <div class="form-group col-lg-2">
                    <label for="mes" style="position: unset;">Año</label>
                    <select name="anio" id="anio" class="select2-item" style="width: 100%">
                        <option value="" disabled selected>Seleccione una opción</option>
                        @if (!empty($years))
                            @foreach ($years as $item)
                                <option value="{{ $item->year }}">{{ $item->year }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group col-lg-2">
                    <label for="mes" style="position: unset;">Mes</label>
                    <select name="mes" id="mes" class="select2-item" style="width: 100%">
                        <option value="" disabled selected>Seleccione una opción</option>
                        <option value="01">Enero</option>
                        <option value="02">Febrero</option>
                        <option value="03">Marzo</option>
                        <option value="04">Abril</option>
                        <option value="05">Mayo</option>
                        <option value="06">Junio</option>
                        <option value="07">Julio</option>
                        <option value="08">Agosto</option>
                        <option value="09">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="form-group col-lg-3">
                    <label for="">Sucursal</label>
                    <select name="sucursal" id="sucursal" class="select2-item" style="width:100%;">
                        <option value="" disabled selected>Seleccione Sucursal</option>
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
                <div class="form-group col-lg-3">
                    <label for="">Evaluación</label>
                    <select name="sucursal" id="evaluacion" class="select2-item" style="width:100%;">
                        <option value="" disabled selected>Seleccione Evaluación</option>
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
    <div class="row justify-content-center">
        <div class="row justify-content-between col-lg-6 p-0 mr-0">
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="ultimaEval"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconUltimo"></i>
                    </div>
                </div>
                <div class="card-footer">Ultima Evaluación</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="promMensual"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconMensual"></i>
                    </div>
                </div>
                <div class="card-footer" id="footerMensual">Promedio Mensual</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="promSemetral"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconSemestral"></i>
                    </div>
                </div>
                <div class="card-footer">Promedio Semestral</div>
            </div>
            <div class="col-sm-4 col-lg-5 card mx-2 mb-2">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <h2 id="promAnual"></h2>
                        <i class="material-icons" style="font-size: 32px; color: ;" id="iconAnual"></i>
                    </div>
                </div>
                <div class="card-footer">Promedio Anual</div>
            </div>
        </div>
        <div class="card col-lg-6 mb-0" id="chart">
            <div class="card-header h3">Desempeño mensual</div>
        </div>
        <div class="card col-lg-5 mb-0 mx-2" style="display: none" id="tableCard">
            <div class="card-header h3">Ultimos 5 CheckList</div>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Puntuación</th>
                        <th>Promedio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTable" class="text-right">
                </tbody>
            </table>
        </div>
        <div class="card col-lg-12 mb-0 mx-2" style="display: none; overflow-x: scroll;" id="distritalCard">
            <div class="card-header h3">Distrital</div>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Distrital</th>
                        <th>Vitrina</th>
                        <th scope="col">Puntaje</th>
                        <th>Cocina</th>
                        <th scope="col">Puntaje</th>
                        <th>Panaderia</th>
                        <th scope="col">Puntaje</th>
                        <th>Barra</th>
                        <th scope="col">Puntaje</th>
                        <th>Salon</th>
                        <th scope="col">Puntaje</th>
                        <th>MKT</th>
                        <th scope="col">Puntaje</th>
                        <th>Legal</th>
                        <th scope="col">Puntaje</th>
                        <th>Temporada</th>
                        <th scope="col">Puntaje</th>
                        <th>APP</th>
                        <th scope="col">Puntaje</th>
                        <th>General</th>
                        <th scope="col">Puntaje</th>
                        <th>Reportes</th>
                        <th scope="col">Puntaje</th>
                        <th>Actual</th>
                        <th>Por porcentaje</th>
                    </tr>
                </thead>
                <tbody id="bodyTableDistrital" class="text-right">
                </tbody>
            </table>
        </div>
        <div class="card col-lg-12 mb-0 mx-2" style="display: none; overflow-x: scroll;" id="sucursalCard">
            <div class="card-header h3">Sucursal</div>
            <table class="table table-bordered table-striped table-condensed" style="height: 250px;">
                <thead>
                    <tr>
                        <th>Sucursales</th>
                        <th>Vitrina</th>
                        <th scope="col">Puntaje</th>
                        <th>Cocina</th>
                        <th scope="col">Puntaje</th>
                        <th>Panaderia</th>
                        <th scope="col">Puntaje</th>
                        <th>Barra</th>
                        <th scope="col">Puntaje</th>
                        <th>Salon</th>
                        <th scope="col">Puntaje</th>
                        <th>MKT</th>
                        <th scope="col">Puntaje</th>
                        <th>Legal</th>
                        <th scope="col">Puntaje</th>
                        <th>Temporada</th>
                        <th scope="col">Puntaje</th>
                        <th>APP</th>
                        <th scope="col">Puntaje</th>
                        <th>General</th>
                        <th scope="col">Puntaje</th>
                        <th>Reportes</th>
                        <th scope="col">Puntaje</th>
                        <th>Actual</th>
                        <th>Por porcentaje</th>
                    </tr>
                </thead>
                <tbody id="bodyTableSucursal" class="text-right">
                </tbody>
            </table>
        </div>
    </div>
    <div id="formsarea"></div>
@endsection
@section('aditionalScripts')
    <style>
        .filter-components {
            background-color: #fff !important;
            border: 1px solid #aaa !important;
            border-radius: 4px !important;
            color: #444 !important;
            line-height: 24px !important;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://d3js.org/d3.v6.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        $('document').ready(function() {
            console.log(getVariableGetByName());
        })

        function getVariableGetByName() {
            var variables = {};
            var arreglos = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
                variables[key] = value;
            });
            return variables;
        }

        $("#exportReport").on("click", function(e) {

            if (validParams()) {
                let url = "{{ route('getReport', ['id' => 13, 'format' => 'xlsx']) }}";
                var myForm = document.createElement('form');
                myForm.setAttribute('action', url);
                myForm.setAttribute('method', 'post');
                myForm.setAttribute('hidden', 'true');
                myForm.setAttribute('target', '_blank');
                var location = document.createElement('input');
                location.setAttribute('type', 'hidden');
                location.setAttribute('name', 'location');
                location.setAttribute('value', $("#sucursal").val());
                myForm.appendChild(location);
                var evaluacion = document.createElement('input');
                evaluacion.setAttribute('type', 'hidden');
                evaluacion.setAttribute('name', 'idEvaluacion');
                evaluacion.setAttribute('value', $("#evaluacion").val());
                myForm.appendChild(evaluacion);
                var api = document.createElement('input');
                evaluacion.setAttribute('type', 'hidden');
                evaluacion.setAttribute('name', 'api');
                evaluacion.setAttribute('value', 1);
                myForm.appendChild(api);
                var daterange = document.createElement('input');
                daterange.setAttribute('type', 'hidden');
                daterange.setAttribute('name', 'daterange');
                daterange.setAttribute('value', `${$('#anio').val()}-${$('#mes').val()}-01`);
                myForm.appendChild(daterange);
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


        $('#sucursal').on("change", function() {
            var evalucacionSelect = document.getElementById('evaluacion');
            evalucacionSelect.innerHTML = '<option value="" disabled selected>Seleccione Evaluación</option>'

            var params = {
                idSucursal: $('#sucursal').val(),
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

        function loadingData() {
            Swal.showLoading();
        }

        function endLoadingData() {
            Swal.close();
        }

        $('#runReport').on('click', function() {
            var idSucursal = $('#sucursal').val();
            var evaluacion = $('#evaluacion').val();
            var mes = $('#mes').val();
            var anio = $('#anio').val();

            if (validParams()) {
                var params = {
                    idSucursal: idSucursal,
                    idEvaluacion: evaluacion,
                    mes: mes,
                    anio: anio,
                    api: "1",
                    _token: "{{ csrf_token() }}"
                };
                loadingData();
                $.ajax({
                    type: "POST",
                    data: params,
                    url: "{{ route('reporteChecklistWeb') }}",
                    success: function(msg) {
                        console.log(msg.data)
                        if (msg.success == true) {
                            if (msg.data.length > 0) {
                                if (Number.isInteger(parseInt(idSucursal)) && idSucursal != 4) {
                                    getDistrital();
                                    document.getElementById('distritalCard').style.display = "block"
                                    document.getElementById('sucursalCard').style.display = "block"
                                    document.getElementById('tableCard').style.display = "none"
                                } else {
                                    document.getElementById('tableCard').style.display = "block"
                                    document.getElementById('distritalCard').style.display = "none"
                                    document.getElementById('sucursalCard').style.display = "none"
                                }
                                if (msg.data[0].ultimo.length > 0) {
                                    setDatos(msg.data[0]);
                                    linechart(msg.data[0]);
                                    endLoadingData();
                                } else {
                                    Swal.fire('No se encontraron datos',
                                        'La sucursal que seleccionaste aun no tiene checklist creados',
                                        'error');
                                    clearTable('bodyTableSucursal');
                                    clearTable('bodyTableDistrital');
                                    clearTable('bodyTable');
                                    clearTable('ultimaEval');
                                    clearTable('promMensual');
                                    clearTable('promSemestral');
                                    clearTable('promAnual');
                                    clearTable('iconUltimo');
                                    clearTable('iconMensual');
                                    clearTable('iconSemestral');
                                    clearTable('iconAnual');
                                    clearTable('chart');
                                }

                            } else {
                                clearTable('bodyTableSucursal');
                                clearTable('bodyTableDistrital');
                                clearTable('bodyTable');
                                clearTable('ultimaEval');
                                clearTable('promMensual');
                                clearTable('promSemestral');
                                clearTable('promAnual');
                                clearTable('iconUltimo');
                                clearTable('iconMensual');
                                clearTable('iconSemestral');
                                clearTable('iconAnual');
                                clearTable('chart');
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

        })

        function clearTable(id) {
            var tabla = document.getElementById(id);
            tabla.innerHTML = "";
        }

        function makeTableDistritales(data) {

        }

        function makeTables(data, id) {
            var tableBody = document.getElementById(id);
            console.log(data)

            tableBody.innerHTML = "";

            var htmlTemplate =
                "<tr><th> :sucursal</th><td> :vtr%</td><td> :ptnVtr </td><td> :coc% </td><td> :ptnCoc </td><td> :pan% </td><td> :ptnPan </td><td> :barra% </td><td> :ptnBar </td><td> :sln% </td><td> :ptnSln </td><td> :mkt% </td><td> :ptnMkt </td><td> :lgl% </td><td> :ptnLgl </td><td> :temp% </td><td> :ptnTemp </td><td> :app% </td><td> :ptnApp </td><td> :grl% </td><td> :ptnGrl </td><td> :rep% </td><td> :ptnRep </td><td> :actual </td><td> :pct </td></tr>";

            data.forEach(suc => {
                var tr = htmlTemplate;
                tr = tr.replace(":sucursal", suc.nombre);
                tr = tr.replace(':vtr', Math.round(suc.clVit));
                tr = tr.replace(':coc', Math.round(suc.clCoc));
                tr = tr.replace(':pan', Math.round(suc.clPan));
                tr = tr.replace(':barra', Math.round(suc.clBar));
                tr = tr.replace(':sln', Math.round(suc.clSal));
                tr = tr.replace(':mkt', Math.round(suc.clMkt));
                tr = tr.replace(':lgl', Math.round(suc.clLeg));
                tr = tr.replace(':temp', Math.round(suc.clProds));
                tr = tr.replace(':app', Math.round(suc.clApp));
                tr = tr.replace(':grl', Math.round(suc.clGrl));
                tr = tr.replace(':rep', Math.round(suc.clRep));
                tr = tr.replace(':actual', Math.round(suc.Actual));
                tr = tr.replace(':ptnVtr', Math.round(suc.punVit));
                tr = tr.replace(':ptnCoc', Math.round(suc.punCoc));
                tr = tr.replace(':ptnPan', Math.round(suc.punPan));
                tr = tr.replace(':ptnBar', Math.round(suc.punBar));
                tr = tr.replace(':ptnSln', Math.round(suc.punSal));
                tr = tr.replace(':ptnMkt', Math.round(suc.punMkt));
                tr = tr.replace(':ptnLgl', Math.round(suc.punLeg));
                tr = tr.replace(':ptnTemp', Math.round(suc.punProds));
                tr = tr.replace(':ptnApp', Math.round(suc.punApp));
                tr = tr.replace(':ptnGrl', Math.round(suc.punGrl));
                tr = tr.replace(':ptnRep', Math.round(suc.punRep));
                tr = tr.replace(':pct', `${parseInt(suc.punVit) + parseInt(suc.punCoc) + parseInt(suc.punPan) +
                    parseInt(suc.punBar) + parseInt(suc.punSal) + parseInt(suc.punMkt) + parseInt(suc.punLeg) +
                    parseInt(suc.punProds)}%`);

                tableBody.innerHTML += tr;

            });
        }


        function getDistrital() {
            var idSucursal = $('#sucursal').val();

            var params = {
                daterange: `${$('#anio').val()}-${$('#mes').val()}-01`,
                location: idSucursal,
                idEvaluacion: $('#evaluacion').val(),
                api: "1",
                _token: "{{ csrf_token() }}"
            };
            $.ajax({
                type: "POST",
                data: params,
                url: "{{ route('getReport', ['id' => 13, 'format' => 'json']) }}",
                success: function(msg) {
                    if (msg.success == true) {
                        if (msg.data) {
                            makeTables(msg.data.sucursales, "bodyTableSucursal");
                            makeTables(msg.data.distritales, "bodyTableDistrital");
                        } else {

                        }
                    }
                },
                error: function() {

                }
            });
        }


        function setDatos(data) {
            var meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septimbre", "Octubre",
                "Noviembre", "Diciembre"
            ];

            console.log(data.ultimo);

            document.getElementById('ultimaEval').innerHTML = (data.ultimo[0].ultimo ?? "0") + "%";
            document.getElementById('iconUltimo').innerHTML = data.ultimo[1] == null ? 'remove' : data.ultimo[1]
                .ultimo < data.ultimo[0].ultimo ? 'arrow_upward' :
                'arrow_downward';
            // document.getElementById('mesUlt').innerHTML = $('#mes').val();
            document.getElementById('iconUltimo').style.color = data.ultimo[1] == null ? "#a09b9b" : data.ultimo[1]
                .ultimo < data.ultimo[0].ultimo ? '#04aa6d' : '#ff0000';


            document.getElementById('promMensual').innerHTML = (data.mensual ?? "0") + "%";
            document.getElementById('iconMensual').innerHTML = data.mensualAnt == null ? 'remove' : data.mensualAnt < data
                .mensual ? 'arrow_upward' :
                'arrow_downward';
            document.getElementById('iconMensual').style.color = data.mensualAnt == null ? "#a09b9b" : data
                .mensualAnt < data.mensual ? '#04aa6d' : '#ff0000';
            document.getElementById('footerMensual').innerHTML = "Promedio Mensual " + meses[$('#mes').val() - 1];


            document.getElementById('promSemetral').innerHTML = (data.semestral ?? "0") + "%";
            document.getElementById('iconSemestral').innerHTML = data.semestralAnt == null ? 'remove' : data.semestralAnt <
                data.semestral ? 'arrow_upward' :
                'arrow_downward';
            document.getElementById('iconSemestral').style.color = data.semestralAnt == null ? "#a09b9b" : data
                .semestralAnt < data.semestral ? '#04aa6d' : '#ff0000';


            document.getElementById('promAnual').innerHTML = (data.anual ?? "0") + "%";
            document.getElementById('iconAnual').innerHTML = data.anualAnt == null ? 'remove' : data.anualAnt < data.anual ?
                'arrow_upward' :
                'arrow_downward';
            document.getElementById('iconAnual').style.color = data.anualAnt == null ? "#a09b9b" : data.anualAnt < data
                .anual ? '#04aa6d' : '#ff0000';

            var bodyTable = document.getElementById('bodyTable');

            bodyTable.innerHTML = "";

            data.registros.forEach(element => {
                var tr = document.createElement('tr');
                var tdFecha = document.createElement('td');
                tdFecha.innerHTML = element.fechaGenerada.substr(0, 10);
                var tdPuntuacion = document.createElement('td');
                tdPuntuacion.innerHTML = `${element.puntajeFinal} / ${element.puntajeMaximo}`;
                var tdPromedio = document.createElement('td');
                tdPromedio.innerHTML = `${Math.round((element.puntajeFinal * 100) / element.puntajeMaximo)}%`;
                var tdAcciones = document.createElement('td');
                var link = `{{ route('visualizarChecklist', ['id' => ':id']) }}`;
                link = link.replace(':id', element.idCheckList);
                tdAcciones.innerHTML =
                    `<a href="${link}" target="_blank" class="btn btn-sm btn-link btn-just-icon btn-info"><i class="material-icons">visibility</i></a>`;
                tr.appendChild(tdFecha);
                tr.appendChild(tdPuntuacion);
                tr.appendChild(tdPromedio);
                tr.appendChild(tdAcciones);
                bodyTable.appendChild(tr);
            });
        }

        // set the dimensions and margins of the graph
        function linechart(json) {
            var divChart = document.getElementById('chart');
            divChart.innerHTML = '<div class="card-header h3 ">Desmpeño mensual</div>';

            const margin = {
                    top: 10,
                    right: 20,
                    bottom: 30,
                    left: 40
                },
                width = divChart.clientWidth - 30,
                height = 300 - margin.top - margin.bottom;

            // append the svg object to the body of the page
            const svg = d3.select("#chart")
                .append("svg")
                .attr("width", width)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("transform", `translate(${margin.left},${margin.top})`);

            var meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
            var mes = meses[$('#mes').val() - 1]

            var datos = d3.keys(json.meses).map(
                function(d) {
                    return {
                        date: meses[json.meses[d].mes],
                        value: json.meses[d].avg
                    }
                })

            chart(datos, json.puntajeMaxMin.cal_m, json.puntajeMaxMin.cal_b, mes);
            // Now I can use this dataset:
            function chart(data, cal_m, cal_b, mes) {
                // Add X axis --> it is a date format
                const x = d3.scaleBand()
                    .domain(data.map(function(x) {
                        return x.date;
                    }))
                    .paddingInner(1)
                    .paddingOuter(0.1)
                    .align(0.5)
                    .range([0, width - 60]);
                svg.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x));
                // const x = d3.scaleLinear()
                //     .domain(d3.extent(data, d => d.date))
                //     .range([0, width]);
                svg.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x));
                // Add Y axis
                const y = d3.scaleLinear()
                    .domain([0, 100])
                    .range([height, 0]);
                svg.append("g")
                    .call(d3.axisLeft(y));

                const Tooltip = d3.select("#chart")
                    .append("div")
                    .style("opacity", 0)
                    .attr("class", "tooltip")
                    .style("background-color", "white")
                    // .style("border", "solid")
                    .style("border-width", "2px")
                    .style("border-radius", "5px")
                    .style("padding", "5px")

                // Three function that change the tooltip when user hover / move / leave a cell
                const mouseover = function(event, d) {
                    Tooltip
                        .style("opacity", 1)
                }
                const mousemove = function(event, d) {
                    Tooltip
                        .html("Exact value: " + d.value)
                        .style("left", `${event.layerX+10}px`)
                        .style("top", `${event.layerY}px`)
                }
                const mouseleave = function(event, d) {
                    Tooltip
                        .style("opacity", 0)
                }

                // Add the line
                svg.append("path")
                    .datum(data)
                    .attr("fill", "none")
                    .attr("stroke", "#ffc300")
                    .attr("stroke-width", 2)
                    .attr("d", d3.line()
                        .x(d => x(d.date))
                        .y(d => y(d.value))
                    )

                svg.append("path")
                    .datum(data)
                    .attr("fill", "none")
                    .attr("stroke", "#FF0000")
                    .attr("stroke-width", 2)
                    .attr("d", d3.line()
                        .x(d => x(d.date))
                        .y(d => y(cal_m))
                    )

                svg.append("path")
                    .datum(data)
                    .attr("fill", "none")
                    .attr("stroke", "#04AA6D")
                    .attr("stroke-width", 2)
                    .attr("d", d3.line()
                        .x(d => x(d.date))
                        .y(d => y(cal_b))
                    )
                // Add the points
                svg.append("g")
                    .selectAll("dot")
                    .data(data)
                    .join("circle")
                    .attr("cx", d => x(d.date))
                    .attr("cy", d => y(d.value))
                    .attr("r", 8)
                    .attr("fill", d => d.date == mes ? "#0e6170" :
                        "#ffc300")
                    .on("mouseover", mouseover)
                    .on("mousemove", mousemove)
                    .on("mouseleave", mouseleave);
            }
        }

        function validParams() {
            if ($("#sucursal").val() != "" && $("#evaluacion").val() != null && $("#sucursal")
                .val() != null && $('#mes').val() != "" && $('#anio').val() != "" && $("#mes").val() != null && $("#anio")
                .val() != null)
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
                'Years': [moment(), moment()],
            }
        });
    </script>
@endsection
