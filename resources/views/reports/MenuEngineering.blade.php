@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'MenuEngineering'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-info">
                    <div class="card-icon">
                        <i class="material-icons">timeline</i>
                    </div>
                    <h4 class="card-title">Menu Engineering
                        <small> - Filters</small>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
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
                        <div class="col-2 align-items-center">
                            <button id="runReport" class="btn btn-info">Run Report</button>
                        </div>
                        <div class="col-2 align-items-center">
                            <button id="exportReport" class="btn">
                                <span class="btn-label">
                                    <i class="material-icons">table_view</i>
                                </span>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Report Area</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-condensed table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Major grp</th>
                                        <th>Gross Sales</th>
                                        <th>Net Sales</th>
                                        <th>Food Cost</th>
                                        <th>Quantity</th>
                                        <th>Sales %</th>
                                    </tr>
                                    </head>
                                <tbody id="baseTable">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h4 class="card-title">Sales Distribution</h4>
                </div>
                <div class="card-body">
                    <div id="chartPreferences" class="ct-chart"></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="card-category">Legend</h6>
                        </div>
                        <div class="col-md-12" id="legendTable">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="formsarea"></div>

    <div class="modal fade" id="grupo" tabindex="-1" aria-labelledby="Editar" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <table class="table table-condensed ">
                        <thead>
                            <tr>
                                <td>Nombre</td>
                                <td>Major Grp</td>
                                <td>Family Grp</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <input type="hidden" name="idMicros" id="idMicros">
                                <td>
                                    <div id="menuitem"></div>
                                </td>
                                <td>
                                    <select class="form-control" id="major">

                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" id="family">

                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="updateApp" data-dismiss="modal">Guardar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('aditionalScripts')
    <style>
        .ct-chart .ct-series-d .ct-slice-pie {
            fill: #4caf50 !important;
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

        .text-rose {
            color: #E6B0AA !important;
        }

        .text-grey {
            color: #E67E22 !important;
        }

        .text-blue {
            color: #AED6F1 !important;
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
                    let url = "{{ route('getReport', ['id' => 1, 'format' => 'xlsx']) }}";
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
                        url: "{{ route('getReport', ['id' => 1, 'format' => 'json']) }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success == true) {
                                /*if(msg.data.length > 0)
                                {*/
                                if (msg.data.report.length > 0) {
                                    console.log(msg.data.majors);
                                    makeTable(msg.data.report);
                                    //TODO PLEASE END THE MAKE LEGEND TABLE FUNCTION
                                    makeLegendTable(msg.data.majors);
                                } else {
                                    clearTable("baseTable");
                                }
                                /*}*/
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

        function openDetailReport(e, idMajor, percentage) {
            const el = document.querySelector("#MjGrp" + idMajor);
            if (el.dataset.open == "0") {

                el.dataset.open = 1;

                loadingData();

                var params = {
                    daterange: $("#daterange").val(),
                    major: idMajor,
                    location: $("#location").val(),
                    perSales: percentage,
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('getReport', ['id' => 4, 'format' => 'json']) }}",
                    data: params,
                    success: function(msg) {
                        console.log(msg)
                        if (msg.success == true) {
                            if (msg.data.length > 0) {
                                //makeTable(msg.data);

                                makeSubTable(idMajor, msg.data);
                            } else {
                                clearTable("baseTable");
                            }
                        }
                    },
                    error: function() {

                    }

                });
            } else {
                const nodes = document.querySelectorAll("#Item_" + idMajor);
                Array.prototype.forEach.call(nodes, function(node) {
                    node.parentNode.removeChild(node);
                });
                el.dataset.open = 0;
            }
        }

        function setParamsModal(idMicros, menuItem) {
            var params = {
                location: $('#location').val(),
                _token: "{{ csrf_token() }}"
            };

            var selectMajors = document.getElementById('major');
            var selectFamilies = document.getElementById('family');
            var menuItemElement = document.getElementById('menuitem');
            selectFamilies.innerHTML = ""
            selectMajors.innerHTML = "<option disabled selected>Selecciona un Major</option>"


            menuItemElement.innerHTML = menuItem;
            $("#idMicros").val(idMicros);

            $.ajax({
                type: "POST",
                url: "{{ route('getMajorAndFamily') }}",
                data: params,
                success: function(msg) {
                    if (msg.success == true) {
                        msg.majors.forEach(opcion => {
                            selectMajors.appendChild(option(opcion));
                        });

                    }
                },
                error: function() {

                }

            });
        }

        $('#major ').on("change", function() {
            var selectFamilies = document.getElementById('family');

            var params = {
                location: $('#location').val(),
                idMajor: $('#major').val(),
                _token: "{{ csrf_token() }}"
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getFamiliesForm') }}",
                data: params,
                success: function(msg) {
                    if (msg.success == true) {
                        selectFamilies.innerHTML = ""
                        msg.families.forEach(opcion => {
                            selectFamilies.appendChild(option(opcion));
                        });
                    }
                },
                error: function() {

                }

            });
        });

        function option(data) {
            var option = document.createElement("option");
            option.setAttribute("value", data.id);
            option.setAttribute("data-type", "");
            option.innerHTML = data.name;

            return option;
        }

        $("#updateApp").on("click", function(e) {
            loadingData();
            idMicros = $("#idMicros").val();
            params = {
                idMicros: $("#idMicros").val(),
                idMajor: $("#major").val(),
                idFamily: $("#family").val(),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                type: "POST",
                url: "{{ route('saveClasiMicrosProd') }}",
                data: params,
                success: function(msg) {
                    if (msg.success == true) {
                        document.getElementById(`button-${idMicros}`).remove();
                        Swal.fire({
                            icon: 'success',
                            title: msg.msg,
                            showConfirmButton: true,
                            text: 'Para visualizar el producto en su categoria, volver a generar el reporte'
                        })
                    } else if (msg.success == false) {
                        Swal.fire({
                            icon: 'error',
                            title: msg.msg,
                            showConfirmButton: true,
                        })
                    }
                },
                error: function(e) {
                    console.log(e.responseText)
                }

            });
        })


        function makeSubTable(major, data) {
            let htmlTemplate =
                "<td class=\"text-left\"><i class=\"material-icons\">:icon</i></td><td class=\"text-left\">:menuitem</td><td class=\"text-right\">:netSales</td><td class=\"text-right\">:grossSales</td><td class=\"text-right\">:cost</td><td class=\"text-right\">:quantity</td><td class=\"text-right\">:percent</td>";

            let domTarget = "#MjGrp" + major;
            let tr = document.createElement('tr');
            let target = document.querySelector(domTarget);
            let innerHTML = "";
            var formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });


            for (var i = data.length - 1; i >= 0; i--) {
                tr = document.createElement('tr');
                tr.id = "Item_" + major;
                innerHTML = htmlTemplate;
                if (major == null && data[i].idItemMicros != "Total") {
                    data[i].idItemMicros = data[i].idItemMicros.replace('\'', '');
                    let buttonTemplate =
                        `<a class=" btn-just-icon" id="crearApp" data-toggle="modal" onClick="setParamsModal('${data[i].idMicros}', '${data[i].idItemMicros}')" data-target="#grupo"><i class=\"material-icons\" id="button-${data[i].idMicros}"">add_circle</i></a>`;

                    innerHTML = innerHTML.replace(':icon', buttonTemplate);
                } else {

                    innerHTML = innerHTML.replace(':icon', "");
                }
                innerHTML = innerHTML.replace(':menuitem', data[i].idItemMicros);
                innerHTML = innerHTML.replace(':netSales', formatter.format(data[i].ventaNeta));
                innerHTML = innerHTML.replace(':grossSales', formatter.format(data[i].ventaBruta));
                innerHTML = innerHTML.replace(':quantity', formatter.format(data[i].cantidad));
                innerHTML = innerHTML.replace(':percent', formatter.format(data[i].salesPercent));
                innerHTML = innerHTML.replace(':cost', formatter.format(data[i].costo));
                tr.innerHTML = innerHTML;
                target.parentNode.insertBefore(tr, target.nextSibling);
            }

            endLoadingData();

        }

        function makeLegendTable(majors) {
            clearTable("legendTable");
            majors = Object.values(majors);
            htmlTemplate =
                "<i class=\"fa fa-circle text-:class\"></i> :major";
            let target = document.querySelector('#legendTable');


            for (let index = 0; index < majors.length; index++) {
                const element = majors[index];
                switch (index) {
                    case 0:
                        clase = "info";
                        break;
                    case 1:
                        clase = "danger";
                        break;
                    case 2:
                        clase = "warning";
                        break;
                    case 3:
                        clase = "success";
                        break;
                    case 4:
                        clase = "primary";
                        break;
                    case 5:
                        clase = "grey";
                        break;
                    case 6:
                        clase = "rose";
                        break;
                    case 7:
                        clase = "blue";
                        break;

                    default:
                        clase = "";
                        break;
                }

                innerHTML = htmlTemplate;
                innerHTML = innerHTML.replace(':major', element ?? "Sin grupo");
                innerHTML = innerHTML.replace(':class', clase);
                div = document.createElement('div');

                target.innerHTML += innerHTML;

            }

        }

        function makeTable(data) {
            clearTable("baseTable");
            let htmlTemplate =
                "<td class=\"text-left\" onClick=\"openDetailReport(event, :idmajor, :percent)\"><span class=\"material-icons\">chevron_right</span></td><td class=\"text-left\">:major</td><td class=\"text-right\">:netSales</td><td class=\"text-right\">:grossSales</td><td class=\"text-right\">:cost</td><td class=\"text-right\">--</td><td class=\"text-right\">:percent</td>";
            let domTarget = '#baseTable';
            let tr = document.createElement('tr');
            let target = document.querySelector(domTarget);
            let innerHTML = "";
            var formatter = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
                maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
            });

            let chartValues = [];
            let chartLabels = [];

            for (var i = 0; i < data.length; i++) {
                tr = document.createElement('tr');
                innerHTML = htmlTemplate;
                tr.id = "MjGrp" + data[i].idMajor;
                tr.dataset.open = 0;

                if (data[i].major == "Total") {
                    innerHTML = innerHTML.replace(':major', "<b>" + data[i].major + "</b>");
                    innerHTML = innerHTML.replace(':netSales', "<b>" + formatter.format(data[i].ventaNeta) + "</b>");
                    innerHTML = innerHTML.replace(':grossSales', "<b>" + formatter.format(data[i].ventaBruta) + "</b>");
                    innerHTML = innerHTML.replace(':idmajor', "");
                    innerHTML = innerHTML.replace(':percent', formatter.format(data[i].salesPercent * 100));
                    innerHTML = innerHTML.replace(':percent', formatter.format(data[i].salesPercent * 100));
                    innerHTML = innerHTML.replace(':cost', formatter.format(data[i].costo));
                } else {
                    innerHTML = innerHTML.replace(':major', data[i].major ?? "Sin grupo");
                    innerHTML = innerHTML.replace(':netSales', formatter.format(data[i].ventaNeta));
                    innerHTML = innerHTML.replace(':grossSales', formatter.format(data[i].ventaBruta));
                    innerHTML = innerHTML.replace(':idmajor', data[i].idMajor);
                    innerHTML = innerHTML.replace(':percent', formatter.format(data[i].salesPercent * 100));
                    innerHTML = innerHTML.replace(':percent', formatter.format(data[i].salesPercent * 100));
                    innerHTML = innerHTML.replace(':cost', formatter.format(data[i].costo));
                    if (data[i].salesPercent > 0) {
                        chartValues.push(formatter.format(data[i].salesPercent));
                        chartLabels.push(formatter.format(data[i].salesPercent) + "%");
                    }
                }

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

        function clearTable(table) {
            var tabla = document.getElementById(table);
            tabla.innerHTML = "";
            //table.textContent = "";
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
    </style>
@endsection
