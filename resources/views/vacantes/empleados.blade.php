@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'empleados'])
@section('content')
    <div class="card">
        <div class="card-body row">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="col-sm-3">
                <div class="form-group bmd-form-group">
                    <label>Nombre</label>
                    <input id="findNombre" name="findNombre" type="text" class="form-control">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group bmd-form-group">
                    <label>Puesto</label>
                    <input id="findPuesto" name="findPuesto" type="text" class="form-control">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group bmd-form-group">
                    <label>Sucursal</label>
                    <input id="findSucursal" name="findSucursal" type="text" class="form-control">
                </div>
            </div>
            <div class="col-sm-2 col-md-2">
                <div class="btn-group">
                    <button id="findVacantebtn" class="btn btn-white btn-round btn-just-icon">
                        <i class="material-icons">search</i>
                        <div class="ripple-container"></div>
                    </button>
                    <a class="btn btn-white btn-round btn-just-icon" href="{{ route('formNewEmployee') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <div class="ripple-container"></div>
                        <i class="material-icons">add</i>
                    </a>
                    <a class="btn btn-white btn-round btn-just-icon" href="{{ route('uploadXlsx') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <div class="ripple-container"></div>
                        <i class="material-icons">upload</i>
                    </a>
                </div>
            </div>
            <div class="col-sm-1">
            </div>
        </div>
    </div>
    <div class="row">
        <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%"
            style="width:100%">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Puesto</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th>Acci&oacute;n</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Empleado</th>
                    <th>Puesto</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th>Acci&oacute;n</th>
                </tr>
            </tfoot>
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
        $('#nuevoEmpleado').on('click', function() {
                swal({
                    title: 'Nuevo Empleado',
                    html: `<form id="formEmpelado" name="formEmpelado">
                        <input type="hidden" value="{{ csrf_token() }}" name="_token">
                        <div class="form-group text-left">
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre">
                        </div>
                        <div class="form-group text-left">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fechaNacimiento">
                        </div>
                        <div class="form-group text-left">
                            <label>Fecha de Ingreso</label>
                            <input type="date" class="form-control" name="fechaIngreso">
                        </div>
                        <div class="form-group text-left">
                            <label>Sucursal</label>
                            <select name="sucursal" id="sucursal" class="form-control" onchange="getPuestos()">
                                <option disabled selected>Seleccione una sucursal</option>
                                @foreach ($sucursales as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group text-left">
                            <label>Puesto</label>
                            <select id="puesto" name="puesto" class="form-control">
                                <option disabled selected>Seleccione un puesto</option>
                            </select>
                        </div>
                    </form>`,
                    showCancelButton: true,
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false
                }).then(function(result) {
                    var form = $('#formEmpelado')[0];
                    var data = new FormData(form);
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{ route('registrarEmpleado') }}",
                        data: data,
                        processData: false,
                        contentType: false,
                        cache: false,
                        success: function(msg) {
                            if (msg.success) {
                                swal({
                                    type: 'success',
                                    title: msg.msg
                                });
                            } else {
                                swal({
                                    type: 'error',
                                    title: 'Oops...',
                                    text: 'Algo ha salido mal!',
                                    footer: 'Problemas? sit@prigo.com.mx',
                                });
                            }
                        },
                        error: function(e) {
                            swal.fire('', e, 'error')
                        }
                    });
                }).catch(swal.noop)
            }

        )

        function getPuestos() {
            $.ajax({
                type: "POST",
                url: "{{ route('getPuestos') }}",
                data: {
                    "idSucursal": $("#sucursal").val(),
                    "_token": "{{ csrf_token() }}"
                },
                success: function(msg) {
                    // obj = JSON.parse(msg);
                    if (msg.success) {
                        console.log(msg)
                        var select = document.getElementById('puesto');
                        select.innerHTML = "<option disabled selected>Seleccione un puesto</option>";
                        msg.data.forEach(element => {
                            var html = '<option value=":id">:nombre</option>'
                            html = html.replace(':id', element.idPuesto);
                            html = html.replace(':nombre', element.puesto);
                            select.innerHTML += html;
                        });
                    } else {
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: 'Algo ha salido mal!',
                            footer: 'Problemas? sit@prigo.com.mx',
                        });
                    }
                }
            });
        }

        $(document).ready(function() {
            $('#datatables').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('getEmpleados') }}",
                    "type": "POST",
                    "data": function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                "columns": [{
                        "data": "nombre"
                    },
                    {
                        "data": "puesto"
                    },
                    {
                        "data": "sucursal"
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return "<span style=\"display:block;\"><span>" + row.estado + "</span>";
                        }
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return "<a href=\"{{ route('detalleempleado') }}/" + row.idEmpleado +
                                "\" class=\"btn btn-link btn-info btn-just-icon like\"><i class=\"material-icons\">open_in_new</i></a> @if ($role == 1 || $role == 5) <a href=\"#\" onclick=\"confirmaBaja('" +
                                row.idEmpleado + "','" + row.nombre +
                                "')\" class=\"btn btn-link btn-danger btn-just-icon like\"><i class=\"material-icons\">remove_circle_outline</i></a>@endif";
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
                    table.column(1).search($("#findPuesto").val());
                else
                    table.column(1).search("");
                if ($("#findNombre").val() != "")
                    table.column(0).search($("#findNombre").val());
                else
                    table.column(0).search("");
                if ($("#findSucursal").val() != "" || $("#findPuesto").val() != "" || $("#findNombre")
                    .val() != "")
                    table.draw();
            });
        });

        function confirmaBaja(id, nom) {

            swal({
                title: "Estas segur@?",
                text: "Se aplicara la baja al empleado: " + nom + "!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                allowOutsideClick: false,
                confirmButtonText: 'Si, actualizar!',
                cancelButtonText: 'No, cancelar!'
            }).then((result) => {
                swal({
                    title: "Puedes agregar un comentario",
                    html: `<form id="formBaja" name="formBaja">
                    <input type="hidden" value="{{ csrf_token() }}" name="_token">
                    <input type="hidden" value="1" name="accion">
                    <input type="hidden" value="${id}" name="id">
                    <div class="form-group text-left">
                        <label>Tipo de baja</label>
                        <select name="tipo" id="tipo" class="form-control">
                            @foreach ($tipoBaja as $item)
                                <option value="{{ $item->idTipo }}">{{ $item->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-left">
                        <label>Comentario</label>
                        <textarea name="comentario" id="comentario" class="form-control"></textarea>
                    </div>
                    <div class="" style="display: flex; justify-content: space-around">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="boletinado" name="boletinado" checked>
                            <label class="form-check-label" for="boletinado">
                                Boletinado
                            </label>
                        </div>
                        <div class="form-check col-auto">
                            <input class="form-check-input" type="checkbox" id="recontratable" name="recontratable">
                            <label class="form-check-label" for="recontratable">
                                Recontratable
                            </label>
                        </div>
                    </div>
                </form>`,
                    inputPlaceholder: 'Type your message here...',
                    showCancelButton: true
                }).then((value) => {
                    var form = $('#formBaja')[0];
                    var data = new FormData(form);
                    swal({
                        title: 'Guardando...',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: false,
                        text: 'Espere un momento...'
                    });
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{ route('guardabaja') }}",
                        data: data,
                        processData: false,
                        contentType: false,
                        cache: false,
                        success: function(msg) {
                            swal({
                                type: 'success',
                                title: 'Tu vacante se ha actualizado!'
                            });

                            $('.button').prop('disabled', false);

                        },
                        error: function() {
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: 'Algo ha salido mal!',
                                footer: 'Problemas? sit@prigo.com.mx	',
                            });
                            $('.button').prop('disabled', false);
                        }
                    });

                });
            });
        }
    </script>
@endsection
