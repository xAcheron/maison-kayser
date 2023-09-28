@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'Discounts'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-info">
                    <div class="card-icon">
                        <i class="material-icons">timeline</i>
                    </div>
                    <h4 class="card-title">Discounts
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
                        <div class="col-3">
                            Tier:<br>
                            <select class="select2-item" id="tier" data-size="7" style="width:100%;" title="Tier">
                                <option value="0" disabled selected>Select a Tier</option>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Discounts</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 30vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Dsc #</th>
                                            <th>Discount</th>
                                            <th>Redeemed</th>
                                            <th>Value</th>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Location Discounts</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="container-fluid p-2" style="overflow:auto !important; height: 50vh !important;">
                                <table class="table table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>Location</th>
                                            <th>Dsc #</th>
                                            <th>Discount</th>
                                            <th>Redeemed</th>
                                            <th>Value</th>
                                        </tr>
                                        </head>
                                    <tbody id="baseTableLocation">

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
                    let url = "{{ route('getReport', ['id' => 8, 'format' => 'xlsx']) }}";
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
                        _token: "{{ csrf_token() }}"
                    };

                    $.ajax({
                        type: "POST",
                        url: "{{ route('getReport', ['id' => 8, 'format' => 'json']) }}",
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

        function openDetailReport(e, idDsc, idLocation) {
            const el = document.querySelector("#Dsc" + idDsc);
            if (el.dataset.open == "0") {

                el.dataset.open = 1;

                loadingData();

                var params = {
                    daterange: $("#daterange").val(),
                    discount: idDsc,
                    location: idLocation == 0 ? $("#location").val() : idLocation,
                    _token: "{{ csrf_token() }}",
                    desglosar: true
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('getReport', ['id' => 9, 'format' => 'json']) }}",
                    data: params,
                    success: function(msg) {
                        if (msg.success == true) {
                            if (msg.data.dps.length > 0) {
                                // if (!(Object.keys(msg.data).length === 0) && msg.data.constructor === Object) {
                                //makeTable(msg.data);

                                makeSubTable(idDsc, msg.data.dps);
                            } else {
                                clearTable();
                            }
                        }
                    },
                    error: function() {

                    }

                });
            } else {
                const nodes = document.querySelectorAll("#Item_" + idDsc);
                Array.prototype.forEach.call(nodes, function(node) {
                    node.parentNode.removeChild(node);
                });
                el.dataset.open = 0;
            }
        }

        function makeSubTable(idDsc, data) {
            let htmlTemplate =
                "<td></td><td class=\"text-left\">:itemnumber</td><td class=\"text-left\">:menuitem</td><td class=\"text-right\">:quantity</td><td class=\"text-right\">:value</td>";
            let domTarget = "#Dsc" + idDsc;
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

            for (var i = data.length - 1; i >= 0; i--) {
                tr = document.createElement('tr');
                tr.id = "Item_" + idDsc;
                innerHTML = htmlTemplate;
                innerHTML = innerHTML.replace(':itemnumber', data[i].idItemMicros);
                innerHTML = innerHTML.replace(':menuitem', data[i].itemName);
                innerHTML = innerHTML.replace(':quantity', formatter2.format(data[i].cantidad));
                innerHTML = innerHTML.replace(':value', formatter2.format(data[i].descuento));
                tr.innerHTML = innerHTML;
                target.parentNode.insertBefore(tr, target.nextSibling);
            }

            endLoadingData();

        }

        function makeTable(data) {
            clearTable();

            let htmlTemplate =
                "<td class=\"text-left\" onClick=\"openDetailReport(event, :iddsc, 0)\"><span class=\"material-icons\">chevron_right</span></td><td class=\"text-left\">:DscNumber</td><td class=\"text-left\">:DscName</td><td class=\"text-right\">:quantity</td><td class=\"text-right\">:value</td>";
            let domTarget = '#baseTable';
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

            for (var i = 0; i < data.discounts.length; i++) {
                tr = document.createElement('tr');
                innerHTML = htmlTemplate;
                tr.id = "Dsc" + data.discounts[i].idDescuento;
                tr.dataset.open = 0;
                innerHTML = innerHTML.replace(':iddsc', data.discounts[i].idDescuento);
                innerHTML = innerHTML.replace(':DscNumber', "<b>" + data.discounts[i].idDescuento + "</b>");
                innerHTML = innerHTML.replace(':DscName', "<b>" + data.discounts[i].discount + "</b>");
                innerHTML = innerHTML.replace(':quantity', formatter2.format(data.discounts[i].cantidad));
                innerHTML = innerHTML.replace(':value', formatter2.format(data.discounts[i].descuento));

                tr.innerHTML = innerHTML;
                target.appendChild(tr);
            }

            htmlTemplate =
                "<td class=\"text-left\">:location</td><td class=\"text-left\">:DscNumber</td><td class=\"text-left\">:DscName</td><td class=\"text-right\">:quantity</td><td class=\"text-right\">:value</td>";
            domTarget = '#baseTableLocation';
            tr = document.createElement('tr');
            target = document.querySelector(domTarget);
            innerHTML = "";

            for (var i = 0; i < data.locations.length; i++) {
                tr = document.createElement('tr');
                innerHTML = htmlTemplate;

                innerHTML = innerHTML.replace(':location', "<b>" + data.locations[i].location + "</b>");
                innerHTML = innerHTML.replace(':DscNumber', "<b>" + data.locations[i].idDescuento + "</b>");
                innerHTML = innerHTML.replace(':DscName', "<b>" + data.locations[i].discount + "</b>");
                innerHTML = innerHTML.replace(':quantity', formatter2.format(data.locations[i].cantidad));
                innerHTML = innerHTML.replace(':value', formatter2.format(data.locations[i].descuento));

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
            var tabla = document.getElementById("baseTable");
            tabla.innerHTML = "";
            tabla = document.getElementById("baseTableLocation");
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
