@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'empleados'])
@section('content')
    <style>
        .label-custom {
            z-index: 2;
            background-color: white;
        }
    </style>
    <div class="card">
        <div class="card-header">
            <h5>Empleado</h5>
        </div>
        <div class="card-body">
            <form id="formEmpelado" name="formEmpelado" class="form-row">
                <input type="hidden" value="{{ csrf_token() }}" name="_token">
                <input type="hidden" name="idEmpleado" id="idEmpleado"
                    value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->idEmpleado }}@endif">
                <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Razon social</label>
                    {{-- <input type="text" class="form-control" name="razSocial"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->razSocial }}@endif"> --}}
                    <select name="razSocial" id="razSocial" class="select2" style="width: 100%">
                        <option value="">Seleccione una Razon Social</option>
                        @foreach ($sociedad as $item)
                            @if (!empty($infoEmp[0]) && $item->idSociedad == $infoEmp[0]->idSociedad)
                                <option value="{{ $item->idSociedad }}" selected>{{ $item->sociedad }}</option>
                            @else
                                <option value="{{ $item->idSociedad }}">{{ $item->sociedad }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Departamento</label>
                    <select name="departamento" class="select2" style="width: 100%">
                        <option value="">Seleccione un departamento</option>
                        @foreach ($deptos as $item)
                            @if (!empty($infoEmp[0]) && $item->id == $infoEmp[0]->idDepartamento)
                                <option value="{{ $item->id }}" selected>{{ $item->nombre }}</option>
                            @else
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                @if (!empty($infoEmp[0]))
                    <div class="col-6 text-center">
                        <input type="hidden" id="sucursal" name="sucursal" value="{{ $infoEmp[0]->idSucursal }}">
                        <p class="font-weigth-bold">Sucursal</p>
                        <i class="material-icons" style="display: block">store</i>
                        <p style="display: inline">{{ $infoEmp[0]->sucursal }}</p>
                        <button class="btn btn-link btn-just-icon" type="button"
                            onclick="editSucursal('{{ $infoEmp[0]->idPuesto }}', '{{ $infoEmp[0]->idSucursal }}')"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                @else
                    <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                        <label class="bmd-label-static px-1 label-custom">Sucursal</label>
                        <select name="sucursal" id="sucursal" class="form-control select2" style="width: 100%">
                            <option disabled selected>Seleccione una sucursal</option>
                            @foreach ($sucursales as $item)
                                <option value="{{ $item->idSucursal }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if (!empty($infoEmp[0]))
                    <div class="col-6 text-center">
                        <input type="hidden" id="puesto" name="puesto" value="{{ $infoEmp[0]->idPuesto }}">
                        <p class="font-weigth-bold">Puesto</p>
                        <i class="material-icons" style="display: block">work</i>
                        <p style="display: inline">{{ $infoEmp[0]->puesto }}</p>
                        <button class="btn btn-link btn-just-icon" onclick="editPuesto()" type="button"
                            style="display: inline; background-color: transparent; color: #00bcd4; box-shadow: none; margin-top: 0px; margin-bottom: 0px; padding-left: 5px; padding-right: 5px;">
                            <i class="material-icons">edit</i>
                        </button>
                    </div>
                @else
                    <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                        <label class="bmd-label-static px-1 label-custom">Puesto</label>
                        <select id="puesto" name="puesto" class="form-control select2" style="width: 100%">
                            <option disabled selected>Seleccione un puesto</option>
                        </select>
                    </div>
                @endif
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Fecha de Ingreso</label>
                    <input type="date" class="form-control" name="fechaIngreso"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->fechaIngreso }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Numero de colaborador</label>
                    <input type="text" class="form-control" name="numColaborador"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->claveEKM }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="nombre"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->nombre }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Apellido Paterno</label>
                    <input type="text" class="form-control" name="apellidoPat"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->apellido_pat }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Apellido Materno</label>
                    <input type="text" class="form-control" name="apellidoMat"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->apellido_mat }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Sexo</label>
                    <select name="sexo" class="select2" style="width: 100%">
                        @foreach ($sexos as $item)
                            <option value="{{ $item->idSexo }}">{{ $item->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Estado Civil</label>
                    <select name="edoCivil" class="select2" style="width: 100%">
                        <option value="">Seleccione un estado civil</option>
                        @foreach ($edoCivil as $item)
                            @if (!empty($infoEmp[0]) && $infoEmp[0]->edoCivil == $item->idEdoCivil)
                                <option value="{{ $item->idEdoCivil }}" selected>{{ $item->nombre }}</option>
                            @else
                                <option value="{{ $item->idEdoCivil }}">{{ $item->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group text-left col-lg-6 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Nivel de estudios</label>
                    <select name="estudios" class="select2" style="width: 100%">
                        <option value="">Seleccione un nivel de estudios</option>
                        @foreach ($estudios as $item)
                            @if (!empty($infoEmp[0]) && $infoEmp[0]->estudios == $item->idNivEstudios)
                                <option value="{{ $item->idNivEstudios }}" selected>{{ $item->nombre }}</option>
                            @else
                                <option value="{{ $item->idNivEstudios }}">{{ $item->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Fecha de Nacimiento</label>
                    <input type="date" class="form-control" name="fechaNacimiento"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->fechaNacimiento }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Correo</label>
                    <input type="text" class="form-control" name="correo"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->correo }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>NSS</label>
                    <input type="text" class="form-control" name="nss"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->nss }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>RFC</label>
                    <input type="text" class="form-control" name="rfc"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->rfc }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>CURP</label>
                    <input type="text" class="form-control" name="curp"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->curp }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Telefono Fijo</label>
                    <input type="text" class="form-control" name="telFijo"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->telFijo }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Celular</label>
                    <input type="text" class="form-control" name="celular"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->celular }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Calle</label>
                    <input type="text" class="form-control" name="calle"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->calle }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-6">
                    <label>Num Ext</label>
                    <input type="text" class="form-control" name="numExt"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->numExt }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-6">
                    <label>Num Int</label>
                    <input type="text" class="form-control" name="numInt"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->numInt }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Colonia</label>
                    <input type="text" class="form-control" name="colonia"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->Colonia }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Municipio o Alcaldia</label>
                    <input type="text" class="form-control" name="munOAlc"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->munOAlc }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>C.P.</label>
                    <input type="text" class="form-control" name="cp"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->cp }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Estado</label>
                    <input type="text" class="form-control" name="estado"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->estadoDir }}@endif">
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Credito Infonavit</label>
                    <input type="text" class="form-control" name="credInfo"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->credInfo }}@endif">
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Salario 100%</label>
                    <div class="d-flex">
                        <div class="col-auto px-1" style="align-self: center;">
                            $
                        </div>
                        <input type="password" class="form-control" name="salario100"
                            value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->salario100 }}@endif">
                        <div class="col-auto px-1" style="align-self: center;">
                            <label for="check1">
                                <i class="material-icons">visibility</i>
                            </label>
                            <input type="checkbox" name="check1" id="check1" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Salario 10%</label>
                    <div class="d-flex">
                        <div class="col-auto px-1" style="align-self: center;">
                            $
                        </div>
                        <input type="password" class="form-control" name="salario10"
                            value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->salario10 }}@endif">
                        <div class="col-auto px-1" style="align-self: center;">
                            <label for="check3">
                                <i class="material-icons">visibility</i>
                            </label>
                            <input type="checkbox" name="check3" id="check3" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="form-group text-left col-lg-3 col-md-12">
                    <label>Salario 90%</label>
                    <div class="d-flex">
                        <div class="col-auto px-1" style="align-self: center;">
                            $
                        </div>
                        <input type="password" class="form-control" name="salario90"
                            value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->salario90 }}@endif">
                        <div class="col-auto px-1" style="align-self: center;">
                            <label for="check2">
                                <i class="material-icons">visibility</i>
                            </label>
                            <input type="checkbox" name="check2" id="check2" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="form-group text-left col-lg-3 col-md-12 bmd-form-group">
                    <label class="bmd-label-static px-1 label-custom">Forma de Pago (Banco)</label>
                    <select name="formPago" class="select2 form-control" style="width: 100%">
                        <option value="">Seleccione un banco</option>
                        @foreach ($medios as $item)
                            @if (!empty($infoEmp[0]) && $infoEmp[0]->formPago == $item->idMedio)
                                <option value="{{ $item->idMedio }}" selected>{{ $item->nombre }}</option>
                            @else
                                <option value="{{ $item->idMedio }}">{{ $item->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group text-left col-lg-6 col-md-12">
                    <label>Numero Tarjeta - Clabe Interbancaria</label>
                    <input type="text" class="form-control" name="numTarjeta"
                        value="@if (!empty($infoEmp[0])){{ $infoEmp[0]->numTarjeta }}@endif">
                </div>
            </form>
        </div>
        <div class="card-footer">
            <button class="btn btn-success" id="nuevoEmpleado">
                Guardar
            </button>
        </div>
    </div>
@endsection
@section('jsimports')
    <script src="{{ asset('MaterialBS/js/plugins/bootstrap-selectpicker.js') }}"></script>
    <script src="{{ asset('MaterialBS/assets-for-demo/js/modernizr.js') }}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endsection
@section('aditionalScripts')
    <script type="text/javascript">
        var disponible = false;

        var sucursal =
            '@if (!empty($infoEmp[0])){{ $infoEmp[0]->idSucursal }}@endif';
        var puesto =
            '@if (!empty($infoEmp[0])){{ $infoEmp[0]->idPuesto }}@endif';

        $(document).ready(function() {
            if (sucursal.length > 0) {
                // getPuestos();
            }
            $('.select2').select2();
        })

        $('#sucursal').on('change', function() {
            getPuestos();
        })

        $('#check1').on('change', function() {
            hideOrShow(this, '[name=salario100]')
        })
        $('#check2').on('change', function() {
            hideOrShow(this, '[name=salario90]')
        })
        $('#check3').on('change', function() {
            hideOrShow(this, '[name=salario10]')
        })

        function hideOrShow(checkElement, input) {
            var check = $(checkElement).prop('checked')
            if (check) {
                $(input).prop('type', 'text')
                $(checkElement).prev().children().first().html('visibility_off')
            } else {
                $(input).prop('type', 'password')
                $(checkElement).prev().children().first().html('visibility')
            }
        }

        $('#nuevoEmpleado').on('click', function() {
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
                            $('.select2').trigger('change.select2')
                            swal({
                                type: 'success',
                                title: msg.msg
                            }).then((value) => {
                                if (sucursal.length > 0) {
                                    window.location.href =
                                        `{{ route('detalleempleado') }}/${$('#idEmpleado').val()}`;
                                } else {
                                    form.reset()
                                }
                                $('.select2').trigger('change.select2')
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
                            if (sucursal > 0 && puesto == element.idPuesto) {
                                var html = '<option value=":id">:nombre</option>'
                            } else {
                                var html = '<option value=":id">:nombre</option>'
                            }
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

        function editPuesto() {
            swal({
                title: 'Modificación de Puesto',
                html: `
                <div id="menuPuesto">
                <div class="form-group">
                    <select id="modPuesto" type="text" class="form-control select2" style="width: 100%" onchange="verfiicarPuesto('','@if (!empty($infoEmp[0])){{ $infoEmp[0]->idSucursal }}@endif')">
                    @foreach ($puestos as $puesto)
                    @if (!empty($infoEmp[0])) 
                    <option value="{{ $puesto->idPuesto }}" @if ($puesto->idPuesto == $infoEmp[0]->idPuesto) SELECTED @endif>{{ $puesto->nombre }}</option>
                    @else 
                    <option value="{{ $puesto->idPuesto }}">{{ $puesto->nombre }}</option>
                    @endif
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
                            "idEmpleado": '@if (!empty($infoEmp[0])){{ $infoEmp[0]->idEmpleado }}@endif',
                            "dato": "2",
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
                                }).then(function(result) {
                                    if (result.value) {
                                        location.reload();
                                    }
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
                        @if (!empty($infoEmp[0])) 
                        <option value="{{ $sucursal->idSucursal }}" @if ($sucursal->idSucursal == $infoEmp[0]->idSucursal) SELECTED @endif>{{ $sucursal->nombre }}</option>
                        @else
                        <option value="{{ $sucursal->idSucursal }}">{{ $sucursal->nombre }}</option>
                        @endif
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
                            "idEmpleado": '@if (!empty($infoEmp[0])){{ $infoEmp[0]->idEmpleado }}@endif',
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
                                }).then(function(result) {
                                    if (result.value) {
                                        location.reload();
                                    }
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
            swal.disableConfirmButton()

            var menu = $('#alertSuc')
            if (idSucursal.length == 0) {
                idSucursal = $('#modSucursal').val()
            }
            var tipoTrans = $('#tipoTrans').val()
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
                    } else {
                        disponible = msg.success;
                        if (tipoTrans != 11) {
                            menu.html(`
                            <div class="alert alert-danger" role="alert">
                                ${msg.msg}
                                </div>
                                `)
                        }
                    }
                }
            });
            activarBoton()
        }

        function activarBoton() {
            var tipoTrans = $('#tipoTrans').val()
            $('#puestoSuc').hide();
            if (tipoTrans == 11) {
                swal.enableConfirmButton()
            } else {
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
            }
        }
    </script>
@endsection
