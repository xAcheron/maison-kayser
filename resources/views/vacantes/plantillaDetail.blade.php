@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'plantilla'])
@section('content')
    <style>
        @keyframes slidein {
            from {
                padding-left: 100%;
                width: 100%
            }

            to {
                padding-left: 0%;
                width: 0px;
            }
        }

        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner-border {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            vertical-align: -0.125em;
            border: 0.25em solid currentcolor;
            border-right-color: transparent;
            border-radius: 50%;
            -webkit-animation: .75s linear infinite spinner-border;
            animation: .75s linear infinite spinner-border;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <h4 id="header-label"></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div id="detModalTabContent" class="col">
                </div>
                <div class="col-1" style="display: none; transition: linear 0.7s" id="user">
                    <div class="card m-0">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0" style="align-self: center"><span id="nombre"></span> - <span
                                    id="activo"></span></h5>
                            <button class="btn btn-link btn-just-icon btn-danger" id="btnDelete">
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div>
                                <span class="font-weight-bold">Sucursal:</span> <span id="sucursal"></span>
                                <span>
                                    <button class="btn btn-link btn-just-icon btn-sm" onclick="editSucursal(1, 2)"
                                        style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                                        <i class="material-icons">edit</i>
                                    </button>
                                </span>
                            </div>
                            <div>
                                <span class="font-weight-bold">Puesto:</span> <span id="puesto"></span>
                                <span>
                                    <button class="btn btn-link btn-just-icon btn-sm" onclick="editPuesto()"
                                        style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                                        <i class="material-icons">edit</i>
                                    </button>
                                </span>
                            </div>
                            <div><span class="font-weight-bold">Fecha de Ingreso:</span> <span id="fechaIngreso"></span>
                            </div>
                            <div><span class="font-weight-bold">NSS:</span> <span id="nss"></span></div>
                            <div><span class="font-weight-bold">RFC:</span> <span id="rfc"></span></div>
                            <div><span class="font-weight-bold">CURP:</span> <span id="curp"></span></div>
                            <div><span class="font-weight-bold">Correo:</span> <span id="correo"></span></div>
                            <div><span class="font-weight-bold">Fecha de Nacimiento:</span> <span
                                    id="fechaNacimiento"></span></div>

                            <div class="mt-3">
                                <h5>Historial</h5>
                                <ul class="timeline timeline-simple" id="historial">

                                </ul>
                            </div>
                        </div>
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
    <script>
        $(document).ready(function() {
            getDetPlantilla('{{ $id }}', '{{ $nombre }}')
        })

        var idEmpleado = 0;
        var idPuesto = 0;
        var idsucursal = 0;
        var nomEmpleado = '';

        function getDetPlantilla(ids, nom) {
            $("#header-label").text("Plantilla " + nom);
            var params = {
                "ids": ids,
                "type": 'page',
                "_token": "{{ csrf_token() }}"
            };
            $.ajax({
                type: "POST",
                url: "{{ route('detPlantillaTable') }}",
                data: params,
                success: function(msg) {
                    $("#detModalTabContent").empty();
                    $("#detModalTabContent").append(msg);
                    $(".loader").remove();
                },
                error: function() {
                    console.log("error");
                }
            });
        }

        $('#btnDelete').on('click', function() {
            swal({
                title: "Estas segur@?",
                text: "Se aplicara la baja al empleado: " + nomEmpleado + "!",
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
                    <input type="hidden" value="${idEmpleado}" name="id">
                    <div class="form-group text-left">
                        <label>Tipo de baja</label>
                        <select name="tipo" id="tipo" class="select2" style="width: 100%">
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
                            <input class="" type="checkbox" id="boletinado" name="boletinado">
                            <label class="form-check-label" for="boletinado">
                                Boletinado
                            </label>
                        </div>
                        <div class="form-check col-auto">
                            <input class="x" type="checkbox" id="recontratable" name="recontratable">
                            <label class="form-check-label" for="recontratable">
                                Recontratable
                            </label>
                        </div>
                    </div>
                </form>`,
                    inputPlaceholder: 'Type your message here...',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    onBeforeOpen() {
                        $('.select2').select2({
                            dropdownParent: $('.swal2-container')
                        })
                    },
                }).then((value) => {

                    if (value.value) {

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
                    }

                });
            });
        })

        function detailEmployee(id) {
            var params = {
                "id": id,
                "_token": "{{ csrf_token() }}"
            };
            $.ajax({
                type: "POST",
                url: "{{ route('getEmployeeDetail') }}",               
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        $('#user').show()
                        $("#user").addClass('col-6')
                        $('#nombre').html(msg.empleado.nombre)
                        $('#activo').html(msg.empleado.estado)
                        $('#sucursal').html(msg.empleado.sucursal)
                        $('#puesto').html(msg.empleado.puesto)
                        $('#fechaIngreso').html(msg.empleado.fechaIngreso)
                        $('#rfc').html(msg.empleado.rfc)
                        $('#curp').html(msg.empleado.curp)
                        $('#nss').html(msg.empleado.nss)
                        $('#correo').html(msg.empleado.correo)
                        $('#fechaNacimiento').html(msg.empleado.fechaNacimiento)

                        idEmpleado = msg.empleado.idEmpleado;
                        idPuesto = msg.empleado.idPuesto;
                        idSucursal = msg.empleado.idSucursal;
                        nomEmpleado = msg.empleado.nombre;

                        var timeline = $('#historial')
                        timeline.html('')

                        window.scroll({
                            top: 0,
                            behavior: 'smooth'
                        });

                        if (msg.partidas.length > 0) {
                            var partidas = msg.partidas;

                            partidas.forEach(element => {
                                timeline.append(timelineElement(element));
                            });
                        }
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        }

        function timelineElement(element) {
            return `<li class="timeline-inverted">
                        <div class="timeline-badge ${element.solicitud == 'Solicita contratacion' ? 'success' : element.solicitud == 'Baja Confirmada' ? 'danger' : 'info'}">
                            <i class="material-icons">
                                ${element.solicitud == 'Solicita contratacion' ? 'card_travel' : element.solicitud == 'Baja Confirmada' ? 'delete' : 'info'}
                            </i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <span class="label label-default">
                                    ${element.fecha}
                                </span>
                            </div>
                            <div class="timeline-body">
                                <p>
                                    ${element.solicitud}
                                        ${element.solicitud == 'Cambio de Nombre' ||
                                            element.solicitud == 'Cambio de Sucursal' ||
                                            element.solicitud == 'Cambio de Puesto' ?
                                                    '-' + element.puesto : ''
                                    }
                                </p>
                            </div>
                            <h6>
                                <i class="ti-time"></i>
                                ${element.usuario}
                            </h6>
                        </div>
                    </li>`;
        }

        function editSucursal() {
            swal({
                title: 'Modificación de Sucursal',
                html: `
                <div id="menuSuc">
                    <div class="form-group">
                        <select id="modSucursal" type="text" class="form-control select2" style="width: 100%" onchange="verfiicarPuesto('${idPuesto}','')">
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->idSucursal }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <select id="tipoTrans" class="form-control select2" style="width: 100%" onchange="activarBoton()">
                            <option value="d">Selecciona un tipo</option>
                            <option value="4">Transferencia</option>
                            <option value="5">Transferencia por desarrollo</option>
                            <option value="11">Movimiento interno del sistema</option>
                        </select>
                    </div>
                     <div class="form-group" id="puestoSuc" style="display: none;">
                        <select id="nuvPuesto" class="form-control select2" style="width: 100%;" onchange="verfiicarPuesto('0','')">
                            <option value="d" selected>Selecciona un tipo</option>
                           @foreach ($puestos as $puesto)
                                <option value="{{ $puesto->idPuesto }}">{{ $puesto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="alertSuc">
                    </div>
                </div>`,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                showCancelButton: true,
                buttonsStyling: false,
                allowOutsideClick: false,
                onBeforeOpen() {
                    swal.disableConfirmButton()
                    $('.select2').select2({
                        dropdownParent: $('.swal2-container')
                    })
                },
            }).then(function(result) {
                console.log(result)
                if (result.value) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('actualizaEmpleado') }}",
                        data: {
                            "idEmpleado": idEmpleado,
                            "dato": "3",
                            "valor": $("#modSucursal").val(),
                            "tipoVac": $('#tipoTrans').val(),
                            "idPuesto": $('#nuvPuesto').val(),
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(msg) {
                            obj = JSON.parse(msg);
                            if (obj.success) {
                                swal({
                                    type: 'success',
                                    title: 'Los datos han sido actualizados!'
                                }).then((value) => {
                                    location.reload()
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
            }).catch(swal.noop);
        }

        function editPuesto() {
            swal({
                title: 'Modificación de Puesto',
                html: `
                <div id="menuPuesto">
                <div class="form-group">
                    <select id="modPuesto" type="text" class="form-control select2" style="width: 100%" onchange="verfiicarPuesto('', '${idSucursal}')">
                    @foreach ($puestos as $puesto)
                        <option value="{{ $puesto->idPuesto }}">{{ $puesto->nombre }}</option>
                    @endforeach
                </select>
                </div>
                <div class="form-group">
                    <select id="tipoTrans" class="form-control select2" style="width: 100%" onchange="activarBoton()">
                        <option value="d">Selecciona un tipo</option>
                        <option value="10">Crecimiento en Sucursal</option>
                        <option value="11">Movimiento interno del sistema</option>
                    </select>
                </div>
                <div class="form-group text-left">
                    <input name="salario100" id="salario100" class="form-control border border-dark pl-1" style="background-image: none; border-color: #aaa !important; border-radius: 5px;" placeholder="Salario 100%"/>
                </div>
                <div id="alertSuc"></div>
                </div>`,
                showCancelButton: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                allowOutsideClick: false,
                onBeforeOpen() {
                    swal.disableConfirmButton()
                    $('.select2').select2({
                        dropdownParent: $('.swal2-container')
                    })
                },
            }).then(function(result) {
                if (result.value) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('actualizaEmpleado') }}",
                        data: {
                            "idEmpleado": idEmpleado,
                            "dato": "2",
                            "salario": $('#salario100').val(),
                            "valor": $("#modPuesto").val(),
                            "tipoVac": $('#tipoTrans').val(),
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(msg) {
                            obj = JSON.parse(msg);
                            if (obj.success) {
                                swal({
                                    type: 'success',
                                    title: 'Los datos han sido actualizados!'
                                }).then((value) => {
                                    location.reload()
                                });
                            } else {
                                swal({
                                    type: 'error',
                                    title: 'Oops...',
                                    text: 'Algo ha salido mal!',
                                    footer: 'Problemas? sit@prigo.com.mx	',
                                });
                            }
                        }
                    });
                }
            }).catch(swal.noop);
        }

        function verfiicarPuesto(idPuesto, idSucursal) {
            var tipoTrans = $('#tipoTrans').val()
            if (tipoTrans == 11) {
                swal.enableConfirmButton()
            } else {
                swal.disableConfirmButton()

                var menu = $('#alertSuc')
                if (idSucursal.length == 0) {
                    idSucursal = $('#modSucursal').val()
                }
                if (tipoTrans == 5) {
                    idPuesto = $('#nuvPuesto').val()
                }
                if (idPuesto.length == 0) {
                    idPuesto = $('#modPuesto').val()
                }

                menu.html(`
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            `);

                var params = {
                    idPuesto: idPuesto,
                    idSucursal: idSucursal,
                    _token: '{{ csrf_token() }}'
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('verificarPuestos') }}",
                    data: params,
                    success: function(msg) {
                        if (msg.success) {
                            menu.html(`
                        <div class="alert alert-success" role="alert">
                            ${msg.msg}
                        </div>
                        `)
                            disponible = msg.success;
                            activarBoton()
                        } else {
                            disponible = msg.success;
                            menu.html(`
                        <div class="alert alert-danger" role="alert">
                            ${msg.msg}
                        </div>
                        `)
                        }
                    }
                });
            }
        }

        function activarBoton() {
            var tipoTrans = $('#tipoTrans').val()

            if (tipoTrans != 11) {
                if (tipoTrans == 5 && $('#nuvPuesto').val() == 'd') {
                    $('#puestoSuc').show();
                    console.log('desactivar')
                    disponible = false;
                    var menu = $('#alertSuc')
                    menu.html('');
                } else if (tipoTrans == 5 && disponible && $('#nuvPuesto').val() != 'd') {
                    swal.enableConfirmButton()
                }
                if (tipoTrans != 'd' && disponible) {
                    swal.enableConfirmButton()
                } else {
                    swal.disableConfirmButton()
                }
            } else {
                swal.enableConfirmButton()
                var menu = $('#alertSuc')
                menu.html('')
            }
        }
    </script>
@endsection
