@extends('layouts.delivery')
@include('menu.delivery')
@section('content')
    <style>
        .estado {
            border-radius: 4px;
            font-size: 16px;
            padding: 4px 8px;
            text-align: center;
        }

        .bg-amber {
            background-color: rgba(255, 193, 7, 1) !important;
        }

        .bg-indigo {
            background-color: rgba(63, 81, 181, 1) !important;
        }

        .bg-green {
            background-color: rgba(76, 175, 80, 1) !important;
        }

        .bg-red {
            background-color: rgba(244, 67, 54, 1) !important;
        }

        .text-white {
            color: #fff !important;
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
            border: 8px solid black;
            border-radius: 50%;
            animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            border-color: black transparent transparent transparent;
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

        #descripcion.modal.right .modal-dialog {
            position: fixed;
            margin: auto;
            width: 100%;
            height: 100%;
            -webkit-transform: translate3d(0%, 0, 0);
            -ms-transform: translate3d(0%, 0, 0);
            -o-transform: translate3d(0%, 0, 0);
            transform: translate3d(0%, 0, 0);
        }

        #descripcion.modal.right .modal-content {
            height: 100%;
            overflow-y: auto;
        }

        #descripcion.modal.right .modal-body {
            padding: 15px 15px 80px;
        }

        #descripcion.modal.right.fade .modal-dialog {
            right: 0;
            -webkit-transition: opacity 0.3s linear, right 0.3s ease-out;
            -moz-transition: opacity 0.3s linear, right 0.3s ease-out;
            -o-transition: opacity 0.3s linear, right 0.3s ease-out;
            transition: opacity 0.3s linear, right 0.3s ease-out;
        }

        #descripcion.modal.right.fade.in .modal-dialog {
            right: 0;
        }

        #descripcion.modal-content {
            border-radius: 0;
            border: none;
        }

        #descripcion.modal-header {
            border-bottom-color: #EEEEEE;
            background-color: #FAFAFA;
        }
    </style>
    {{-- <script type="module" crossorigin src="/Laravel/resources/views/delivery/assets/index.js"></script> --}}
    <script type="module" crossorigin src="assets/index.js"></script>
    <link rel="stylesheet" href="assets/index.css">
    <input type="hidden" id="_token" value="{{ csrf_token() }}">
    <div class="card" style="height: 90vh">
        <div class="card-header pt-0 pb-0">
            <h3 class="text-center mb=0">

                <div class="float-left">
                    <a class="btn btn-info text-white" id="horariosBtn" data-toggle="modal" data-target="#horariosModal"
                        onclick=""><i class="material-icons">schedule</i></a>
                </div>

                <span id="sucursalTitle">sucursal</span>
                <i class="material-icons text-info" data-target="#sucursalModal" data-toggle="modal"
                    style="cursor: pointer">edit</i>
                <div class="float-right">
                    <a class="btn btn-info text-white" id="settingsBtn" data-toggle="modal" data-target="#settingModal"
                        onclick="getStatusNoti()"><i class="material-icons">settings</i></a>
                    <a class="btn btn-info text-white" id="orderListBtn"><i class="material-icons">list</i></a>
                    <a class="btn btn-info text-white" id="productListBtn"><i class="material-icons">fastfood</i></a>
                </div>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-md-4 border rounded p-0">
                    <h4 class="text-center">
                        Nuevas
                    </h4>
                    <hr class="dropdown-divider">
                    <div class="container" id="ordersNew" style="height: 75vh; overflow-y: scroll">
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 border rounded p-0">
                    <h4 class="text-center">
                        En Proceso
                    </h4>
                    <hr class="dropdown-divider">
                    <div class="container" id="ordersDelivered" style="height: 75vh; overflow-y: scroll">
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 border rounded p-0">
                    <h4 class="text-center">
                        Necesita Atención
                    </h4>
                    <hr class="dropdown-divider">
                    <div class="container" id="ordersError" style="height: 75vh; overflow-y: scroll">
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
                    <select name="sucursal" id="sucursal" class="form-control select2" style="width: 100%">
                        @if (!empty($sucursales))
                            @if (count($sucursales) > 1)
                                <option value="All" data-nombre="Todas" selected>Todas</option>
                            @endif
                            @foreach ($sucursales as $item)
                                <option value="{{ $item->id }}" data-nombre="{{ $item->nombre }}">{{ $item->nombre }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="selectSuc" data-dismiss="modal">Seleccionar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Descripcion --}}
    <div class="modal right fade" id="descripcion" tabindex="-1" role="dialog" aria-labelledby="descripcion">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="lds-ring" style="display: none" id="spinnerDetail">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="modal-header"></div>
                <div class="modal-body" style="overflow-y: scroll; display: none;" id="modalBody">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div id="bannerError"></div>
                    <div id="bannerPago">
                    </div>
                    <h3 id="clientOrderModal"></h3>
                    <div class="row align-items-center" id="deliveryInfo" style="display: none">
                        <hr class="dropdown-divider">
                        <div class="col-md-9">
                            <p>Repartidor</p>
                            <p id="deliveryName" class="m-0"></p>
                            <p id="deliveryPhone" class="m-0"></p>
                            <p id="deliveryPhoneCode" class="m-0"></p>
                            <p id="deliveryEta" class="m-0"></p>
                            <p id="deliveryPassword" class="m-0"></p>
                        </div>
                        <div class="col-md-3" id="deliveryImg">
                            <img src="" alt="" class="img-fluid rounded-circle">
                        </div>
                    </div>
                    <hr class="dropdown-divider">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h3 id="idOrderModal"></h3>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-success text-white rounded text-center" id="estado">
                            </div>
                        </div>
                    </div>
                    <hr class="dropdown-divider">
                    <div>
                        <span class="font-weight-bold">Detalle de la orden</span>
                    </div>
                    <hr class="dropdown-divider">
                    <div id="itemsOrder">
                    </div>
                    <div>
                        <h3>Comentarios</h3>
                        <div id="comentsOrder">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success ml-3" id="envPos" style="display: none">Enviar
                            pedido a pos</button>
                        <button type="button" class="btn btn-warning ml-3" id="anticipar"
                            style="display: none;">Anticipar Delivery</button>
                    </div>
                    <div class="text-right ">
                        <h3 class="font-weight-bold">Total: $<span id="totalModal"></span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Horarios --}}
    <div class="modal fade" id="horariosModal" tabindex="-1" role="dialog" aria-labelledby="horarios">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="sucursalLabel">Horarios <span id="dateNow"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="horariosTable" class="table">
                        <thead></thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Setting Modal --}}
    <div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingModalLabel">Configuración</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 style="text-align: center">Notificaciones Delivery</h5>
                    <div class="togglebutton">
                        <label
                            style="color: #AAAAAA !important; display: flex; align-items: center; justify-content: space-between;">
                            En todos los dispositivos
                            <input type="checkbox" checked="" id="allNoti" onchange="setStatusNoti('allNoti')">
                            <span class="toggle"></span>
                        </label>
                    </div>
                    <div class="togglebutton">
                        <label
                            style="color: #AAAAAA !important; display: flex; align-items: center; justify-content: space-between;">
                            En este dispositivo
                            <input type="checkbox" checked="" id="tokenNoti" onchange="setStatusNoti('tokenNoti')">
                            <span class="toggle"></span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
        $('#orderListBtn').on('click', function() {
            document.location.href = `{{ route('ordersListDelivery') }}?idStore=${$('#sucursal').val()}`;
        })
        $('#productListBtn').on('click', function() {
            document.location.href = `{{ route('productListDelivery') }}?idStore=${$('#sucursal').val()}`;
        })

        $(document).ready(function() {
            const body = $('.card-body')
            const height = body.innerHeight() - 100;
            const ordersNew = document.getElementById('ordersNew');
            const ordersDelivered = document.getElementById('ordersDelivered');
            const ordersError = document.getElementById('ordersError');
            ordersNew.style.height = `${height}px`;
            ordersDelivered.style.height = `${height}px`;
            ordersError.style.height = `${height}px`;
            const valores = window.location.search;
            const urlParams = new URLSearchParams(valores);
            const idStore = urlParams.get('idStore');
            console.log(idStore)
            if (idStore != null) {
                $('#sucursal').val(idStore)
            }
            $('#minimizeSidebar').click()
            getOrders();
            $('.select2').select2({
                dropdownParent: $('#sucursalModal')
            })
            setInterval(() => {
                getOrders();
            }, 50000);
        })


        $("#selectSuc").on("click", function() {
            getOrders();
        })

        function getStatusNoti() {
            var params = {
                token: $('#fireToken').val(),
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getStatusNotiDelivery') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        $('#allNoti').prop('checked', msg.all);
                        $('#tokenNoti').prop('checked', msg.statusToken);
                    } else {
                        $('#allNoti').prop('checked', false);
                        $('#tokenNoti').prop('checked', false);
                    }
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        $('#horariosBtn').on('click', function() {
            var params = {
                sucursal: $('#sucursal').val(),
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getHorarios') }}",
                data: params,
                success: function(msg) {

                    const data = msg.data;

                    const tbody = $('#horariosTable tbody');
                    const span = $('#dateNow')
                    tbody.html('')

                    span.html(data.textDate)

                    data.plataformas.forEach(element => {
                        if (element.platform == "RAPPI") {
                            img = "{{ asset('Laravel/resources/assets/logo_Rappi.webp') }}";
                        } else if (element.platform == "EKM") {
                            img = "{{ asset('Laravel/resources/assets/logo_EKM.webp') }}";
                        } else if (element.platform == "UBER") {
                            img = "{{ asset('Laravel/resources/assets/logo_Uber.webp') }}";
                        } else {
                            img = "{{ asset('Laravel/resources/assets/logo_EKM.webp') }}";
                        }

                        $html =
                            `<tr>
                                <td>
                                    <img src="${img}" class="rounded-circle" style="height: 50px" alt="${element.platform}">
                                </td>
                                <td>
                                    ${element.horario}
                                </td>
                                <td>
                                     ${element.especial ? `<i class="material-icons text-warning">stars</i>` : ''}
                                </td>
                                <td>
                                    ${element.estado == 1 ? 
                                        `<i class="material-icons text-success">check_circle</i>`
                                        :`<i class="material-icons text-danger">cancel</i>`
                                    }
                                </td>
                            </tr>`;

                        tbody.append($html);
                    });


                },
                error: function() {
                    console.log('fallo')
                }

            });
        })

        function setStatusNoti(id) {
            const value = $(`#${id}`).prop('checked');
            var params = {
                estado: value,
                token: id == 'tokenNoti' ? $('#fireToken').val() : '',
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('setStatusNotiDelivery') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        swal(`Las notificaciones de delivery se ${value ? 'activaron' : 'desactivaron'}`, '',
                            'success');
                    } else {
                        swal(`Ocurrio un error al modificar las notificaciones`, '', 'error');
                    }
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function getOrders(params) {
            var sucursal = document.getElementById("sucursal");
            var sucursalVal = sucursal.value;
            var sucursalNombre = sucursal.getElementsByTagName("option")[sucursal.selectedIndex].text;
            $("#sucursalTitle").text(sucursalNombre);
            var ordersNew = document.getElementById('ordersNew');
            var ordersDelivered = document.getElementById('ordersDelivered');
            var ordersError = document.getElementById('ordersError');

            var params = {
                idStore: sucursalVal,
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getAcceptedOrders') }}",
                data: params,
                success: function(msg) {
                    ordersNew.innerHTML = "";
                    ordersDelivered.innerHTML = "";
                    ordersError.innerHTML = "";
                    if (msg.total > 0) {
                        msg.pedidos.forEach(element => {

                            if (element.pos_created == 2 || element.pos_notified == 4) {
                                ordersError.innerHTML += tarjetaOrder(element, element.pos_notified);
                            } else if (element.pos_notified == 1 || element.pos_notified == 0) {
                                ordersNew.innerHTML += tarjetaOrder(element, element.pos_notified);
                            } else if (element.pos_notified != 3) {
                                ordersDelivered.innerHTML += tarjetaOrder(element, element
                                    .pos_notified);
                            }
                        });
                    }

                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function getOrderDetail(id, type, clientName, idOrder, total, recoleccion, pago, estado, posCreated, telefono,
            numeroDoc, idPlat, billing_name, billing_phone, billing_type, billing_document_type, billing_document_number,
            billing_address) {

            var itemsOrderElement = document.getElementById('itemsOrder');
            var clientNameModal = document.getElementById('clientOrderModal');
            var idOrderModal = document.getElementById('idOrderModal');
            var totalModal = document.getElementById('totalModal');
            var estadoPedido = document.getElementById('estado');
            var deliveryName = document.getElementById('deliveryName');
            var deliveryPhone = document.getElementById('deliveryPhone');
            var deliveryPhoneCode = document.getElementById('deliveryPhoneCode');
            var deliveryEta = document.getElementById('deliveryEta');
            var deliveryPassword = document.getElementById('deliveryPassword');
            var deliveryImg = document.getElementById('deliveryImg').children[0];
            var spinner = $('#spinnerDetail');
            var modalBody = $('#modalBody');
            var comentsOrder = $('#comentsOrder')
            var envPosEle = document.getElementById('envPos');
            var anticiparEle = document.getElementById('anticipar');

            itemsOrderElement.innerHTML = '';
            clientName.innerHTML = '';
            idOrderModal.innerHTML = '';
            totalModal.innerHTML = '';
            estadoPedido.innerHTML = '';
            deliveryName.innerHTML = '';
            deliveryPhone.innerHTML = '';
            deliveryPhoneCode.innerHTML = '';
            deliveryEta.innerHTML = '';
            deliveryPassword.innerHTML = '';
            deliveryImg.innerHTML = '';
            deliveryImg.innerHTML = '';
            comentsOrder.innerHTML = '';
            document.getElementById('bannerError').innerHTML = '';
            document.getElementById('bannerPago').innerHTML = '';

            spinner.show();
            modalBody.hide();

            var params = {
                id: id,
                type: type,
                platform: idPlat,
                _token: "{{ csrf_token() }}",
            }
            itemsOrderElement.innerHTML = "";


            if (billing_name != 'null' && billing_name != '') {
                $('#deliveryInfo').show();
                deliveryName.innerHTML = `Nombre: ${billing_name}`;
                deliveryPhone.innerHTML = `Telefono: ${billing_phone}`;
                if (billing_type != 'null' && billing_type != '') {
                    deliveryPhoneCode.innerHTML = `Codigo: ${billing_type}`;
                }
                if (billing_document_type != 'null' && billing_document_type != '') {
                    deliveryEta.innerHTML = `Tiempo de llegada: ${billing_document_type}`;
                }
                if (billing_document_number != 'null' && billing_document_number != '') {
                    deliveryPassword.innerHTML = `Contraseña: ${billing_document_number}`;
                }

                if (billing_address != 'null' && billing_address != '') {
                    deliveryImg.src = billing_address;
                } else {
                    deliveryImg.src = ''
                }
            } else {
                $('#deliveryInfo').hide();
            }

            $.ajax({
                type: "POST",
                url: "{{ route('getOrderDetail') }}",
                data: params,
                success: function(msg) {
                    spinner.hide();
                    modalBody.show();
                    clientNameModal.innerHTML = `<div>${clientName} - ${type}</div>
                                                <div>Tel.: ${telefono}</div>
                                                ${
                                                    type == 'Uber' ? 
                                                    `<div>Ref.: ${numeroDoc}</div>`
                                                    : ''
                                                }
                                                `;
                    idOrderModal.innerHTML = idOrder;
                    totalModal.innerHTML = total;
                    envPosEle.style.display = 'none'
                    anticiparEle.style.display = 'none'
                    if (posCreated == 2) {
                        envPosEle.style.display = 'block'
                        envPosEle.onclick = () => envPos(id, type);
                    }
                    if (estado < 3 || estado == 4) {
                        estadoPedido.innerHTML = "Sin entregar"
                        estadoPedido.className = 'bg-danger text-white rounded text-center';
                        if (type != 'Uber' && recoleccion == 'delivery') {
                            anticiparEle.style.display = 'block'
                            if (type == 'Rappi') {
                                anticiparEle.onclick = () => anticiparDelivery(idOrder, type);
                            } else {
                                var orderIdSend = idOrder.substring(1);
                                anticiparEle.onclick = () => anticiparDelivery(orderIdSend, type);
                            }
                        }
                    } else {
                        estadoPedido.innerHTML = "Entregado"
                        estadoPedido.className = 'bg-success text-white rounded text-center';
                    }

                    if (posCreated == 2) {
                        document.getElementById('bannerError').innerHTML = `<div class="alert alert-danger mt-4" role="alert">
                            Error: ${msg.pedidos[0].error}
                            </div>`;
                    }
                    document.getElementById('bannerPago').innerHTML = bannerPago(recoleccion, pago);

                    msg.pedidos[0].partidas.forEach(element => {
                        itemsOrderElement.appendChild(itemOrder(element));
                    });
                    msg.pedidos[0].combos.forEach(element => {
                        itemsOrderElement.appendChild(itemOrder(element));
                    });

                    comentsOrder.html('')

                    msg.pedidos[0].comentarios.forEach(element => {
                        comentsOrder.append(element + "<br />")
                    })
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function anticiparDelivery(id, type) {
            var params = {
                id: id,
                type: type,
                _token: "{{ csrf_token() }}",
            }
            var url = "{{ route('anticiparDelivery', [':id', ':type']) }}";
            url = url.replace(':id', id);
            url = url.replace(':type', type);

            $.ajax({
                type: "POST",
                url: url,
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        swal('', msg.msg, 'success')
                    } else {
                        swal('Algo salio mal', msg.msg, 'error')
                    }
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function envPos(id, type) {

            var params = {
                id: id,
                type: type,
                _token: "{{ csrf_token() }}",
            }

            $.ajax({
                type: "POST",
                url: "{{ route('envPos') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        swal('', msg.msg, 'success')
                    } else {
                        swal('Algo salio mal', 'La orden no se movio a la pos', 'error')

                    }
                },
                error: function() {
                    console.log('fallo')
                }

            });
        }

        function bannerPago(recoleccion, pago) {

            var barra;
            var metodo;

            switch (pago) {
                case "inStore":
                    metodo = "En tienda";
                    break;
                case "mercadoPagoCardMX":
                    metodo = "Mercado Pago";
                    break;
                case "paypalMX":
                    metodo = "PayPal";
                    break;
                case "cc":
                    metodo = "Tarjeta";
                    break;
                case "cash":
                    metodo = "Efectivo";
                    break;
                default:
                    metodo = "Pagado online";
                    break;
            }

            if (recoleccion == "delivery") {

                barra = `<div class="alert alert-warning mt-4" role="alert">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                Tipo de pago: ${metodo}
                                            </div>
                                            <div class="col-md-6 text-right">
                                                Recoleccion: Delivery
                                                <i class="material-icons text-white" style="font-size: 19px">motorcycle</i>
                                            </div>
                                        </div>
                                    </div>`;
            } else {
                barra = `<div class="alert alert-info mt-4" role="alert">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                Tipo de pago: ${metodo}
                                            </div>
                                            <div class="col-md-6 text-right">
                                                Recoleccion: Cliente
                                                <i class="material-icons text-white" style="font-size: 19px">local_mall</i>
                                            </div>
                                        </div>
                                    </div>`;
            }
            return barra;

        }

        function tarjetaOrder(order, status = 1) {

            var img;
            var estadoHtml;


            if (order.type == "Rappi") {
                img = "{{ asset('Laravel/resources/assets/logo_Rappi.webp') }}";
            } else if (order.type == "EKM") {
                img = "{{ asset('Laravel/resources/assets/logo_EKM.webp') }}";
            } else if (order.type == "Uber") {
                img = "{{ asset('Laravel/resources/assets/logo_Uber.webp') }}";
            } else {
                img = "{{ asset('Laravel/resources/assets/logo_EKM.webp') }}";
            }

            const estados = {
                0: {
                    clase: 'bg-amber',
                    texto: 'Pedido Nuevo',
                    icono: 'motorcycle',
                },
                1: {
                    clase: 'bg-amber',
                    texto: 'Pedido Nuevo',
                    icono: 'motorcycle',
                },
                2: {
                    clase: 'bg-indigo',
                    texto: 'Delivery cerca del local',
                    icono: 'store',
                },
                3: {
                    clase: 'bg-green',
                    texto: 'Pedido Entregado',
                    icono: 'takeout_dining',
                },
                4: {
                    clase: 'bg-red',
                    texto: 'Pedido Cancelado',
                    icono: 'cancel',
                },
                5: {
                    clase: 'bg-green',
                    texto: 'Pedido Terminado',
                    icono: 'done',
                },
            };

            const estado = estados[status];

            if (order.pos_created == 2) {
                clase = 'bg-red';
                texto = 'Pedido con error';
                icono = 'error'
                estadoHtml = `<div class="estado ${clase} text-white">
                  ${texto} <i class="material-icons">${icono}</i>
                </div>`;
            } else if (estado != null) {
                const {
                    clase,
                    texto,
                    icono
                } = estado;
                estadoHtml = `<div class="estado ${clase} text-white">
                  ${texto} <i class="material-icons">${icono}</i>
                </div>`;
            }

            return `<div>
                            <a id="toggle-button" data-toggle="modal" data-target="#descripcion" onclick="getOrderDetail('${order.id}','${order.type}', '${order.cliente}', '${order.order_id}', '${order.montoTotal}', '${order.delivery_method}', '${order.payment_method}', ${order.pos_notified}, ${order.pos_created}, '${order.telefono}', '${order.customer_document_number}', '${order.idPlat}', '${order.billing_name}', '${order.billing_phone}', '${order.billing_type}', '${order.billing_document_type}', '${order.billing_document_number}', '${order.billing_address}')">
                                <div class="row align-items-center mb-1">
                                    <div class="col-3">
                                        <img src="${img}"
                                            class="w-75 rounded-circle" style="aspect-ratio: 1/1">
                                    </div>
                                    <div class="col-9">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <p class="mb-0"><b>${order.order_id}</b></p>
                                            </div>
                                            <div style="position: absolute; top: 0; right: 10px;">
                                                <div class="d-flex" style="background-color: rgba(96, 125, 139, 1); color: white; border-radius: 8px !important; padding: 2px; margin: 2px;">
                                                    <p class="mb-0"><b>${order.hora}</b></p>
                                                    <i class="material-icons">store</i>
                                                </div>
                                                    <div class="d-flex" style="background-color: rgba(69, 90, 100, 1); color: white; border-radius: 8px !important; padding: 2px; margin: 2px;"><p class="mb-0"><b>${ order.delivery_method == 'delivery' ? order.billing_document_type != 'null' && order.billing_document_type != '' ?  order.billing_document_type : 'No asignado' : 'Cliente'}</b></p><i class="material-icons">${order.delivery_method == 'delivery' ?'delivery_dining' : 'local_mall'}</i></div>
                                            </div>
                                        </div>
                                        <p class="mb-0">${order.cliente}</p>
                                        <p class="mb-0">Tel.: ${order.telefono}</p>
                                        ${
                                            order.type == 'Uber' ?
                                            `<p class="mb-0">Ref.: ${order.customer_document_number}</p>` 
                                            : ''
                                        }
                                        <p class="mb-0 font-weight-bold">$${order.montoTotal}</p>
                                        <p class="mb-0 font-weight-bold">${order.sucursal}</p>
                                    </div>
                                </div>
                                ${estadoHtml}
                            </a>
                            <hr class="dropdown-divider">
                        </div>`;
        }

        function itemOrder(params) {
            var contenedor = document.createElement('div');
            var hr = document.createElement('hr');
            var subtotal = 0;
            var descuento = 0;
            hr.className = "dropdown-divider";
            contenedor.appendChild(itemRowOrder(params.cantidad, params.name, `$${params.precio}`));
            descuento = descuento + (parseInt(params.cantidad) * parseInt(params.descuento));
            subtotal = subtotal + (parseInt(params.precio) * parseInt(params.cantidad));
            if (params.toppings.length > 0) {
                contenedor.appendChild(itemRowOrder("", "Acompañamientos", ""));
                params.toppings.forEach(element => {
                    contenedor.appendChild(itemRowOrder(element.cantidad, element.name, `$${element.precio}`));
                    subtotal = subtotal + ((parseInt(element.precio) * parseInt(element.cantidad)));
                    descuento = descuento + (parseInt(element.cantidad) * parseInt(element.descuento));
                });
            }
            contenedor.appendChild(itemRowOrder("", "Descuento", `-$${descuento}`));
            contenedor.appendChild(itemRowOrder("", "Subtotal", `$${subtotal - descuento}`));
            contenedor.appendChild(hr);
            return contenedor;
        }

        function itemRowOrder(cantidad, nombre, precio) {
            var divRow = document.createElement('div');
            var divCant = document.createElement('div');
            var divDesc = document.createElement('div');
            var divPrec = document.createElement('div');
            if (nombre == 'Acompañamientos') {
                divDesc.className = "col-md-8 font-weight-bold";
            } else if (nombre == 'Subtotal' || nombre == 'Descuento') {
                divDesc.className = "col-md-8 font-weight-bold text-right";
            } else {
                divDesc.className = "col-md-8";
            }
            divRow.className = "row";
            divPrec.className = "col-md-3 text-right";
            divCant.className = "col-md-1";
            divCant.innerHTML = cantidad;
            divDesc.innerHTML = nombre;
            divPrec.innerHTML = precio;
            divRow.appendChild(divCant);
            divRow.appendChild(divDesc);
            divRow.appendChild(divPrec);

            return divRow;
        }
    </script>
@endsection
