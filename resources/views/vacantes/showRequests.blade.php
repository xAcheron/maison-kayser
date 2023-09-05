@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'consultavacantes'])
@section('content')
    <form id="formExporta" action="{{ route('exportavacantes') }}" method="POST" target="_blank" class="form-horizontal">
        <div class="card">
            <div class="card-body row">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="tipo" value="{{ $tipo }}">
                <div class="col-sm-3">
                    <div class="form-group bmd-form-group">
                        <label class="">Puesto</label>
                        <input id="findPuesto" name="findPuesto" type="text" class="form-control">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group bmd-form-group">
                        <label class="">Sucursal</label>
                        <input id="findSucursal" name="findSucursal" type="text" class="form-control">
                    </div>
                </div>
                <div class="col-sm-1">
                    <button id="findVacantebtn" class="btn btn-white btn-round btn-just-icon">
                        <i class="material-icons">search</i>
                        <div class="ripple-container"></div>
                    </button>
                </div>
                <div class="col-sm-1">
                    <button id="exportVacantebtn" class="btn btn-white btn-round btn-just-icon">
                        <i class="material-icons">cloud_download</i>
                        <div class="ripple-container"></div>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <div class="row">
        <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%"
            style="width:100%">
            <thead>
                <tr>
                    <th>Solicitud</th>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Puesto</th>
                    <th>Solicitud</th>
                    <th>Reclutador</th>
                    <th>Estado</th>
                    <th>Acci&oacute;n</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Solicitud</th>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Puesto</th>
                    <th>Solicitud</th>
                    <th>Reclutador</th>
                    <th>Estado</th>
                    <th>Acci&oacute;n</th>
                </tr>
            </tfoot>
            <tbody>
            </tbody>
        </table>
    </div>
@endsection
@section('jsimports')
    <script src="{{ asset('MaterialBS/js/plugins/bootstrap-selectpicker.js') }}"></script>
    <script src="{{ asset('MaterialBS/js/plugins/jquery.select-bootstrap.js') }}"></script>
    <script src="{{ asset('MaterialBS/js/plugins/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('MaterialBS/assets-for-demo/js/modernizr.js') }}"></script>

    <script src="{{ asset('MaterialBS/js/plugins/jquery.datatables.js') }}"></script>
@endsection
@section('aditionalScripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#datatables').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('getSolicitudes') }}",
                    "type": "POST",
                    "data": function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.tipo = "{{ $tipo }}";
                    }
                },
                "columns": [{
                        "data": "idSolicitud"
                    },
                    {
                        "data": "fechaCrea"
                    },
                    {
                        "data": "sucursal"
                    },
                    {
                        "data": "puesto"
                    },
                    {
                        "data": "solicitud"
                    },
                    {
                        "data": "reclutador"
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return "<span style=\"display:block;\"><span>" + row.estado +
                                "</span> <i class=\"pull-right material-icons " + (row.atraso == 1 ?
                                    " text-danger\">highlight_off" :
                                    " text-success\">check_circle_outline") + "</i>";
                        }
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return "<a href=\"{{ route('detallevacante') }}/" + row.idSolicitud +
                                "\" class=\"btn btn-link btn-info btn-just-icon like\"><i class=\"material-icons\">open_in_new</i></a>";
                        }
                    }
                ]
            });

            var table = $('#datatables').DataTable();

            $('#findVacantebtn').on('click', function(event) {
                event.preventDefault();
                if ($("#findSucursal").val() != "")
                    table.column(2).search($("#findSucursal").val());
                else
                    table.column(2).search("");
                if ($("#findPuesto").val() != "")
                    table.column(3).search($("#findPuesto").val());
                else
                    table.column(3).search("");
                if ($("#findSucursal").val() != "" || $("#findPuesto").val() != "")
                    table.draw();
            });
            $("#datatables_filter").hide();
            $('#exportVacantebtn').on('click', function() {
                if ($("#findSucursal").val() != "")
                    table.column(2).search($("#findSucursal").val());
                else
                    table.column(2).search("");
                if ($("#findPuesto").val() != "")
                    table.column(3).search($("#findPuesto").val());
                else
                    table.column(3).search("");
                if ($("#findSucursal").val() != "" || $("#findPuesto").val() != "")
                    table.draw();
                $('form#formExporta').submit();
            });
        });
    </script>
@endsection
