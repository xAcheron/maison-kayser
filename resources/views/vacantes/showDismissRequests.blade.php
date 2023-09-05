@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'getBajas'])
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
                    <th>Sucursal</th>
                    <th>Puesto</th>
                    <th>Nombre</th>
                    <th>Recontratable</th>
                    <th>Boletinado</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Baja</th>
                    <th>Acci&oacute;n</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Sucursal</th>
                    <th>Puesto</th>
                    <th>Nombre</th>
                    <th>Recontratable</th>
                    <th>Boletinado</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Baja</th>
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
        function cancelaBaja(id, nom) {

            swal({
                title: "Estas segur@?",
                text: "Se regresara al empleado " + nom + " a la plantilla!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                allowOutsideClick: false,
                confirmButtonText: 'Si, actualizar!',
                cancelButtonText: 'No, cancelar!'
            }).then((result) => {
                swal({
                    title: "Puedes agregar un comentario",
                    input: 'textarea',
                    inputPlaceholder: 'Type your message here...',
                    showCancelButton: true
                }).then((value) => {
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
                        url: "{{ route('guardabaja') }}",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "accion": 2,
                            "id": id,
                            "comentario": value
                        },
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

        function editarBaja(id, idBaja) {

            var data;

            var params = {
                _token: "{{ csrf_token() }}",
                idEmpleado: id,
                idBaja: idBaja,
            };

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "{{ route('getBaja') }}",
                data: params,
                success: function(msg) {
                    data = msg.data[0];

                    swal({
                        title: "Puedes agregar un comentario",
                        html: `<form id="formBaja" name="formBaja">
                                 <input type="hidden" value="{{ csrf_token() }}" name="_token">
                                 <input type="hidden" value="3" name="accion">
                                 <input type="hidden" value="${id}" name="id">
                                 <input type="hidden" value="${idBaja}" name="idBaja">
                                 <div class="form-group text-left">
                                       <label>Tipo de baja</label>
                                       <select name="tipo" id="tipo" class="form-control" value="${data.tipo}">
                                          @foreach ($tipoBaja as $item)
                                             <option value="{{ $item->idTipo }}">{{ $item->tipo }}</option>
                                          @endforeach
                                       </select>
                                 </div>
                                 <div class="form-group text-left">
                                       <label>Comentario</label>
                                       <textarea name="comentario" id="comentario" class="form-control">${data.comentario ?? ''}</textarea>
                                 </div>
                                  <div class="" style="display: flex; justify-content: space-around">
                                       <div class="form-check">
                                          <input class="form-check-input" type="checkbox" id="boletinado" name="boletinado" ${data.boletinado ? 'checked' : ''}>
                                          <label class="form-check-label" for="boletinado">
                                             Boletinado
                                          </label>
                                       </div>
                                       <div class="form-check col-auto">
                                          <input class="form-check-input" type="checkbox" id="recontratable" name="recontratable" ${data.recontratable ? 'checked' : ''}>
                                          <label class="form-check-label" for="recontratable">
                                             Recontratable
                                          </label>
                                       </div>
                                 </div>
                              </form>`,
                        inputPlaceholder: 'Type your message here...',
                        showCancelButton: true,
                        onBeforeOpen() {},
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

                },
                error: function() {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Algo ha salido mal!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                }
            });
        }



        $(document).ready(function() {

            $('#datatables').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('getSolicitudesBaja') }}",
                    "type": "POST",
                    "data": function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                "columns": [{
                        "data": "sucursal"
                    },
                    {
                        "data": "puesto"
                    },
                    {
                        "data": "nombre"
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return `<i class="material-icons ${row.recontratable == 1 ? 'text-success' : 'text-danger'}">${row.recontratable == 1 ? 'done' : 'close'}</i>`;
                        }
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return `<i class="material-icons ${row.boletinado == 1 ? 'text-success' : 'text-danger'}">${row.boletinado == 1 ? 'done' : 'close'}</i>`;
                        }
                    },
                    {
                        "data": "fechaSolBaja"
                    },
                    {
                        "data": "fechaBaja"
                    },
                    {
                        "render": function(data, type, row, meta) {
                            return `${ row.estado == 6 ? `<a href="#" onclick="editarBaja('${row.idEmpleado}','${row.idBaja}')" class="btn btn-link btn-info btn-just-icon like"><i class="material-icons">edit</i></a>` : `<a href="#" onclick="confirmaBaja('${row.idEmpleado}','${row.nombre}')" class="btn btn-link btn-danger btn-just-icon like"><i class="material-icons">remove_circle_outline</i></a>`}
									<a href="#" onclick="cancelaBaja('${row.idEmpleado}','${row.nombre}')" class="btn btn-link btn-success btn-just-icon like">
										<i class="material-icons">open_in_new</i>
									</a>`;
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
