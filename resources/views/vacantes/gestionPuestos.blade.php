@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'gestionPuestos'])
@section('content')
    <div class="w-50" style="margin: 0 auto">
        <div class="card">
            <div class="card-header card-header-success card-header-icon">
                <div class="card-icon">
                    <i class="material-icons">manage_accounts</i>
                </div>
                <h4 class="card-title">Gestion de puestos</h4>
            </div>
            <div class="row m-2">
                <div class="col">
                    <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Buscar">
                </div>
                <div class="col-1">
                    <a class="btn btn-primary btn-just-icon btn-sm text-white" id="agregar" data-toggle="tooltip"
                        data-placement="top" title="Agregar nuevo puesto    "><i class="material-icons">add</i></a>
                </div>
            </div>
        </div>
        <div class="card">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Puesto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($puestos as $item)
                        <tr id="item-{{ $item->idPuesto }}">
                            <td class="text-left pl-2">{{ $item->nombre }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm btn-just-icon"
                                    onclick="editar({{ $item->idPuesto }}, `{{ $item->nombre }}`)"><i
                                        class="material-icons">edit</i></a>
                                <a class="btn btn-danger btn-sm text-white btn-just-icon"
                                    onclick="eliminar({{ $item->idPuesto }})"><i class="material-icons">delete</i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
@endsection
@section('aditionalScripts')
    <script>
        var lista = {!! json_encode($puestos) !!}
        var plantillaHTML = `<tr id="item-:id">
                                    <td class="text-left pl-2">:nombre</td>
                                    <td>
                                        <a class="btn btn-secondary btn-sm btn-just-icon" onclick="editar( :id , ':nombre')"><i class="material-icons">edit</i></a>
                                        <a class="btn btn-danger btn-sm text-white btn-just-icon" onclick="eliminar( :id )"><i
                                                class="material-icons">delete</i></a>
                                    </td>
                                </tr>`;

        $('#agregar').on('click', function() {
            swal({
                title: 'Agregar puesto',
                html: '<input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre">',
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Guardar'
            }).then((value) => {
                if (value) {
                    var params = {
                        nombre: $('#nombre').val().toUpperCase(),
                        _token: "{{ csrf_token() }}",
                    }
                    $.ajax({
                        type: "POST",
                        url: "{{ route('agregarPuesto') }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success) {
                                swal(msg.msg, '', 'success');
                                var element = {
                                    nombre: $('#nombre').val().toUpperCase(),
                                    idPuesto: msg.id
                                }
                                lista.push(element);
                                renderTable();
                            } else {
                                swal(msg.msg, '', 'error');
                            }
                        },
                        error: function() {
                            console.log("error");
                        }
                    });
                }
            })
        })

        function editar(id, nombre) {
            swal({
                title: 'Editar puesto',
                html: `<input type="text" name="editarNombre" id="editarNombre" class="form-control" placeholder="Nombre" value="${nombre}">`,
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Guardar'
            }).then((value) => {
                if (value) {
                    var params = {
                        idPuesto: id,
                        nombre: $('#editarNombre').val().toUpperCase(),
                        _token: "{{ csrf_token() }}",
                    }
                    $.ajax({
                        type: "POST",
                        url: "{{ route('editarPuesto') }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success) {
                                swal(msg.msg, '', 'success');
                                $('#tableBody').html('');
                                lista.map((element) => {
                                    if (element.idPuesto == id) {
                                        element.nombre = $('#editarNombre').val().toUpperCase();
                                    }
                                })
                                renderTable();
                            } else {
                                swal(msg.msg, '', 'error');
                            }
                        },
                        error: function() {
                            console.log("error");
                        }
                    });
                }
            })
        }

        function eliminar(id) {
            swal({
                title: 'Seguro deseas eliminar?',
                type: 'warning',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Si'
            }).then((value) => {
                if (value) {
                    var params = {
                        idPuesto: id,
                        _token: "{{ csrf_token() }}"
                    }

                    $.ajax({
                        type: "POST",
                        url: "{{ route('eliminarPuesto') }}",
                        data: params,
                        success: function(msg) {
                            if (msg.success) {
                                swal(msg.msg, '', 'success');
                                $(`#item-${id}`).remove();
                                lista = lista.filter((item) => item.idPuesto != id)
                            } else {
                                swal(msg.msg, msg.msg2 ?? "", 'error');
                            }
                        },
                        error: function() {
                            console.log("error");
                        }
                    });
                }
            })
        }

        $('#buscar').keyup(function() {
            renderTable();
        })

        function renderTable() {
            var query = $('#buscar').val();
            if (query.length > 3) {
                $('#tableBody').html('');
                lista.map(function(element) {
                    if (element.nombre.toLowerCase().includes(query.toLowerCase())) {
                        var html = plantillaHTML;
                        html = html.replaceAll(':nombre', element.nombre);
                        html = html.replaceAll(':id', element.idPuesto);
                        $('#tableBody').append(html);
                    }
                })
            } else {
                $('#tableBody').html('');
                lista.map(function(element) {
                    var html = plantillaHTML;
                    html = html.replaceAll(':nombre', element.nombre);
                    html = html.replaceAll(':id', element.idPuesto);
                    $('#tableBody').append(html);
                })
            }
        }
    </script>
@endsection
