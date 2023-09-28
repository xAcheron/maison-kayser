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
                    <h4 class="card-title">Analisis de precios
                        <small> - Filtros</small>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            Fechas:<br>
                            <input type="text" class="filter-components" style="width:100%;" name="daterange"
                                id="daterange" value="{{ date('Y-m-d') }} - {{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-2">
                            Company:<br>
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
                            Analisis por:<br>
                            <select name="analisis" id="analisis" class="select2-item" style="width: 100%">
                                <option value="" disabled selected>Selecciona una opción</option>
                                <option value="1">Proveedor</option>
                                <option value="2">Producto</option>
                            </select>
                        </div>
                        <div class="col-2" style="display: none" id="targetDiv">
                            <span id="labelSeleccion"></span><br>
                            <select name="target" id="target" class="" style="width: 100%"></select>
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
        <div class="col-12" id="containerTableProv" style="display: none">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Precio por <span id="analisisTitle"></span></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow-x:auto !important;">
                                <table class="table table-condensed table-striped">
                                    <thead id="baseHeadProv">
                                        <tr>
                                            <th># Articulo</th>
                                            <th>Año</th>
                                            <th>Mes</th>
                                            <th>Nombre</th>
                                            <th>Proveedor</th>
                                            <th>Mensual</th>
                                            <th>Anual</th>
                                            <th>Maximo</th>
                                            <th>Minimo</th>
                                            <th>% Variación</th>
                                        </tr>
                                    </thead>
                                    <tbody id="baseTableProv">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        </style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.29.2/sweetalert2.all.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        </link>

        <script type="text/javascript">
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
            $(function() {
                $('input[name="daterange"]').daterangepicker({
                    opens: 'right',
                    minYear: 2019,
                    maxYear: {{ date('Y') }},
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                    ranges: {
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    }
                });

                $('.select2-item').select2({
                    templateResult: formatState
                });

                $("#exportReport").on("click", function(e) {
                    if (validParams()) {} else {
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

                        $('#containerTableProv').show()
                        if ($('#analisis').val() == 1) {
                            $('#analisisTitle').html('proveedor')
                        } else if ($('#analisis').val() == 2) {
                            $('#analisisTitle').html('producto')
                        }

                        var params = {
                            daterange: $("#daterange").val(),
                            location: $("#location").val(),
                            target: $('#target').val(),
                            analisis: $('#analisis').val(),
                            _token: "{{ csrf_token() }}"
                        };

                        $.ajax({
                            type: "POST",
                            url: "{{ route('getReport', ['id' => 25, 'format' => 'json']) }}",
                            data: params,
                            success: function(msg) {
                                if (msg.success == true) {
                                    if (msg.data != undefined && msg.data != null) {
                                        makeTable(msg.data, 'baseTableProv');
                                    } else {
                                        clearTable('baseTableProv');
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

            $('#analisis').on('change', function() {
                if ($('#location').val()) {

                    $("#target").val('');
                    $("#target").trigger('change.select2');

                    $('#containerTableProv').hide()

                    if ($(this).val() == 1) {
                        var params = {
                            company: $('#location').val(),
                            analisis: $(this).val(),
                            _token: "{{ csrf_token() }}"
                        };
                        url = "{{ route('getProveedoresReports') }}";
                        $('#labelSeleccion').html('Proveedor:')
                        $('#target').select2({
                            templateResult: formatState
                        });
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: params,
                            success: function(msg) {
                                if (msg.success == true) {
                                    if (msg.data != undefined && msg.data != null) {
                                        generateOption({
                                            id: '',
                                            nombre: 'Seleccione una opción',
                                        }, 'target')
                                        msg.data.forEach(item => {
                                            generateOption(item, 'target');
                                        });
                                        $('#targetDiv').show();
                                    } else {
                                        $('#targetDiv').hide();
                                        $('#target').val('')
                                        $('#target').html('')
                                    }
                                }
                            },
                            error: function() {

                            }

                        });
                    } else if ($(this).val() == 2) {
                        $('#labelSeleccion').html('Articulo:')
                        $('#targetDiv').show();
                        $('#target').select2({
                            ajax: {
                                url: "{{ route('getArituclosReports') }}",
                                dataType: 'json',
                                type: "POST",
                                delay: 250,
                                data: function(params) {
                                    return {
                                        query: params.term,
                                        page: params.page,
                                        _token: "{{ csrf_token() }}",
                                        analisis: $('#analisis').val(),
                                        company: $('#location').val(),
                                    };
                                },
                                processResults: function(data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: data.data,
                                        pagination: {
                                            more: (params.page * 30) < data.total_count
                                        }
                                    };
                                },
                                cache: true
                            },
                            placeholder: 'Buscar...',
                            minimumInputLength: 1,
                            // escapeMarkup: function(markup) {
                            //     return markup;
                            // },
                            templateResult: formatResult,
                            templateSelection: formatDataSelection
                        });
                    }

                } else {
                    $(this).val('')
                    swal('Advertencia', 'Elija una compañia antes', 'warning')
                }
            })

            function generateRow(value, isNumber, isPorcentaje) {
                var formatter = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
                    maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
                });
                var formatter2 = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });
                var clases;


                if (isNumber == true) {
                    if (typeof(value) == 'number') {
                        value = formatter.format(value);
                    }
                    clases = 'text-right';
                } else {
                    clases = 'text-left';
                }

                if (isPorcentaje && value > 100) {
                    value += ' <i class="material-icons text-danger">warning<i/>'
                }

                const td = document.createElement('td');
                td.innerHTML = value;
                td.classList = clases;

                console.log(td)

                return td;
            }

            function generateOption(data, idSelectTarget) {
                const target = $(`#${idSelectTarget}`)
                const option = document.createElement('option');
                option.value = data.id;
                option.innerHTML = data.nombre;

                target.append(option);
            }

            function loadingData() {
                Swal.showLoading();
            }

            function endLoadingData() {
                Swal.close();
            }

            function makeTable(data, idTarget) {
                clearTable(idTarget);

                const table = $(`#${idTarget}`)

                data.forEach(item => {
                    const tr = document.createElement('tr');
                    var porcentaje = '--'
                    if (item.PrecioMensual != 0) {
                        porcentaje = item.porcentaje;
                    }

                    tr.appendChild(generateRow(item.idArticulo));
                    tr.appendChild(generateRow(item.anio));
                    tr.appendChild(generateRow(item.mes));
                    tr.appendChild(generateRow(item.articulo));
                    tr.appendChild(generateRow(item.proveedor));
                    tr.appendChild(generateRow(item.PrecioMensual, true));
                    tr.appendChild(generateRow(item.PrecioAnual, true));
                    tr.appendChild(generateRow(item.PrecioMaximo, true));
                    tr.appendChild(generateRow(item.PrecioMinimo, true));
                    tr.appendChild(generateRow(porcentaje, true, true));

                    table.append(tr);
                });

                endLoadingData();
            }

            function clearTable(id) {
                var tabla = document.getElementById(id);
                console.log(tabla)
                tabla.innerHTML = "";
            }

            function validParams() {
                if ($("#daterange").val() != "" && $("#location").val() != "" && $("#daterange").val() != null && $("#location")
                    .val() != null)
                    return true;
                return false;
            }

            function formatResult(data) {
                console.log(data)
                if (data.loading) return data.text;
                var markup = $("<div class='select2-result-data clearfix'>" +
                    "<div class='select2-result-data__meta'>" +
                    "<div class='select2-result-data__title'>" + data.name + "</div></div></div>"
                );
                return markup;
            }

            function formatDataSelection(data) {
                return data.name || data.id;
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
