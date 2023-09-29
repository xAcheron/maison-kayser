@extends('layouts.delivery')
@include('menu.delivery')
@section('content')
    <style>
        button:focus:not(:focus-visible) {
            outline: 0;
        }

        .nav-link:focus,
        .nav-link:hover {
            text-decoration: none;
        }

        [type=button]:not(:disabled),
        [type=reset]:not(:disabled),
        [type=submit]:not(:disabled),
        button:not(:disabled) {
            cursor: pointer;
        }

        .nav-link {
            display: block;
            padding: 0.5rem 1rem;
        }

        .nav-pills .nav-link {
            background: 0 0;
            border: 0;
            border-radius: 0.25rem;
        }

        .lds-ring {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .lds-ring div {
            box-sizing: border-box;
            display: block;
            position: absolute;
            width: 64px;
            height: 64px;
            margin: 8px;
            border: 8px solid #2196f3;
            border-radius: 50%;
            animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: #2196f3 transparent transparent transparent;
        }

        .lds-ring div:nth-child(1) {
            animation-delay: -0.45s;
        }

        .lds-ring div:nth-child(2) {
            animation-delay: -0.3s;
        }

        .lds-ring div:nth-child(3) {
            animation-delay: -0.15s;
        }

        @keyframes lds-ring {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="text-center mb-0">
                <span id="sucursalTitle">sucursal</span>
                <i class="material-icons text-info" data-target="#sucursalModal" data-toggle="modal"
                    style="cursor: pointer">edit</i>
                <div class="float-right">
                    <a class="btn btn-info text-white" id="orderVisualizerBtn"><i class="material-icons">web_asset</i></a>
                    <a class="btn btn-info text-white" id="orderListBtn"><i class="material-icons">list</i></a>
                </div>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-3">
                    <div class="mb-2">
                        <input type="text" class="form-control" placeholder="Buscar" id="searchItem">
                    </div>
                    <div id="loadingItems" class="d-flex justify-content-center" style="display: block;">
                        <div class="lds-ring">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="nav flex-column flex-nowrap nav-pills" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical" style="height: 80vh; overflow-x: auto; display: none;">
                    </div>
                </div>
                <div class="col-9">

                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show" id="v-pills-home" role="tabpanel"
                            aria-labelledby="v-pills-home-tab">
                            <div id="loading" class="d-flex justify-content-center" style="display: block;">
                                <div class="lds-ring">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <div id="details" class="card" style="display: none">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <img src="" alt="" class="img-fluid" id="imgDetail"
                                            style="height: 300px;">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nombre</label>
                                        <input type="text" name="name" id="name" class="w-100" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Descripcion</label>
                                        <textarea name="description" id="description" cols="20" class="w-100" disabled></textarea>
                                    </div>
                                    <form action="" id="formItem">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" id="idItem" name="idItem">
                                        <input type="hidden" id="idEMC" name="idStore">
                                        <div class="form-group">
                                            <label for="">Estado</label>
                                            <div class="togglebutton">
                                                <label>
                                                    <input type="checkbox" id="status" name="status">
                                                    <span class="toggle"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="accordion px-4">
                                    <div class="card my-0">
                                        <div class="card-header" id="headingOne">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link btn-block text-left" type="button"
                                                    data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                                    aria-controls="collapseOne" style="color: black !important"
                                                    id="btnHistory">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            Historial cambio de estados
                                                        </div>
                                                        <div>
                                                            <i class="material-icons">arrow_drop_down</i>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                        </div>

                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                            data-parent="#accordionExample">
                                            <div class="card-body">
                                                <table class="table table-condensed">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Hora</th>
                                                            <th>Accion</th>
                                                            <th>Usuario</th>
                                                            <th>Uber</th>
                                                            <th>Rappi</th>
                                                            <th>Justo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbodyHistory">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-success" id="saveBtn">
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sucursalModal" tabindex="-1" role="dialog" aria-labelledby="sucursal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="sucursalLabel">Selecciones una sucursal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <select name="store" id="store" class="form-control select2" style="width: 100%">
                        @if (!empty($sucursales))
                            @foreach ($sucursales as $item)
                                <option value="{{ $item->id }}" data-nombre="{{ $item->nombre }}"
                                    data-emc="{{ $item->idEMC }}">
                                    {{ $item->nombre }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="selectSuc"
                        data-dismiss="modal">Seleccionar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('jsimports')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endsection
@section('aditionalScripts')
    <script>
        var timeout;
        $(document).ready(function() {
            var items;
            const valores = window.location.search;
            const urlParams = new URLSearchParams(valores);
            const idStore = urlParams.get('idStore');

            if (idStore != 'undefined') {
                $('#store').val(idStore)
            }

            $('.select2').select2({
                dropdownParent: $('#sucursalModal')
            })
            getItems();
        })

        $('#orderVisualizerBtn').on('click', function() {
            document.location.href = `{{ route('delivery') }}?idStore=${$('#store').val()}`;
        })
        $('#orderListBtn').on('click', function() {
            document.location.href = `{{ route('ordersListDelivery') }}?idStore=${$('#store').val()}`;
        })

        $('#selectSuc').on('click', function() {
            getItems();
        })

        $('#searchItem').on('keydown', function() {
            query = $(this).val();
            $('#loadingItems').show();
            $('#v-pills-tab').hide();
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                const data = items.filter(e => e.name.toLowerCase().includes(query.toLowerCase()))
                genItems(data);
                $('#loadingItems').attr('style', 'display:none !important');
                $('#v-pills-tab').show();
            }, 1500);
        })

        $('#btnHistory').on('click', function() {
            const idStore = $('#store').val()
            const idItem = $('#idItem').val()

            const params = {
                idStore: idStore,
                idItem: idItem,
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('historyBlock') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        const tbody = $('#tbodyHistory')
                        tbody.html('');

                        msg.data.forEach(element => {
                            const tr = document.createElement('tr');
                            const tdFecha = document.createElement('td');
                            const tdHora = document.createElement('td');
                            const tdEstado = document.createElement('td');
                            const tdUsuario = document.createElement('td');
                            const tdUber = document.createElement('td');
                            const tdRappi = document.createElement('td');
                            const tdJusto = document.createElement('td');

                            var plataformas = element.plataforma.split(',');
                            var estadoB = element.estadoB.split(',');
                            var intentos = element.intentos.split(',');

                            plataformas.forEach((plat, index) => {
                                const icon = document.createElement('i');
                                icon.classList.add('material-icons');
                                if (estadoB[index] == "1") {
                                    icon.classList.add('text-success');
                                    icon.innerHTML = 'published_with_changes';
                                } else if (estadoB[index] == "0" && intentos[index] >
                                    0) {
                                    icon.classList.add('text-danger');
                                    icon.innerHTML = 'sync_problem';
                                } else if (estadoB[index] == "0") {
                                    icon.classList.add('text-info');
                                    icon.innerHTML = 'cloud_sync';
                                }

                                if (plat == 1) {
                                    tdJusto.appendChild(icon);
                                } else if (plat == 4) {
                                    tdRappi.appendChild(icon);
                                } else if (plat == 6) {
                                    tdUber.appendChild(icon);
                                }

                            });

                            tdFecha.innerHTML = element.fecha;
                            tdHora.innerHTML = element.hora;
                            tdEstado.innerHTML = element.estadoI == 1 ? 'Habilitar' :
                                'Deshabilitar';
                            tdUsuario.innerHTML = element.usuario ?? 'Sin usuario';

                            tr.appendChild(tdFecha);
                            tr.appendChild(tdHora);
                            tr.appendChild(tdEstado);
                            tr.appendChild(tdUsuario);
                            tr.appendChild(tdUber);
                            tr.appendChild(tdRappi);
                            tr.appendChild(tdJusto);

                            tbody.append(tr);

                        });
                    }
                },
                error: function() {
                    console.log('fallo')
                }

            });
        })

        function getItems() {
            $('#v-pills-home').removeClass('show active')
            const idStore = $('#store').val()
            const nameSuc = $('#store :selected').data('nombre')
            $("#sucursalTitle").text(nameSuc);
            $('#loadingItems').show();
            $('#v-pills-tab').hide();

            const params = {
                idStore: idStore,
                _token: "{{ csrf_token() }}"
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getItemsDelivery') }}",
                data: params,
                success: function(msg) {
                    items = msg.data;
                    genItems(items);
                    $('#loadingItems').attr('style', 'display:none !important');
                    $('#v-pills-tab').show();

                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function getDetails(idItem) {
            $('#loading').attr('style', 'display:none !important');
            $('#details').hide();

            const idStore = $('#store').val();
            const idEMC = $('#store :selected').data('emc');
            $('#tbodyHistory').html('');
            $('#collapseOne').removeClass('show');

            const params = {
                idItem: idItem,
                idStore: idStore,
                _token: "{{ csrf_token() }}"
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getItemDetailDelivery') }}",
                data: params,
                success: function(msg) {
                    $('#idItem').val(msg.data[0].id);
                    $('#idEMC').val(idStore);
                    $('#name').val(msg.data[0].name);
                    $('#description').val(msg.data[0].description);
                    $('#status').prop('checked', msg.data[0].status);
                    $('#imgDetail').prop('src', msg.data[0].image_url);
                    $('#loading').hide();
                    $('#details').show();
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        $('#saveBtn').on('click', function() {
            const formData = $('#formItem').serialize();
            Swal.fire({
                allowEscapeKey: false,
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading()
                }
            });

            $.ajax({
                type: "POST",
                url: "{{ route('blockItemDelivery') }}",
                data: formData,
                success: function(msg) {
                    if (msg.success) {
                        swal('Se mando la solicitud correctamente', '', 'success')
                    } else {
                        swal('No se mando la solicitud', 'Contacte con soporte', 'error')
                    }
                },
                error: function() {
                    console.log('fallo')
                    swal.close()
                }

            });

        })

        function genItems(data) {
            $('#v-pills-tab').html('')
            data.forEach(element => {
                const btn = `<button class="nav-link w-100" id="v-pills-home-tab" data-toggle="pill"
                                data-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home"
                                aria-selected="true" onclick="getDetails('${element.id}')">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        ${element.name}
                                    </div>
                                </div>
                            </button>`
                $('#v-pills-tab').append(btn)
            });
        }
    </script>
@endsection
