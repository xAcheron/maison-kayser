@extends('layouts.app')
@include('menu.vacantes', ['seccion' => 'nuevavacante'])
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header card-header-orange card-header-text">
                    <div class="card-text">
                        <h4 class="card-title">Nueva vacante</h4>
                        <input type="hidden" value="0" id="artId" />
                        <input type="hidden" value="" id="artCod" />
                        <input type="hidden" value="" id="artName" />
                        <input type="hidden" value="" id="artUnit" />
                        <input type="hidden" value="1" id="artConv" />
                        <input type="hidden" value="" id="artUnitFood" />
                    </div>
                </div>
                <div class="card-body ">
                    @if (!empty($items))
                        <div class="alert alert-success" role="alert">
                            Se agregaron correctamente {{ $nitems }} articulos del archivo excel
                        </div>
                    @endif
                    @if (!empty($noitems))
                        <div class="alert alert-danger" role="alert">
                            No se pudieron agregar {{ $nnoitems }} articulos del archivo excel
                        </div>
                    @endif
                    <form method="get" action="/" class="form-horizontal" id="formVacante">
                        <input type="hidden" value="" id="idPuesto">
                        <div class="row">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <label class="col-sm-2 col-form-label">Acci&oacute;n</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select id="accion" name="accion" onchange="validaAccion()" class="select2"
                                        style="width: 100%" data-style="btn select-with-transition"
                                        title="Seleccione una accion" data-size="7" tabindex="-98">
                                        <option value="d" selected disabled>Seleccione una accion</option>
                                        <option value="6">Baja</option>
                                        <option value="3">Solicitar Posicion Adicional</option>
                                        <option value="4">Solicitar Transferencia entre sucursales</option>
                                        <option value="5">Solicitar Transferencia por Desarrollo</option>
                                        <option value="10">Crecimiento en misma sucursal</option>
                                    </select>
                                </div>
                            </div>
                            <label class="col-sm-2 col-form-label">Sucursal/Oficina</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select onchange="getEmployees(1)" id="idSucursal" name="sucursal" class="select2"
                                        style="width: 100%" data-style="btn select-with-transition"
                                        title="Seleccione una sucursal" data-size="7" tabindex="-98">
                                        <option value="d" selected disabled>Seleccione una sucursal</option>
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->idSucursal }}">{{ $sucursal->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- <label class="col-sm-2 col-form-label">Departamento</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select id="departamento" name="departamento" class="selectpicker"
                                        data-style="btn select-with-transition" title="Seleccione un departamento"
                                        data-size="7" tabindex="-98">
                                        <option>Seleccione departamento</option>
                                    </select>
                                </div>
                            </div>
                            <label class="col-sm-2 col-form-label">Area</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select onchange="getPuestos(1)" id="area" name="area" class="selectpicker"
                                        data-style="btn select-with-transition" title="Seleccione area" data-size="7"
                                        tabindex="-98">
                                        <option>Seleccione area</option>
                                    </select>
                                </div>
                            </div>
                            <label class="col-sm-2 col-form-label">Puesto</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select onchange="validaPuesto()" id="puesto" name="puesto" class="selectpicker"
                                        data-style="btn select-with-transition" title="Seleccione un puesto" data-size="7"
                                        tabindex="-98">
                                        <option value="0">Seleccione un puesto</option>
                                    </select>
                                </div>
                            </div> --}}
                            <label id="lblEmpleado" display="none" class="col-sm-2 col-form-label">Empleado</label>
                            <div id="divEmpleado" display="none" class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <input type="hidden" value="0" id="disponibles" name="disponibles">
                                    <select id="empleado" name="empleado" class="select2" style="width: 100%"
                                        data-style="btn select-with-transition" title="Seleccione un empleado"
                                        data-size="7" tabindex="-98">
                                        <option value="0">No Aplica</option>
                                    </select>
                                </div>
                            </div>
                            <label id="lblTransSuc" class="col-sm-2 col-form-label">Sucursal Destino</label>
                            <div id="divtransSuc" class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select id="transucId" name="transucId" onchange="validaPuestoSuc(this)"
                                        class="select2" data-style="btn select-with-transition" style="width: 100%"
                                        title="Seleccione una sucursal" data-size="7" tabindex="-99">
                                        <option value="d" selected>Seleccione una sucursal</option>
                                        @foreach ($trsucursales as $trsucursal)
                                            <option value="{{ $trsucursal->idSucursal }}">{{ $trsucursal->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <label id="lblnvoPuesto" class="col-sm-2 col-form-label">Nuevo Puesto</label>
                            <div id="divnvoPuesto" class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <select onchange="validaPuestoCrece()" id="nvoPuesto" name="nvoPuesto1"
                                        class="select2" data-style="btn select-with-transition" style="width: 100%"
                                        title="Seleccione un puesto" data-size="7" tabindex="-99">
                                        <option value="0" selected>Seleccione un puesto</option>
                                    </select>
                                </div>
                            </div>
                            {{-- <label id="lblEmpleadoBaja" display="none" class="col-sm-2 col-form-label">Empleado</label>
                            <div id="divEmpleadoBaja" display="none" class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <input type="hidden" value="0" id="disponibles" name="disponibles">
                                    <select id="empleadoBaja" name="empleadoBaja" class="selectpicker"
                                        data-style="btn select-with-transition" title="Seleccione un empleado"
                                        data-size="7" tabindex="-98">
                                        <option value="0">No Aplica</option>
                                    </select>
                                </div>
                            </div> --}}
                        </div>
                        <div class="row">
                            <div id="divMessages" class="col-sm-12">

                            </div>
                            <div class="col-sm-12 table-wrapper-2">
                                <div class="col-sm-4" style="display: inline-block;vertical-align: middle;float: none;">
                                    <button type="button" id="addArtBtn" class="btn btn-info btn-round" disabled><i
                                            class="material-icons">playlist_add</i> Agregar</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 table-wrapper-2">
                                <table id="tblReq" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class='col-xs-2'>Sucursal</th>
                                            <th class='col-xs-2'>Sucursal Destino</th>
                                            <th class="col-xs-2">Empleado</th>
                                            <th class="col-xs-2">Nuevo Puesto</th>
                                            <th class='col-xs-3'>Solicitud</th>
                                            <th class='col-xs-1'>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label">Comentarios</label>
                            <div class="col-sm-10">
                                <div class="form-group bmd-form-group">
                                    <textarea name="comentario" class="form-control"></textarea>
                                    <span class="bmd-help">Detalles o excepciones de la contratación</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="form-group bmd-form-group">
                                    <button id="sendRequest" type="button" class="btn btn-info btn-round">Guardar
                                        Solicitud</button>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                &nbsp;
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modals -->
    <div class="modal fade" id="xlsModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('upPedido') }}" class="form-horizontal"
                    enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Carga de archivo Excel</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h4>Seleccione el archivo a cargar:</h4>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="file" name="xlsPedido">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <!--button type="button" class="btn btn-primary">Cargar archivo</button-->
                        <input type="submit" id="upXlsBtn" class="btn btn-info btn-round" value="Cargar Xls">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modals -->
@endsection
@section('aditionalScripts')
    <style>
        .table-wrapper-2 {
            display: block;
            max-height: 300px;
            overflow-y: auto;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <!--script src="{{ asset('js/plugin/waitingfor.jquery.js') }}"></script-->
    <script>
        $("#lblTransSuc").hide();
        $("#divtransSuc").hide();
        $("#lblEmpleado").hide();
        $("#divEmpleado").hide();
        $("#lblnvoPuesto").hide();
        $("#divnvoPuesto").hide();
        $("#lblEmpleadoBaja").hide();
        $("#divEmpleadoBaja").hide();

        $('.select2').select2()
        var partidas = 0;

        $('#empleado').on('change', function() {
            var idPuesto = $(`#empleado :selected`).data('idpuesto');
            $('#idPuesto').val(idPuesto)
            
            if($('#accion').val() == 6){
                $('#addArtBtn').prop('disabled', false)
            }
        })

        // function getDeptos(tipo) {
        //     $("#divMessages").empty();
        //     $("#empleado").empty();
        //     $("#empleado").selectpicker('refresh');
        //     $.ajax({
        //         type: "POST",
        //         url: "{{ route('getdeptos') }}",
        //         data: {
        //             "id": $("#idSucursal").val(),
        //             "_token": "{{ csrf_token() }}"
        //         },
        //         success: function(msg) {
        //             getPuestos(2);
        //             var len = msg.data.length;
        //             var l = 0;

        //             if (len > 0) {
        //                 $("#departamento").empty();

        //                 while (l < len) {
        //                     $("#departamento").append("<option value='" + msg.data[l].id + "'>" + msg.data[l]
        //                         .nombre + "</option>");
        //                     l++;
        //                 }
        //                 $("#departamento").selectpicker('refresh');
        //             }

        //             len = msg.areas.length;
        //             l = 0;

        //             if (len > 0) {
        //                 $("#area").empty();

        //                 while (l < len) {
        //                     $("#area").append("<option value='" + msg.areas[l].id + "'>" + msg.areas[l].nombre +
        //                         "</option>");
        //                     l++;
        //                 }
        //                 $("#area").selectpicker('refresh');
        //             }
        //         },
        //         error: function() {
        //             swal({
        //                 type: 'error',
        //                 title: 'Oops...',
        //                 text: 'Algo ha salido mal!',
        //                 footer: 'Problemas? sit@prigo.com.mx	',
        //             });
        //             $('.button').prop('disabled', false);
        //         }
        //     });
        // }

        function getEmployees() {

            $.ajax({
                type: "POST",
                url: "{{ route('getEmpleados') }}",
                data: {
                    "RHRole": "{{ $role }}",
                    "columns": [{
                        "search": {
                            "value": $("#idSucursal :selected").html()
                        },
                        "data": "sucursal",
                    }, ],
                    "length": "100",
                    "_token": "{{ csrf_token() }}"
                },
                success: function(msg) {

                    var mensaje = '';

                    $("#divMessages").empty();
                    $("#empleado").empty();

                    var data = msg.data;

                    $("#empleado").append(
                        "<option selected value='0' selected disabled>Seleccione un empleado</option>");

                    data.forEach(e => {
                        $("#empleado").append(`<option value='${e.idEmpleado}' data-idPuesto='${e.idPuesto}'>
                            ${e.nombre}</option>`);
                    });

                },
                error: function() {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Algo ha salido mal!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                    $('.button').prop('disabled', false);
                }
            });

            if ($('#accion').val() == 10) {
                getPuestos(4)
            }

            if($('#accion').val() == 3){
                getPuestos(5)
            }
        }

        function getPuestos(tipo) {
            urlpuesto = "";
            ctrPuesto = "";
            data = {};
            $("#divMessages").empty();
            if (tipo == 2) {
                $("#nvoPuesto").empty();
                $("#nvoPuesto").selectpicker('refresh');
                urlpuesto = "{{ route('getpuestoscrece') }}";
                ctrPuesto = "nvoPuesto";
                data = {
                    "sucursal": $("#idSucursal").val(),
                    "puesto": $("#puesto").val(),
                    "_token": "{{ csrf_token() }}"
                }
            } else if (tipo == 3) {
                $("#empleadoBaja").empty();
                $("#empleadoBaja").selectpicker('refresh');
                urlpuesto = "{{ route('getPuestos') }}";
                ctrPuesto = "nvoPuesto";
                data = {
                    "idSucursal": $("#transucId").val(),
                    "id": $("#area").val(),
                    "_token": "{{ csrf_token() }}"
                };
            } else if(tipo == 4){
                $("#empleado").empty();
                $("#empleado").selectpicker('refresh');
                urlpuesto = "{{ route('getPuestos') }}";
                ctrPuesto = "nvoPuesto";
                data = {
                    "idSucursal": $("#idSucursal").val(),
                    "id": $("#area").val(),
                    "_token": "{{ csrf_token() }}"
                };
            }else{
                $("#empleado").empty();
                $("#empleado").selectpicker('refresh');
                urlpuesto = "{{ route('getPuestos') }}";
                ctrPuesto = "nvoPuesto";
                data = {
                    "idSucursal": $("#idSucursal").val(),
                    "id": $("#area").val(),
                    "_token": "{{ csrf_token() }}"
                };
            }

            $.ajax({
                type: "POST",
                url: urlpuesto,
                data: data,
                success: function(msg) {

                    var data = msg.data;

                    $("#" + ctrPuesto).empty();
                    $("#" + ctrPuesto).selectpicker('refresh');
                    $("#divMessages").empty();
                    if (tipo == 1) {
                        $("#empleado").empty();
                        $("#empleado").selectpicker('refresh');
                    }
                    console.log(data)

                    if (data != undefined) {
                        data.forEach(e => {
                            $("#" + ctrPuesto).append("<option value='" + e.idPuesto + "'>" + e.puesto +
                                "</option>");
                        });
                    }

                    // console.log(msg)
                    // var len = msg.data.length;
                    // var l = 0;
                    // if (len > 0) {
                    //     while (l < len) {
                    //         $("#" + ctrPuesto).append("<option value='" + msg.data[l].idPuesto + "'>" + msg
                    //             .data[l]
                    //             .puesto + "</option>");
                    //         l++;
                    //     }
                    //     $("#" + ctrPuesto).selectpicker('refresh');
                    // }
                },
                error: function() {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Algo ha salido mal!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                    $('.button').prop('disabled', false);
                }
            });
        }

        function validaAccion() {
            if ($("#accion").val()) {
                $('#idSucursal').val('d');
                $('#idSucursal').trigger('change.select2');
                $("#empleado").empty();
                $('#transucId').val('d');
                $('#transucId').trigger('change.select2');
                $('#nvoPuesto').val('0');
                $('#nvoPuesto').trigger('change.select2');
                if ($("#accion").val() == 2 || $("#accion").val() == 4 || $("#accion").val() == 5 || $("#accion").val() ==
                    6 || $("#accion").val() == 10) {
                    $("#lblEmpleado").show();
                    $("#divEmpleado").show();
                    if ($("#accion").val() == 5) {
                        $("#lblTransSuc").show();
                        $("#divtransSuc").show();
                        $("#lblnvoPuesto").show();
                        $("#divnvoPuesto").show();
                    } else if ($("#accion").val() == 10) {
                        $("#lblnvoPuesto").show();
                        $("#divnvoPuesto").show();
                        $("#lblTransSuc").hide();
                        $("#divtransSuc").hide();
                    } else if ($("#accion").val() == 4) {
                        $("#lblTransSuc").show();
                        $("#divtransSuc").show();
                        $("#lblnvoPuesto").hide();
                        $("#divnvoPuesto").hide();
                    } else if ($('#accion').val() == 6) {
                        $("#lblnvoPuesto").hide();
                        $("#divnvoPuesto").hide();
                    }
                } else if ($('#accion').val() == 3) {
                    $("#lblnvoPuesto").show();
                    $("#divnvoPuesto").show();
                    $("#lblTransSuc").hide();
                    $("#divtransSuc").hide();
                    $("#lblEmpleado").hide();
                    $("#divEmpleado").hide();
                    
                } else {
                    $("#lblnvoPuesto").hide();
                    $("#divnvoPuesto").hide();
                    $("#lblTransSuc").hide();
                    $("#divtransSuc").hide();
                    $("#lblEmpleado").hide();
                    $("#divEmpleado").hide();
                }
            }
        }

        function validaPuestoSuc(sucursal) {
            var accion = $('#accion').val();
            if (accion == 5) {
                getPuestos(3);
            } else if (accion == 4) {
                var puesto = $('#idPuesto').val();
                validaPuestoCrece(puesto);
            }
        }

        function validaPuestoCrece(puesto) {

            if (puesto == undefined) {
                puesto = $("#nvoPuesto").val()
            }

            var sucursal = $('#transucId').val()
            if (sucursal == 'd') {
                sucursal = $('#idSucursal').val()
            }

            console.log(sucursal)

            if (sucursal != 'd' && sucursal != null) {

                $("#divMessages").empty();

                $.ajax({
                    type: "POST",
                    url: "{{ route('verificarPuestos') }}",
                    data: {
                        "idSucursal": sucursal,
                        "idPuesto": puesto,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(msg) {

                        if($('#accion').val() != 3){   
                            $('#addArtBtn').prop('disabled', !msg.success)
                            
                            var mensaje =
                            `<div class="alert ${msg.success ? 'alert-success' : 'alert-danger'}" role="alert">${msg.msg}</div>`;
                            
                            $("#divMessages").append(mensaje);
                        }else{
                            $('#addArtBtn').prop('disabled', false)
                        }


                    },
                    error: function() {
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: 'Algo ha salido mal!',
                            footer: 'Problemas? sit@prigo.com.mx',
                        });
                        $('.button').prop('disabled', false);
                    }
                });
            }
        }

        function validaPuesto() {
            if ($("#puesto").val()) {
                $("#divMessages").empty();
                $("#empleado").empty();
                $("#empleado").selectpicker('refresh');

                if ($('#accion').val() == 1 || $('#accion').val() == 2 || $('#accion').val() == 3) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('validapuesto') }}",
                        data: {
                            "idSucursal": $("#idSucursal").val(),
                            "idArea": $("#area").val(),
                            "idPuesto": $("#puesto").val(),
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(msg) {

                            var mensaje = '';

                            $("#divMessages").empty();
                            $("#empleado").empty();
                            $("#empleado").selectpicker('refresh');

                            if (msg.disponibles > 0) {
                                mensaje = '<div class="alert alert-success" role="alert">' + msg.disponibles +
                                    ' vacantes disponibles ' + (msg.solicitudes > 0 ? msg.solicitudes +
                                        ' Solicitudes Pendientes' : '') + '</div>';
                                $("#disponibles").val(msg.disponibles);
                            } else {
                                mensaje =
                                    '<div class="alert alert-danger" role="alert">Sin vacantes disponibles</div>';
                                $("#disponibles").val("0");
                            }

                            $("#divMessages").append(mensaje);

                            var len = msg.empleados.length;
                            var l = 0;

                            if (len > 0) {
                                // $("#empleado").append(
                                //     "<option selected value='0'>Seleccione un empleado</option>");

                                while (l < len) {
                                    $("#empleado").append("<option value='" + msg.empleados[l].id + "'>" + msg
                                        .empleados[l].nombre + "</option>");
                                    l++;
                                }
                                $("#empleado").selectpicker('refresh');
                            }

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
            }
        }
        /*

        function formatRepoSelection (repo) {
        	$("#artId").val(repo.id);	
        	$("#artCod").val(repo.cod);	
        	$("#artName").val(repo.name);
        	$("#artUnit").val(repo.unit);
        	$("#artConv").val(repo.conv);
        	$("#artUnitFood").val(repo.unitfood);
        	return repo.name || repo.id;
        }
        /*
        function isNumberKey(evt)
        {
        	var charCode = (evt.which) ? evt.which : evt.keyCode;
        	if (charCode != 46 && charCode > 31 
        	&& (charCode < 48 || charCode > 57))
        	return false;
        	return true;
        } */

        $('#addArtBtn').click(function() {

            if ($("#puesto").val() != 0 && $("#idSucursal").val() != 0 && $("#area").val() != 0 && $(
                    "#departamento").val() != 0) {
                if ((($("#accion").val() == 2 || $("#accion").val() == 4 || $("#accion").val() == 5 || $("#accion")
                        .val() == 10) && ($("#empleado").val() == 0 || $("#empleado").val() == "0"))) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Seleccione un empleado a reemplazar/trasladar/promover!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                } else if ($("#disponibles").val() == 0 && ($("#accion").val() == 1)) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'No hay vacantes disponibles, seleccione reemplazar o solicitar vacante!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                } else if ($("#transucId").val() == 0 && ($("#accion").val() == 4 || $("#accion").val() == 5)) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Seleccione una sucursal para realizar una transferencia!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                } else if ($("#nvoPuesto").val() == 0 && ($("#accion").val() ==
                        10)) {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Seleccione un puesto para promover y un empleado a reemplazar!',
                        footer: 'Problemas? sit@prigo.com.mx',
                    });
                } else {

                   
                    partidas += 1;
                    var id = $("#puesto").val();
                    var idSucursal = $("#idSucursal").val();
                    var sucursal = $("#idSucursal option:selected").text();
                    var idArea = $("#area").val();
                    var idDepto = $("#departamento").val();
                    var idEmpleado = $("#empleado").val();
                    var idAccion = $("#accion").val();
                    var accion = $("#accion option:selected").text();
                    var transucId = $("#transucId").val() == 'd' ? 0 : $("#transucId").val();
                    var nvoPuesto = $("#nvoPuesto").val();
                    var empleadoBaja = $("#empleadoBaja").val();
                    var sucursalDes = $("#transucId").val() != 'd' ? $('#transucId option:selected').text() : ''    ;
                    var empleado = $("#empleado").val() != null ? $('#empleado option:selected').text() : '';
                    var puesto = $("#nvoPuesto").val() != 0 ? $('#nvoPuesto option:selected').text() : '';

                    $('#tblReq tbody').append(`
                    <tr id='trVac${partidas}' class='item-row form-group'>
                        <td class='col-xs-2'>
                            <input type='hidden' class='item-added' value='${partidas}' name='numPartida[]'>
                            <input type='hidden' class='item-added' value='${idSucursal}' name='idSucursal[]'>
                            ${sucursal}
                        </td>
                        <td class='col-xs-2'>
                            ${sucursalDes}
                        </td>
                        <td class='col-xs-2'>
                            ${empleado}
                        </td>
                        <td class='col-xs-2'>
                            ${puesto}
                        </td>
                        <td class='col-xs-3'>
                            <input type='hidden' name='empleado[]' value='${idEmpleado}'/>
                            <input type='hidden' name='accion[]' value='${idAccion}'/>
                            <input type='hidden' class='item-added' value='${nvoPuesto}' name='nvoPuesto[]'>
                            <input type='hidden' class='item-added' value='${empleadoBaja}' name='empleadoBaja[]'>
                            <input type='hidden' class='item-added' value='${transucId}' name='transucId[]'>
                            ${accion}
                        </td>
                        <td class='td-actions col-xs-1'>
                            <button type='button' rel='tooltip' data-placement='left' title='' class='btn btn-link remove-btn' data-original-title='Remove item'>
                                <i class='material-icons'>close</i>
                            </button>
                        </td>
                    </tr>`);

                    $(".remove-btn").click(function() {
                        $(this).parents(".item-row").remove();
                    });

                     $('#addArtBtn').prop('disabled', true);
                    $('#formVacante')[0].reset();
                    $('.select2').trigger('change.select2');

                }
            } else {
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Seleccione un articulo de la lista!',
                    footer: 'Problemas? sit@prigo.com.mx',
                });
            }
        });
        $("#sendRequest").click(function() {
            if ($('.item-added').length > 0) {
                swal({
                    title: "Estas segur@?",
                    text: "La solicitud sera enviada directamente al area de reclutamiento!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    allowOutsideClick: false,
                    confirmButtonText: 'Si, enviar solicitud!',
                    cancelButtonText: 'No, cancelar!'
                }).then((result) => {
                    if (result) {
                        $('.button').prop('disabled', true);
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
                            url: "{{ route('guardasolicitud') }}",
                            data: $('form.form-horizontal').serialize(),
                            success: function(msg) {
                                $('#tblReq tbody').empty();
                                $('form.form-horizontal')[0].reset();
                                swal({
                                    type: 'success',
                                    title: 'Tu solicitud a quedado registrada!'
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
            } else {
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Agregue al menos un elemento a la lista!',
                    footer: 'Problemas? sit@prigo.com.mx',
                });
            }
        });
    </script>
@endsection
