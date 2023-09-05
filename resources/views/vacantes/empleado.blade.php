@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'empleados'])
@section('content')
    <style>
        .font-weigth-bold {
            font-weight: bold;
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
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="display: inline">{{ $empleado->nombre }} - {{ $empleado->idEmpleado }}</h4>
                <a class="btn btn-link btn-just-icon" href="{{ route('formNewEmployee', [$empleado->idEmpleado]) }}"
                    style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                    <i class="material-icons">edit</i>
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Detalle</h4>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Puesto</p>
                        <i class="material-icons" style="display: block">work</i>
                        <p style="display: inline">{{ $empleado->puesto }}</p>
                        <button class="btn btn-link btn-just-icon" onclick="editPuesto()"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Sucursal</p>
                        <i class="material-icons" style="display: block">store</i>
                        <p style="display: inline">{{ $empleado->sucursal }}</p>
                        <button class="btn btn-link btn-just-icon"
                            onclick="editSucursal('{{ $empleado->idPuesto }}', '{{ $empleado->sucursal }}')"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Fecha de Nacimiento</p>
                        <i class="material-icons" style="display: block">event</i>
                        <p style="display: inline">{{ $empleado->fechaNacimiento }}</p>
                        <button class="btn btn-link btn-just-icon"
                            onclick="editFecha('{{ $empleado->fechaNacimiento }}', 5)"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Fecha de Ingreso</p>
                        <i class="material-icons" style="display: block">event_available</i>
                        <p style="display: inline">{{ $empleado->fechaIngreso }}</p>
                        <button class="btn btn-link btn-just-icon" onclick="editFecha('{{ $empleado->fechaIngreso }}', 6)"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Estado</p>
                        <i class="material-icons">info</i>
                        <p>{{ $empleado->estado }}</p>
                    </div>
                    <div class="col-md-2 text-center">
                        <p class="font-weigth-bold">Fecha de creacion</p>
                        <i class="material-icons">calendar_today</i>
                        <p>
                            @if ($empleado->fecha == '0000-00-00' || empty($empleado->fecha))
                                Plantilla
                            @else
                                {{ $empleado->fecha }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Historial</h4>
                        <ul class="timeline timeline-simple">
                            @foreach ($partidas as $partida)
                                <li class="timeline-inverted">
                                    <div
                                        class="timeline-badge @if ($partida->solicitud == 'Solicita contratacion') success @elseif($partida->solicitud == 'Baja Confirmada') danger @else info @endif">
                                        <i class="material-icons">
                                            @if ($partida->solicitud == 'Solicita contratacion')
                                                card_travel
                                            @elseif($partida->solicitud == 'Baja Confirmada')
                                                trash
                                            @else
                                                info
                                            @endif
                                        </i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <span class="label label-default">{{ $partida->fecha }}</span>
                                        </div>
                                        <div class="timeline-body">
                                            <p>{{ $partida->solicitud }} @if ($partida->solicitud != 'Contratacion')
                                                    - {{ $partida->puesto }}
                                                @endif
                                            </p>
                                        </div>
                                        <h6>
                                            <i class="ti-time"></i>
                                            {{ $partida->usuario }}
                                        </h6>
                                    </div>
                                </li>
                            @endforeach
                            <ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('jsimports')
    <script src="{{ asset('MaterialBS/js/plugins/bootstrap-selectpicker.js') }}"></script>
    <script src="{{ asset('MaterialBS/js/plugins/jquery.select-bootstrap.js') }}"></script>
    <script src="{{ asset('MaterialBS/js/plugins/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('MaterialBS/assets-for-demo/js/modernizr.js') }}"></script>
    <script src="{{ asset('MaterialBS/js/plugins/jquery.datatables.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endsection
@section('aditionalScripts')
    <script>
        var disponible = false;

        function editNombre() {
            swal({
                title: 'Modificación de Nombre',
                html: '<div class="form-group">' +
                    '<input id="modNombre" type="text" class="form-control" value="{{ $empleado->nombre }}" />' +
                    '</div>',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('actualizaEmpleado') }}",
                        data: {
                            "idEmpleado": {{ $empleado->idEmpleado }},
                            "dato": "1",
                            "valor": $("#modNombre").val(),
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(msg) {
                            console.log(msg);
                            obj = JSON.parse(msg);
                            if (obj.success) {
                                swal({
                                    type: 'success',
                                    title: 'Los datos han sido actualizados!'
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

        function editPuesto() {
            swal({
                title: 'Modificación de Puesto',
                html: `
                <div id="menuPuesto">
                <div class="form-group">
                    <select id="modPuesto" type="text" class="form-control select2" style="width: 100%" onchange="verfiicarPuesto('','{{ $empleado->idSucursal }}')">
                    @foreach ($puestos as $puesto)
                        <option value="{{ $puesto->idPuesto }}" @if ($puesto->idPuesto == $empleado->idPuesto) SELECTED @endif>{{ $puesto->nombre }}</option>
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
                            "idEmpleado": {{ $empleado->idEmpleado }},
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

        $(document).ready(function() {
            $('.select2').select2()
        })

        function editSucursal(puesto, sucursal) {
            swal({
                title: 'Modificación de Sucursal',
                html: `
                <div id="menuSuc">
                    <div class="form-group">
                        <select id="modSucursal" type="text" class="form-control select2" style="width: 100%" onchange="verfiicarPuesto('${puesto}','')">
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->idSucursal }}" @if ($sucursal->idSucursal == $empleado->idSucursal) SELECTED @endif>{{ $sucursal->nombre }}</option>
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
                            "idEmpleado": {{ $empleado->idEmpleado }},
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

        function editFecha(fecha, dato) {
            swal({
                title: 'Modificación de fecha',
                html: `<div class="form-group">
                        <input type="date" id="fecha" value="${fecha}"/>
                    </div>`,
                showCancelButton: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                allowOutsideClick: false,
                buttonsStyling: false
            }).then(function(result) {
                if (result.value) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('actualizaEmpleado') }}",
                        data: {
                            "idEmpleado": {{ $empleado->idEmpleado }},
                            "dato": dato,
                            "valor": $("#fecha").val(),
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(msg) {
                            obj = JSON.parse(msg);
                            if (obj.success) {
                                swal({
                                    type: 'success',
                                    title: 'Los datos han sido actualizados!'
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
                    console.log('activo')
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
