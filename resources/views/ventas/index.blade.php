@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'VentaMensual'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <label class="col-1 col-form-label">Fecha Jaja</label>
                        <div class="col-2">
                            <div class="form-group">
                                <div class='input-group date'>
                                    <input type="text" id="dtpIni" class="form-control datepicker" value="">
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- <label class="col-1 col-form-label">Compañia</label>
                        <div class="col-3">
                            <select id="compania" name="compania" class="selectpicker"
                                data-style="btn select-with-transition" title="Seleccione una compania" data-size="7"
                                tabindex="-98">
                                <option value="0">Global</option>
                                <option value="1">Maison Kayser Mexico</option>
                                <option value="2">Carmela y Sal</option>
                                <option value="3">Tzuco</option>
                                <option value="4">Maison Kayser España</option>
                            </select>
                        </div> --}}
                        <label for="" class="col-1 col-form-label">La Compañia</label>
                        <div class="col-3">
                            <select class="select2-item" id="compania" name="compania[]" data-size="7" style="width:100%;"
                                title="Location" multiple>
                                {{-- <option value="0" data-clas="SC" disabled selected>Select a location</option> --}}
                                <option value="All" data-type="1">Global</option>
                                @if (!empty($hierachy))
                                    @foreach ($hierachy as $location)
                                        <option value="{{ $location['id'] }}" data-type="{{ $location['tipo'] }}"
                                            data-clas="{{ $location['clas'] }}"
                                            @if ($location['id'] == 0) disabled @endif>
                                            {{ $location['nombre'] }}</option>
                                    @endforeach
                                @else
                                    <option value="tzuco">Tzuco</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-3">
                            <div class="btn-group">
                                <button id="findPListbtn" onclick="reload(event)" class="btn btn-white btn-just-icon">
                                    <i class="material-icons">search</i>
                                    <div class="ripple-container"></div>
                                </button>
                                <button id="xlsPListbtn" onclick="xlsExport(event)" class="btn btn-white btn-just-icon">
                                    <i class="material-icons">table_view</i>
                                    <div class="ripple-container"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <style>
            table {
                margin: 0 auto;
                width: 100%;
                clear: both;
                border-collapse: collapse;
                table-layout: fixed; // ***********add this
                word-wrap: break-word; // ***********and this
            }

            th,
            td {
                white-space: nowrap;
            }
        </style>
        <div id="tablediv" class="col-12" style="overflow: hidden !important;">
            <table id="datatables" class="cell-border compact stripe table-bordered table-sm table-striped table-hover"
                cellspacing="0" width="100%" style="width:100%;">
                <thead>
                    <tr class="thead-dark" style="font-size: 11px!important;" id="tableHeaders">
                        <th>Fecha</th>
                        @foreach ($sucursales as $suc)
                            <th>{{ $suc->nombre }}</th>
                        @endforeach
                        <th>Diario</th>
                        <th>Semanal</th>
                    </tr>
                </thead>
                <tbody style="font-size: 12px!important;">
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('jsimports')
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/moment.min.js') }}"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/bootstrap-selectpicker.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endsection
@section('aditionalScripts')
    <script type="text/javascript">
        $('#compania').on('change', function() {
            var options = $('#compania option');
            if ($('#compania :selected')[0]) {
                var clas = $('#compania :selected')[0].dataset.clas;
                options.map((element) => {
                    if (clas != options[element].dataset.clas) {
                        $(`#compania option:eq(${element})`).attr('disabled', true)
                    } else {
                        $(`#compania option:eq(${element})`).attr('disabled', false)
                    }
                })
            } else {
                options.map((element) => {
                    if (options[element].dataset.clas != 'SC') {
                        $(`#compania option:eq(${element})`).attr('disabled', false)
                    }
                })
            }

            $('#compania').select2({
                templateResult: formatState
            })
        })

        function getSucursales() {
            var params = {
                company: $("#compania").val(),
                type: $("#compania :selected").data('clas'),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                type: "POST",
                url: "{{ route('getSucursalesVen') }}",
                data: params,
                success: function(data) {
                    sucursalesHTML = ""
                    sucursales = [];
                    data.sucursales.forEach(element => {
                        sucursalesHTML += `<th>${element.nombre}</th>`
                        sucursales.push(element.idSucursal)
                    });

                    genDataTable();
                },
                error: function() {}

            });
        }

        function genHTMLTable() {
            $("#tablediv").empty();
            html =
                "<table id='datatables' class='cell-border compact stripe table-bordered table-sm table-striped table-hover' cellspacing='0' width='100%' style='width:100%;'><thead><tr class='thead-dark' style='font-size: 11px!important;'><th>Fecha</th>";
            html += sucursalesHTML;
            html +=
                "<th>Diario</th><th>Semanal</th></tr></thead><tbody style='font-size: 12px!important;'></tbody></table>";
            $("#tablediv").append(html);
        }

        function genDataTable() {

            let table = $('#datatables').DataTable();
            let columns = [];
            var lastItem = 0;
            columns[0] = {
                "data": "fecha"
            };
            sucursales.forEach(function(value, index, array) {
                columns[index + 1] = {
                    "data": value,
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                };
                lastItem = index + 1;
            });
            columns[lastItem + 1] = {
                "data": "diario",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            }
            columns[lastItem + 2] = {
                "data": "semana",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            };

            table.destroy();
            genHTMLTable();
            $('#datatables').DataTable({
                "processing": true,
                "paging": false,
                "dom": 'rt',
                "ajax": {
                    "url": "{{ route('getDetVenta') }}",
                    "type": "GET",
                    "data": function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.fechaIni = $('#dtpIni').val();
                        d.compania = $('#compania').val();
                        d.typeLoc = $("#compania :selected").data('clas');
                    }
                },
                scrollY: false,
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: {
                    leftColumns: 1,
                    rightColumns: 2
                },
                "columns": columns,
                "columnDefs": [{
                        "targets": "_all",
                        "width": 90
                    },
                    {
                        "targets": "_all",
                        "className": "dt-right"
                    }
                ],
                fixedColumns: true
            });
        }

        function reload(event) {
            event.preventDefault();
            getSucursales();
        }

        function xlsExport(event) {
            var form = document.createElement("form");
            var element1 = document.createElement("input");
            var element2 = document.createElement("input");
            var element3 = document.createElement("input");
            var element4 = document.createElement("input");
            var element5 = document.createElement("input");
            var element6 = document.createElement("input");

            form.method = "POST";
            form.id = "getDetVentaXls";
            form.action = "{{ route('getDetVentaXls') }}";
            form.target = "_blank";

            element1.value = $("#dtpIni").val();
            element1.name = "fechaIni";
            element1.type = "hidden";
            form.appendChild(element1);

            element2.value = "{{ csrf_token() }}";
            element2.name = "_token";
            element2.type = "hidden";
            form.appendChild(element2);

            document.body.appendChild(form);

            form.submit();
        }

        var sucursalesHTML =
            "@foreach ($sucursales as $suc)<th>{{ $suc->nombre }}</th>@endforeach";
        var sucursales = [];

        @foreach ($sucursales as $i => $suc)
            sucursales[{{ $i }}] = "{{ $suc->idSucursal }}";
        @endforeach

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
            $('#dtpIni').datetimepicker({
                viewMode: 'months',
                format: 'YYYY-MM',
            });
            genDataTable();
            $('.select2-item').select2({
                templateResult: formatState
            });

        });
    </script>
@endsection
