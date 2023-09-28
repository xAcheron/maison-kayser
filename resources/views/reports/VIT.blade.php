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
                    <h4 class="card-title">VIT
                        <small> - Filters</small>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
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
                            Tier:<br>
                            <select class="select2-item" id="tier" data-size="7" style="width:100%;" title="Tier">
                                <option value="0" disabled selected>Select a Tier</option>
                            </select>
                        </div>
                        <div class="col-1">
                            Desglosar:<br>
                            <input type="checkbox" id="desglosar" name="desglosar">
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Guests</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 30vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>Day Part</th>
                                            <th>Tienda</th>
                                            <th>Lunes</th>
                                            <th>%LW</th>
                                            <th>Martes</th>
                                            <th>%LW</th>
                                            <th>Miercoles</th>
                                            <th>%LW</th>
                                            <th>Jueves</th>
                                            <th>%LW</th>
                                            <th>Viernes</th>
                                            <th>%LW</th>
                                            <th>Sábado</th>
                                            <th>%LW</th>
                                            <th>Domingo</th>
                                            <th>%LW</th>
                                            <th>% DIF LW</th>
                                        </tr>
                                        </head>
                                    <tbody id="guestTableRvc">

                                    </tbody>
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
                    <h4 class="card-title">Sales</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 30vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>Day Part</th>
                                            <th>Tienda</th>
                                            <th>Lunes</th>
                                            <th>%LW</th>
                                            <th>Martes</th>
                                            <th>%LW</th>
                                            <th>Miercoles</th>
                                            <th>%LW</th>
                                            <th>Jueves</th>
                                            <th>%LW</th>
                                            <th>Viernes</th>
                                            <th>%LW</th>
                                            <th>Sábado</th>
                                            <th>%LW</th>
                                            <th>Domingo</th>
                                            <th>%LW</th>
                                            <th>% DIF LW</th>
                                        </tr>
                                        </head>
                                    <tbody id="salesTableRvc">

                                    </tbody>
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
                    <h4 class="card-title">Avg Check</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 30vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>Day Part</th>
                                            <th>Tienda</th>
                                            <th>Lunes</th>
                                            <th>%LW</th>
                                            <th>Martes</th>
                                            <th>%LW</th>
                                            <th>Miercoles</th>
                                            <th>%LW</th>
                                            <th>Jueves</th>
                                            <th>%LW</th>
                                            <th>Viernes</th>
                                            <th>%LW</th>
                                            <th>Sábado</th>
                                            <th>%LW</th>
                                            <th>Domingo</th>
                                            <th>%LW</th>
                                            <th>% DIF LW</th>
                                        </tr>
                                        </head>
                                    <tbody id="checkTableRvc">

                                    </tbody>
                                </table>
                            </div>
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
        $("#location").on("change", function() {
            var location = $("#location").val();
            var select = document.querySelector("#tier");

            params = {
                idEmpresa: location,
                _token: "{{ csrf_token() }}"
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getTiers') }}",
                data: params,
                success: function(msg) {
                    if (msg.success == true) {
                        if (msg.data.length > 0) {
                            select.innerHTML = "";
                            var tr = document.createElement('option');
                            tr.value = 0;
                            tr.innerHTML = "All Tiers";
                            tr.setAttribute("data-type", "")
                            select.appendChild(tr);
                            msg.data.forEach(element => {
                                var tr = document.createElement('option');
                                tr.value = element.idTier;
                                tr.innerHTML = element.tier;
                                tr.setAttribute("data-type", "")
                                select.appendChild(tr);
                            });
                        } else {

                        }
                    }
                },
                error: function() {

                }

            });
        });

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
                    let url = "{{ route('getReport', ['id' => 9, 'format' => 'xlsx']) }}";
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

                    var desglosar = document.createElement('input');
                    desglosar.setAttribute('type', 'hidden');
                    desglosar.setAttribute('name', 'desglosar');
                    desglosar.setAttribute('value', $("#desglosar").is(':checked'));
                    myForm.appendChild(desglosar);

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
                        tier: $("#tier").val(),
                        desglosar: $("#desglosar").is(':checked'),
                        _token: "{{ csrf_token() }}"
                    };

                    $.ajax({
                        type: "POST",
                        url: "{{ route('getReport', ['id' => 9, 'format' => 'json']) }}",
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

            let htmlTemplate =
                "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:gbl</td><td class=\"text-right\">:gblLW</td><td class=\"text-right\">:gbm</td><td class=\"text-right\">:gbmLW</td><td class=\"text-right\">:gbmr</td><td class=\"text-right\">:gbmrLW</td><td class=\"text-right\">:gbj</td><td class=\"text-right\">:gbjLW</td><td class=\"text-right\">:gbv</td><td class=\"text-right\">:gbvLW</td><td class=\"text-right\">:gbs</td><td class=\"text-right\">:gbsLW</td><td class=\"text-right\">:gbd</td><td class=\"text-right\">:gbdLW</td><td class=\"text-right\">:gbper</td>";
            let domTarget = '#guestTableRvc';
            let tr = document.createElement('tr');
            let target = document.querySelector(domTarget);
            let innerHTML = "";
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
            if (data.dps.length > 0) {

                console.log(data.dps.length);

                for (i = 0; data.dps.length > i; i++) {
                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:gbl</td><td class=\"text-right\">:gblLW</td><td class=\"text-right\">:gbm</td><td class=\"text-right\">:gbmLW</td><td class=\"text-right\">:gbmr</td><td class=\"text-right\">:gbmrLW</td><td class=\"text-right\">:gbj</td><td class=\"text-right\">:gbjLW</td><td class=\"text-right\">:gbv</td><td class=\"text-right\">:gbvLW</td><td class=\"text-right\">:gbs</td><td class=\"text-right\">:gbsLW</td><td class=\"text-right\">:gbd</td><td class=\"text-right\">:gbdLW</td><td class=\"text-right\">:gbper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Breakfast</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");

                    innerHTML = innerHTML.replace(':gbl', formatter2.format(data.dps[i].gbl));
                    innerHTML = innerHTML.replace(':gbm', formatter2.format(data.dps[i].gbm));
                    innerHTML = innerHTML.replace(':gbmr', formatter2.format(data.dps[i].gbmr));
                    innerHTML = innerHTML.replace(':gbj', formatter2.format(data.dps[i].gbj));
                    innerHTML = innerHTML.replace(':gbv', formatter2.format(data.dps[i].gbv));
                    innerHTML = innerHTML.replace(':gbs', formatter2.format(data.dps[i].gbs));
                    innerHTML = innerHTML.replace(':gbd', formatter2.format(data.dps[i].gbd));
                    innerHTML = innerHTML.replace(':gblLW', formatter.format(data.dpslw[i].gbl));
                    innerHTML = innerHTML.replace(':gbmLW', formatter.format(data.dpslw[i].gbm));
                    innerHTML = innerHTML.replace(':gbmrLW', formatter.format(data.dpslw[i].gbmr));
                    innerHTML = innerHTML.replace(':gbjLW', formatter.format(data.dpslw[i].gbj));
                    innerHTML = innerHTML.replace(':gbvLW', formatter.format(data.dpslw[i].gbv));
                    innerHTML = innerHTML.replace(':gbsLW', formatter.format(data.dpslw[i].gbs));
                    innerHTML = innerHTML.replace(':gbdLW', formatter.format(data.dpslw[i].gbd));
                    innerHTML = innerHTML.replace(':gbper', formatter.format(data.dpslw[i].gb));

                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:gll</td><td class=\"text-right\">:gllLW</td><td class=\"text-right\">:glm</td><td class=\"text-right\">:glmLW</td><td class=\"text-right\">:glmr</td><td class=\"text-right\">:glmrLW</td><td class=\"text-right\">:glj</td><td class=\"text-right\">:gljLW</td><td class=\"text-right\">:glv</td><td class=\"text-right\">:glvLW</td><td class=\"text-right\">:gls</td><td class=\"text-right\">:glsLW</td><td class=\"text-right\">:gld</td><td class=\"text-right\">:gldLW</td><td class=\"text-right\">:glper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Lunch</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':gll', formatter2.format(data.dps[i].gll));
                    innerHTML = innerHTML.replace(':glm', formatter2.format(data.dps[i].glm));
                    innerHTML = innerHTML.replace(':glmr', formatter2.format(data.dps[i].glmr));
                    innerHTML = innerHTML.replace(':glj', formatter2.format(data.dps[i].glj));
                    innerHTML = innerHTML.replace(':glv', formatter2.format(data.dps[i].glv));
                    innerHTML = innerHTML.replace(':gls', formatter2.format(data.dps[i].gls));
                    innerHTML = innerHTML.replace(':gld', formatter2.format(data.dps[i].gld));
                    innerHTML = innerHTML.replace(':gllLW', formatter.format(data.dpslw[i].gll));
                    innerHTML = innerHTML.replace(':glmLW', formatter.format(data.dpslw[i].glm));
                    innerHTML = innerHTML.replace(':glmrLW', formatter.format(data.dpslw[i].glmr));
                    innerHTML = innerHTML.replace(':gljLW', formatter.format(data.dpslw[i].glj));
                    innerHTML = innerHTML.replace(':glvLW', formatter.format(data.dpslw[i].glv));
                    innerHTML = innerHTML.replace(':glsLW', formatter.format(data.dpslw[i].gls));
                    innerHTML = innerHTML.replace(':gldLW', formatter.format(data.dpslw[i].gld));
                    innerHTML = innerHTML.replace(':glper', formatter.format(data.dpslw[i].gl));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:gdl</td><td class=\"text-right\">:gdlLW</td><td class=\"text-right\">:gdm</td><td class=\"text-right\">:gdmLW</td><td class=\"text-right\">:gdmr</td><td class=\"text-right\">:gdmrLW</td><td class=\"text-right\">:gdj</td><td class=\"text-right\">:gdjLW</td><td class=\"text-right\">:gdv</td><td class=\"text-right\">:gdvLW</td><td class=\"text-right\">:gds</td><td class=\"text-right\">:gdsLW</td><td class=\"text-right\">:gdd</td><td class=\"text-right\">:gddLW</td><td class=\"text-right\">:gdper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Dinner</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':gdl', formatter2.format(data.dps[i].gdl));
                    innerHTML = innerHTML.replace(':gdm', formatter2.format(data.dps[i].gdm));
                    innerHTML = innerHTML.replace(':gdmr', formatter2.format(data.dps[i].gdmr));
                    innerHTML = innerHTML.replace(':gdj', formatter2.format(data.dps[i].gdj));
                    innerHTML = innerHTML.replace(':gdv', formatter2.format(data.dps[i].gdv));
                    innerHTML = innerHTML.replace(':gds', formatter2.format(data.dps[i].gds));
                    innerHTML = innerHTML.replace(':gdd', formatter2.format(data.dps[i].gdd));
                    innerHTML = innerHTML.replace(':gdlLW', formatter.format(data.dpslw[i].gdl));
                    innerHTML = innerHTML.replace(':gdmLW', formatter.format(data.dpslw[i].gdm));
                    innerHTML = innerHTML.replace(':gdmrLW', formatter.format(data.dpslw[i].gdmr));
                    innerHTML = innerHTML.replace(':gdjLW', formatter.format(data.dpslw[i].gdj));
                    innerHTML = innerHTML.replace(':gdvLW', formatter.format(data.dpslw[i].gdv));
                    innerHTML = innerHTML.replace(':gdsLW', formatter.format(data.dpslw[i].gds));
                    innerHTML = innerHTML.replace(':gddLW', formatter.format(data.dpslw[i].gdd));
                    innerHTML = innerHTML.replace(':gdper', formatter.format(data.dpslw[i].gd));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:gnl</td><td class=\"text-right\">:gnlLW</td><td class=\"text-right\">:gnm</td><td class=\"text-right\">:gnmLW</td><td class=\"text-right\">:gnmr</td><td class=\"text-right\">:gnmrLW</td><td class=\"text-right\">:gnj</td><td class=\"text-right\">:gnjLW</td><td class=\"text-right\">:gnv</td><td class=\"text-right\">:gnvLW</td><td class=\"text-right\">:gns</td><td class=\"text-right\">:gnsLW</td><td class=\"text-right\">:gnd</td><td class=\"text-right\">:gndLW</td><td class=\"text-right\">:gnper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Night</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':gnl', formatter2.format(data.dps[i].gnl));
                    innerHTML = innerHTML.replace(':gnm', formatter2.format(data.dps[i].gnm));
                    innerHTML = innerHTML.replace(':gnmr', formatter2.format(data.dps[i].gnmr));
                    innerHTML = innerHTML.replace(':gnj', formatter2.format(data.dps[i].gnj));
                    innerHTML = innerHTML.replace(':gnv', formatter2.format(data.dps[i].gnv));
                    innerHTML = innerHTML.replace(':gns', formatter2.format(data.dps[i].gns));
                    innerHTML = innerHTML.replace(':gnd', formatter2.format(data.dps[i].gnd));
                    innerHTML = innerHTML.replace(':gnlLW', formatter.format(data.dpslw[i].gnl));
                    innerHTML = innerHTML.replace(':gnmLW', formatter.format(data.dpslw[i].gnm));
                    innerHTML = innerHTML.replace(':gnmrLW', formatter.format(data.dpslw[i].gnmr));
                    innerHTML = innerHTML.replace(':gnjLW', formatter.format(data.dpslw[i].gnj));
                    innerHTML = innerHTML.replace(':gnvLW', formatter.format(data.dpslw[i].gnv));
                    innerHTML = innerHTML.replace(':gnsLW', formatter.format(data.dpslw[i].gns));
                    innerHTML = innerHTML.replace(':gndLW', formatter.format(data.dpslw[i].gnd));
                    innerHTML = innerHTML.replace(':gnper', formatter.format(data.dpslw[i].gn));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);
                }


                domTarget = '#salesTableRvc';
                for (i = 0; data.dps.length > i; i++) {
                    tr = document.createElement('tr');
                    target = document.querySelector(domTarget);
                    innerHTML = "";
                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:nsbl</td><td class=\"text-right\">:nsblLW</td><td class=\"text-right\">:nsbm</td><td class=\"text-right\">:nsbmLW</td><td class=\"text-right\">:nsbmr</td><td class=\"text-right\">:nsbmrLW</td><td class=\"text-right\">:nsbj</td><td class=\"text-right\">:nsbjLW</td><td class=\"text-right\">:nsbv</td><td class=\"text-right\">:nsbvLW</td><td class=\"text-right\">:nsbs</td><td class=\"text-right\">:nsbsLW</td><td class=\"text-right\">:nsbd</td><td class=\"text-right\">:nsbdLW</td><td class=\"text-right\">:nsbper</td>";

                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Breakfast</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':nsbl', formatter2.format(data.dps[i].nsbl));
                    innerHTML = innerHTML.replace(':nsbm', formatter2.format(data.dps[i].nsbm));
                    innerHTML = innerHTML.replace(':nsbmr', formatter2.format(data.dps[i].nsbmr));
                    innerHTML = innerHTML.replace(':nsbj', formatter2.format(data.dps[i].nsbj));
                    innerHTML = innerHTML.replace(':nsbv', formatter2.format(data.dps[i].nsbv));
                    innerHTML = innerHTML.replace(':nsbs', formatter2.format(data.dps[i].nsbs));
                    innerHTML = innerHTML.replace(':nsbd', formatter2.format(data.dps[i].nsbd));
                    innerHTML = innerHTML.replace(':nsblLW', formatter.format(data.dpslw[i].nsbl));
                    innerHTML = innerHTML.replace(':nsbmLW', formatter.format(data.dpslw[i].nsbm));
                    innerHTML = innerHTML.replace(':nsbmrLW', formatter.format(data.dpslw[i].nsbmr));
                    innerHTML = innerHTML.replace(':nsbjLW', formatter.format(data.dpslw[i].nsbj));
                    innerHTML = innerHTML.replace(':nsbvLW', formatter.format(data.dpslw[i].nsbv));
                    innerHTML = innerHTML.replace(':nsbsLW', formatter.format(data.dpslw[i].nsbs));
                    innerHTML = innerHTML.replace(':nsbdLW', formatter.format(data.dpslw[i].nsbd));
                    innerHTML = innerHTML.replace(':nsbper', formatter.format(data.dpslw[i].nsb));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:nsll</td><td class=\"text-right\">:nsllLW</td><td class=\"text-right\">:nslm</td><td class=\"text-right\">:nslmLW</td><td class=\"text-right\">:nslmr</td><td class=\"text-right\">:nslmrLW</td><td class=\"text-right\">:nslj</td><td class=\"text-right\">:nsljLW</td><td class=\"text-right\">:nslv</td><td class=\"text-right\">:nslvLW</td><td class=\"text-right\">:nsls</td><td class=\"text-right\">:nslsLW</td><td class=\"text-right\">:nsld</td><td class=\"text-right\">:nsldLW</td><td class=\"text-right\">:nslper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Lunch</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':nsll', formatter2.format(data.dps[i].nsll));
                    innerHTML = innerHTML.replace(':nslm', formatter2.format(data.dps[i].nslm));
                    innerHTML = innerHTML.replace(':nslmr', formatter2.format(data.dps[i].nslmr));
                    innerHTML = innerHTML.replace(':nslj', formatter2.format(data.dps[i].nslj));
                    innerHTML = innerHTML.replace(':nslv', formatter2.format(data.dps[i].nslv));
                    innerHTML = innerHTML.replace(':nsls', formatter2.format(data.dps[i].nsls));
                    innerHTML = innerHTML.replace(':nsld', formatter2.format(data.dps[i].nsld));
                    innerHTML = innerHTML.replace(':nsllLW', formatter.format(data.dpslw[i].nsll));
                    innerHTML = innerHTML.replace(':nslmLW', formatter.format(data.dpslw[i].nslm));
                    innerHTML = innerHTML.replace(':nslmrLW', formatter.format(data.dpslw[i].nslmr));
                    innerHTML = innerHTML.replace(':nsljLW', formatter.format(data.dpslw[i].nslj));
                    innerHTML = innerHTML.replace(':nslvLW', formatter.format(data.dpslw[i].nslv));
                    innerHTML = innerHTML.replace(':nslsLW', formatter.format(data.dpslw[i].nsls));
                    innerHTML = innerHTML.replace(':nsldLW', formatter.format(data.dpslw[i].nsld));
                    innerHTML = innerHTML.replace(':nslper', formatter.format(data.dpslw[i].nsl));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:nsdl</td><td class=\"text-right\">:nsdlLW</td><td class=\"text-right\">:nsdm</td><td class=\"text-right\">:nsdmLW</td><td class=\"text-right\">:nsdmr</td><td class=\"text-right\">:nsdmrLW</td><td class=\"text-right\">:nsdj</td><td class=\"text-right\">:nsdjLW</td><td class=\"text-right\">:nsdv</td><td class=\"text-right\">:nsdvLW</td><td class=\"text-right\">:nsds</td><td class=\"text-right\">:nsdsLW</td><td class=\"text-right\">:nsdd</td><td class=\"text-right\">:nsddLW</td><td class=\"text-right\">:nsdper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Dinner</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':nsdl', formatter2.format(data.dps[i].nsdl));
                    innerHTML = innerHTML.replace(':nsdm', formatter2.format(data.dps[i].nsdm));
                    innerHTML = innerHTML.replace(':nsdmr', formatter2.format(data.dps[i].nsdmr));
                    innerHTML = innerHTML.replace(':nsdj', formatter2.format(data.dps[i].nsdj));
                    innerHTML = innerHTML.replace(':nsdv', formatter2.format(data.dps[i].nsdv));
                    innerHTML = innerHTML.replace(':nsds', formatter2.format(data.dps[i].nsds));
                    innerHTML = innerHTML.replace(':nsdd', formatter2.format(data.dps[i].nsdd));
                    innerHTML = innerHTML.replace(':nsdlLW', formatter.format(data.dpslw[i].nsdl));
                    innerHTML = innerHTML.replace(':nsdmLW', formatter.format(data.dpslw[i].nsdm));
                    innerHTML = innerHTML.replace(':nsdmrLW', formatter.format(data.dpslw[i].nsdmr));
                    innerHTML = innerHTML.replace(':nsdjLW', formatter.format(data.dpslw[i].nsdj));
                    innerHTML = innerHTML.replace(':nsdvLW', formatter.format(data.dpslw[i].nsdv));
                    innerHTML = innerHTML.replace(':nsdsLW', formatter.format(data.dpslw[i].nsds));
                    innerHTML = innerHTML.replace(':nsddLW', formatter.format(data.dpslw[i].nsdd));
                    innerHTML = innerHTML.replace(':nsdper', formatter.format(data.dpslw[i].nsd));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:nsnl</td><td class=\"text-right\">:nsnlLW</td><td class=\"text-right\">:nsnm</td><td class=\"text-right\">:nsnmLW</td><td class=\"text-right\">:nsnmr</td><td class=\"text-right\">:nsnmrLW</td><td class=\"text-right\">:nsnj</td><td class=\"text-right\">:nsnjLW</td><td class=\"text-right\">:nsnv</td><td class=\"text-right\">:nsnvLW</td><td class=\"text-right\">:nsns</td><td class=\"text-right\">:nsnsLW</td><td class=\"text-right\">:nsnd</td><td class=\"text-right\">:nsndLW</td><td class=\"text-right\">:nsnper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Night</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':nsnl', formatter2.format(data.dps[i].nsnl));
                    innerHTML = innerHTML.replace(':nsnm', formatter2.format(data.dps[i].nsnm));
                    innerHTML = innerHTML.replace(':nsnmr', formatter2.format(data.dps[i].nsnmr));
                    innerHTML = innerHTML.replace(':nsnj', formatter2.format(data.dps[i].nsnj));
                    innerHTML = innerHTML.replace(':nsnv', formatter2.format(data.dps[i].nsnv));
                    innerHTML = innerHTML.replace(':nsns', formatter2.format(data.dps[i].nsns));
                    innerHTML = innerHTML.replace(':nsnd', formatter2.format(data.dps[i].nsnd));
                    innerHTML = innerHTML.replace(':nsnlLW', formatter.format(data.dpslw[i].nsnl));
                    innerHTML = innerHTML.replace(':nsnmLW', formatter.format(data.dpslw[i].nsnm));
                    innerHTML = innerHTML.replace(':nsnmrLW', formatter.format(data.dpslw[i].nsnmr));
                    innerHTML = innerHTML.replace(':nsnjLW', formatter.format(data.dpslw[i].nsnj));
                    innerHTML = innerHTML.replace(':nsnvLW', formatter.format(data.dpslw[i].nsnv));
                    innerHTML = innerHTML.replace(':nsnsLW', formatter.format(data.dpslw[i].nsns));
                    innerHTML = innerHTML.replace(':nsndLW', formatter.format(data.dpslw[i].nsnd));
                    innerHTML = innerHTML.replace(':nsnper', formatter.format(data.dpslw[i].nsn));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);
                }


                domTarget = '#checkTableRvc';
                for (i = 0; data.dps.length > i; i++) {
                    tr = document.createElement('tr');
                    target = document.querySelector(domTarget);
                    innerHTML = "";
                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:avgbl</td><td class=\"text-right\">:avgblLW</td><td class=\"text-right\">:avgbm</td><td class=\"text-right\">:avgbmLW</td><td class=\"text-right\">:avgbmr</td><td class=\"text-right\">:avgbmrLW</td><td class=\"text-right\">:avgbj</td><td class=\"text-right\">:avgbjLW</td><td class=\"text-right\">:avgbv</td><td class=\"text-right\">:avgbvLW</td><td class=\"text-right\">:avgbs</td><td class=\"text-right\">:avgbsLW</td><td class=\"text-right\">:avgbd</td><td class=\"text-right\">:avgbdLW</td><td class=\"text-right\">:avgbper</td>";

                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Breakfast</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':avgbl', formatter2.format(data.dps[i].avgbl));
                    innerHTML = innerHTML.replace(':avgbm', formatter2.format(data.dps[i].avgbm));
                    innerHTML = innerHTML.replace(':avgbmr', formatter2.format(data.dps[i].avgbmr));
                    innerHTML = innerHTML.replace(':avgbj', formatter2.format(data.dps[i].avgbj));
                    innerHTML = innerHTML.replace(':avgbv', formatter2.format(data.dps[i].avgbv));
                    innerHTML = innerHTML.replace(':avgbs', formatter2.format(data.dps[i].avgbs));
                    innerHTML = innerHTML.replace(':avgbd', formatter2.format(data.dps[i].avgbd));
                    innerHTML = innerHTML.replace(':avgblLW', formatter.format(data.dpslw[i].avgbl));
                    innerHTML = innerHTML.replace(':avgbmLW', formatter.format(data.dpslw[i].avgbm));
                    innerHTML = innerHTML.replace(':avgbmrLW', formatter.format(data.dpslw[i].avgbmr));
                    innerHTML = innerHTML.replace(':avgbjLW', formatter.format(data.dpslw[i].avgbj));
                    innerHTML = innerHTML.replace(':avgbvLW', formatter.format(data.dpslw[i].avgbv));
                    innerHTML = innerHTML.replace(':avgbsLW', formatter.format(data.dpslw[i].avgbs));
                    innerHTML = innerHTML.replace(':avgbdLW', formatter.format(data.dpslw[i].avgbd));
                    innerHTML = innerHTML.replace(':avgbper', formatter.format(data.dpslw[i].avgb));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:avgll</td><td class=\"text-right\">:avgllLW</td><td class=\"text-right\">:avglm</td><td class=\"text-right\">:avglmLW</td><td class=\"text-right\">:avglmr</td><td class=\"text-right\">:avglmrLW</td><td class=\"text-right\">:avglj</td><td class=\"text-right\">:avgljLW</td><td class=\"text-right\">:avglv</td><td class=\"text-right\">:avglvLW</td><td class=\"text-right\">:avgls</td><td class=\"text-right\">:avglsLW</td><td class=\"text-right\">:avgld</td><td class=\"text-right\">:avgldLW</td><td class=\"text-right\">:avglper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Lunch</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':avgll', formatter2.format(data.dps[i].avgll));
                    innerHTML = innerHTML.replace(':avglm', formatter2.format(data.dps[i].avglm));
                    innerHTML = innerHTML.replace(':avglmr', formatter2.format(data.dps[i].avglmr));
                    innerHTML = innerHTML.replace(':avglj', formatter2.format(data.dps[i].avglj));
                    innerHTML = innerHTML.replace(':avglv', formatter2.format(data.dps[i].avglv));
                    innerHTML = innerHTML.replace(':avgls', formatter2.format(data.dps[i].avgls));
                    innerHTML = innerHTML.replace(':avgld', formatter2.format(data.dps[i].avgld));
                    innerHTML = innerHTML.replace(':avgllLW', formatter.format(data.dpslw[i].avgll));
                    innerHTML = innerHTML.replace(':avglmLW', formatter.format(data.dpslw[i].avglm));
                    innerHTML = innerHTML.replace(':avglmrLW', formatter.format(data.dpslw[i].avglmr));
                    innerHTML = innerHTML.replace(':avgljLW', formatter.format(data.dpslw[i].avglj));
                    innerHTML = innerHTML.replace(':avglvLW', formatter.format(data.dpslw[i].avglv));
                    innerHTML = innerHTML.replace(':avglsLW', formatter.format(data.dpslw[i].avgls));
                    innerHTML = innerHTML.replace(':avgldLW', formatter.format(data.dpslw[i].avgld));
                    innerHTML = innerHTML.replace(':avglper', formatter.format(data.dpslw[i].avgl));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:avgdl</td><td class=\"text-right\">:avgdlLW</td><td class=\"text-right\">:avgdm</td><td class=\"text-right\">:avgdmLW</td><td class=\"text-right\">:avgdmr</td><td class=\"text-right\">:avgdmrLW</td><td class=\"text-right\">:avgdj</td><td class=\"text-right\">:avgdjLW</td><td class=\"text-right\">:avgdv</td><td class=\"text-right\">:avgdvLW</td><td class=\"text-right\">:avgds</td><td class=\"text-right\">:avgdsLW</td><td class=\"text-right\">:avgdd</td><td class=\"text-right\">:avgddLW</td><td class=\"text-right\">:avgdper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Dinner</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':avgdl', formatter2.format(data.dps[i].avgdl));
                    innerHTML = innerHTML.replace(':avgdm', formatter2.format(data.dps[i].avgdm));
                    innerHTML = innerHTML.replace(':avgdmr', formatter2.format(data.dps[i].avgdmr));
                    innerHTML = innerHTML.replace(':avgdj', formatter2.format(data.dps[i].avgdj));
                    innerHTML = innerHTML.replace(':avgdv', formatter2.format(data.dps[i].avgdv));
                    innerHTML = innerHTML.replace(':avgds', formatter2.format(data.dps[i].avgds));
                    innerHTML = innerHTML.replace(':avgdd', formatter2.format(data.dps[i].avgdd));
                    innerHTML = innerHTML.replace(':avgdlLW', formatter.format(data.dpslw[i].avgdl));
                    innerHTML = innerHTML.replace(':avgdmLW', formatter.format(data.dpslw[i].avgdm));
                    innerHTML = innerHTML.replace(':avgdmrLW', formatter.format(data.dpslw[i].avgdmr));
                    innerHTML = innerHTML.replace(':avgdjLW', formatter.format(data.dpslw[i].avgdj));
                    innerHTML = innerHTML.replace(':avgdvLW', formatter.format(data.dpslw[i].avgdv));
                    innerHTML = innerHTML.replace(':avgdsLW', formatter.format(data.dpslw[i].avgds));
                    innerHTML = innerHTML.replace(':avgddLW', formatter.format(data.dpslw[i].avgdd));
                    innerHTML = innerHTML.replace(':avgdper', formatter.format(data.dpslw[i].avgd));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);

                    htmlTemplate =
                        "<td class=\"text-left\">:DayPart</td><td class=\"text-left\">:Tienda</td><td class=\"text-right\">:avgnl</td><td class=\"text-right\">:avgnlLW</td><td class=\"text-right\">:avgnm</td><td class=\"text-right\">:avgnmLW</td><td class=\"text-right\">:avgnmr</td><td class=\"text-right\">:avgnmrLW</td><td class=\"text-right\">:avgnj</td><td class=\"text-right\">:avgnjLW</td><td class=\"text-right\">:avgnv</td><td class=\"text-right\">:avgnvLW</td><td class=\"text-right\">:avgns</td><td class=\"text-right\">:avgnsLW</td><td class=\"text-right\">:avgnd</td><td class=\"text-right\">:avgndLW</td><td class=\"text-right\">:avgnper</td>";
                    tr = document.createElement('tr');
                    innerHTML = htmlTemplate;
                    innerHTML = innerHTML.replace(':DayPart', "<b>Night</b>");
                    innerHTML = innerHTML.replace(':Tienda', "<b>" + data.dps[i].sucursal + "</b>");
                    innerHTML = innerHTML.replace(':avgnl', formatter2.format(data.dps[i].avgnl));
                    innerHTML = innerHTML.replace(':avgnm', formatter2.format(data.dps[i].avgnm));
                    innerHTML = innerHTML.replace(':avgnmr', formatter2.format(data.dps[i].avgnmr));
                    innerHTML = innerHTML.replace(':avgnj', formatter2.format(data.dps[i].avgnj));
                    innerHTML = innerHTML.replace(':avgnv', formatter2.format(data.dps[i].avgnv));
                    innerHTML = innerHTML.replace(':avgns', formatter2.format(data.dps[i].avgns));
                    innerHTML = innerHTML.replace(':avgnd', formatter2.format(data.dps[i].avgnd));
                    innerHTML = innerHTML.replace(':avgnlLW', formatter.format(data.dpslw[i].avgnl));
                    innerHTML = innerHTML.replace(':avgnmLW', formatter.format(data.dpslw[i].avgnm));
                    innerHTML = innerHTML.replace(':avgnmrLW', formatter.format(data.dpslw[i].avgnmr));
                    innerHTML = innerHTML.replace(':avgnjLW', formatter.format(data.dpslw[i].avgnj));
                    innerHTML = innerHTML.replace(':avgnvLW', formatter.format(data.dpslw[i].avgnv));
                    innerHTML = innerHTML.replace(':avgnsLW', formatter.format(data.dpslw[i].avgns));
                    innerHTML = innerHTML.replace(':avgndLW', formatter.format(data.dpslw[i].avgnd));
                    innerHTML = innerHTML.replace(':avgnper', formatter.format(data.dpslw[i].avgn));
                    tr.innerHTML = innerHTML;
                    target.appendChild(tr);
                }
            }
            endLoadingData();
        }

        function clearTable() {
            var tabla = document.getElementById("guestTableRvc");
            tabla.innerHTML = "";
            tabla = document.getElementById("salesTableRvc");
            tabla.innerHTML = "";
            tabla = document.getElementById("checkTableRvc");
            tabla.innerHTML = "";
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
