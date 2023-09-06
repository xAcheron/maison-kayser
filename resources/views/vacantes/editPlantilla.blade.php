@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'plantilla'])
@section('content')
    <div class="row justify-content-center" style="margin-bottom: 10px;">
        <div id="dashop-panel-5" class="col-md-8">
            <div class="card">
                <div class="card-header card-header-success card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons"></i>
                    </div>
                    <h4 class="card-title">Plantilla PRIGO - {{ $sucursal->nombre }}</h4>
                </div>
                <div class="card-body ">
                    <form action="" class="" id="plantilla">
                        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="idSucursal" id="idSucursal" value="{{ $sucursal->id }}">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <th>
                                        Puesto
                                    </th>
                                    <th>
                                        Cantidad Autorizados
                                    </th>
                                    <th>
                                        Activos
                                    </th>
                                    <th>
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @foreach ($puestosActivos as $item)
                                    <tr id="item-{{ $item->idPlaza }}">
                                        <td class="text-left">
                                            <label>{{ $item->puesto }}</label>
                                        </td>
                                        <td class="text-left">
                                            <input type="number" class="form-control" name="cantidad[]"
                                                value="{{ $item->cantidad }}"
                                                onblur="numeroMinimo('{{ $item->Activos }}', '{{ $item->idPlaza }}', '{{ $item->cantidad }}')"
                                                onchange="" id="input-{{ $item->idPlaza }}">
                                            <input type="hidden" name="puestosIds[]" value="{{ $item->idPlaza }}">
                                            <input type="hidden" name="puestos[]" value="{{ $item->idPuesto }}">
                                        </td>
                                        <td>
                                            {{ $item->Activos }} / {{ $item->cantidad }}
                                        </td>
                                        <td>
                                            <a class="btn btn-just-icon btn-danger text-white"><i class="material-icons"
                                                    onclick="borrarPuesto('{{ $item->idPlaza }}', '{{ $item->Activos }}')">delete</i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" id="guardarBtn" class="btn btn-success">Guardar</button>
                        <button type="button" id="agregar" class="btn btn-info" data-toggle="modal"
                            data-target="#puestosModal">Agregar puesto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="puestosModal" tabindex="-1" aria-labelledby="puestosModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="puestosModalLabel">Añadir Puesto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <select name="puesto" id="puesto" class="select2" style="width: 100%">
                            @foreach ($puestos as $item)
                                <option value="{{ $item->idPuesto }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Cantidad Autorizados</label>
                        <input type="number" id="cantidadModal" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="nuevoPuesto">Añadir</button>
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
        $('.select2').select2({
            templateResult: formatState,
            dropdownParent: $('#puestosModal')
        });

        function formatState(state) {
            if (state.disabled == false && (state.id > 0 || state.id != "")) {
                if (state.element.attributes['data-type'] != null && state.element.attributes['data-type'] != undefined) {
                    tipo = state.element.attributes['data-type'].value
                    return $('<span class="' + (tipo == 2 ? "ml-2" : "font-weight-bold") + '">' + state.text + '</span>');
                } else {
                    return state.text;
                }
            } else {
                return state.text;
            }

        }

        function numeroMinimo(minimo, idPlaza, cantidad) {
            let v = parseInt($(`#input-${idPlaza}`).val());
            if (v < minimo && v != cantidad) {
                $(`#input-${idPlaza}`).val(cantidad);
                swal({
                    title: '',
                    html: `Actualmente tienes ${minimo} plazas ocupadas, para poder reducir el numero de plazas primero debes mover a los empleados a la sucursal correspondiente`,
                    type: 'warning',
                }).then((value) => {
                    
                })
            }
        }

        $('#guardarBtn').on('click', function() {
            var form = $('#plantilla')[0];
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
                url: "{{ route('actualizarPlantilla') }}",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                success: function(msg) {
                    if (msg.success) {
                        swal({
                            type: 'success',
                            title: 'Tu plantilla se ha actualizado!'
                        });
                        actualizarTable();
                    } else {
                        swal({
                            type: 'error',
                            title: msg.msg
                        });
                    }
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
        })

        $('#nuevoPuesto').on('click', function() {
            var params = {
                idSucursal: "{{ $sucursal->id }}",
                idPuesto: $('#puesto').val(),
                cantidad: $('#cantidadModal').val(),
                _token: "{{ csrf_token() }}",
            }
            $.ajax({
                type: "POST",
                url: "{{ route('agregarPuestoSuc') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        swal(msg.msg, '', 'success')
                        var input = document.getElementById('puesto');
                        generarRow($('#puesto').val(), $('#cantidadModal').val(), msg.id, input.options[
                            input.selectedIndex].text, '0');
                    } else {
                        swal(msg.msg, '', 'error')
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        })

        function generarRow(puesto, cantidad, idPlaza, puestoNom, activos) {

            var table = $('#tableBody');
            var tr = document.createElement('tr');
            var tdPuesto = document.createElement('td');
            var tdCant = document.createElement('td');
            var tdActivo = document.createElement('td');
            var tdBtn = document.createElement('td');
            var label = document.createElement('label');
            var inputCant = document.createElement('input');
            var inputId = document.createElement('input');
            var inputIdplaza = document.createElement('input');
            var btn = document.createElement('a');

            tdPuesto.classList = "text-left";
            inputCant.classList = "form-control";
            btn.classList = 'btn btn-just-icon btn-danger text-white';

            inputId.type = 'hidden'
            inputIdplaza.type = 'hidden'
            inputCant.type = 'number'

            label.innerHTML = puestoNom;

            inputId.value = puesto;
            inputIdplaza.value = idPlaza;
            inputCant.value = cantidad;
            btn.onclick = function() {
                borrarPuesto(idPlaza, 0);
            }
            btn.innerHTML = '<i class="material-icons">delete</i>';

            inputId.name = 'puestos[]';
            inputIdplaza.name = 'puestosIds[]';
            inputCant.name = 'cantidad[]';

            tdPuesto.appendChild(label);
            tdCant.appendChild(inputCant);
            tdCant.appendChild(inputId);
            tdCant.appendChild(inputIdplaza);
            tdBtn.appendChild(btn);
            tdActivo.innerHTML = activos + ` / ` + cantidad

            tr.appendChild(tdPuesto);
            tr.appendChild(tdCant);
            tr.appendChild(tdActivo);
            tr.appendChild(tdBtn);
            tr.id = `item-${idPlaza}`;

            table.append(tr);
        }

        function borrarPuesto(id, activos) {
            swal({
                title: 'Seguro que quieres borrar este puesto?',
                html: 'El puesto sera borrado para la sucursal actual',
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: 'Si'
            }).then((value) => {
                if (value) {
                    if (activos > 0) {
                        swal({
                            title: 'Seguro quieres eliminar?',
                            html: 'Este puesto tiene empleados activos, seguro que quieres eliminarlo?',
                            type: 'warning',
                            showCloseButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Si'
                        }).then((value) => {
                            if (value) {
                                eliminarRegistro(id);
                            }
                        })
                    } else {
                        eliminarRegistro(id);
                    }
                }
            })
        }

        function eliminarRegistro(id) {
            var params = {
                idPlaza: id,
                _token: "{{ csrf_token() }}"
            }
            $.ajax({
                type: "POST",
                url: "{{ route('borrarPuestoSuc') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        swal(msg.msg, '', 'success')
                        var table = $('#tableBody')[0];
                        table.removeChild($(`#item-${id}`)[0]);
                    } else {
                        swal(msg.msg, '', 'error')
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        }

        function actualizarTable() {
            var params = {
                idSucursal: "{{ $sucursal->id }}",
                _token: "{{ csrf_token() }}"
            }
            $.ajax({
                type: "POST",
                url: "{{ route('actualizarPlantillaTabla') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        document.getElementById('tableBody').innerHTML = '';
                        console.log(msg)
                        msg.data.forEach(element => {
                            generarRow(element.idPuesto, element.cantidad, element.idPlaza, element
                                .puesto, element.Activos);
                        });
                    } else {
                        swal(msg.msg, '', 'error')
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        }
    </script>
@endsection
