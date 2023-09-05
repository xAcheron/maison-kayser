@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'micros'])
@section('content')
    <div class="card" style="height: calc(100% - 200px); hidden; position: absolute; width: calc(100% - 100px);">
        <div class="card-body" id="cardBody" style="overflow-y: hidden;">
            <div class="row h-100">
                <div class="col h-100" id="tableEmpDiv" >
                    <div style="overflow-y: scroll; overflow-x:unset; height: 95%;">
                        <table class="table table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th colspan="3"><input type="text" class="form-control my-1" placeholder="Nombre"
                                        id="searchEmpleado"></th>
                                    </tr>
                                    <tr class="sticky-top" style="background: white; top: -1px">
                                        <th class="font-weight-bold pl-2" colspan="3" scope="col">Empleado Intranet</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                </tr>
                            </thead>
                            <tbody id="tableBodyEmpleados">
                                
                            </tbody>
                        </table>
                    </div>
                     <nav style="margin: 0 auto; width: min-content;" >
                        <ul class="pagination m-0" id="paginationEmp">  
                        </ul>
                    </nav>
                </div>
                <div class="col h-100" id="tablePerDiv">
                    <div style="overflow-y: scroll; overflow-x:clip; height: 95%;">
                        <table class="table table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th colspan="5">
                                        <div class="d-flex align-items-center">
                                            <div class="col-11">
                                                <input type="text" class="form-control p-0" placeholder="Nombre"
                                                id="searchPerfil">
                                            </div>
                                            <div class="col-1 p-0">
                                                <button type="button" class="btn btn-just-icon btn-link" id="filtrosPer">
                                                    <i class="material-icons">settings</i>
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                <tr class="sticky-top" style="background: white; top: -1px">
                                    <th class="font-weight-bold pl-2" colspan="5">Perfiles Micros</th>  
                                </tr>
                                <tr class="sticky-top" style="background: white; top:25px"> 
                                    <th colspan="2">Nombre</th>
                                    <th>Sucursal</th>
                                    <th>Contratacion</th>
                                    <th>Nacimiento</th>
                                </tr>
                            </thead>
                            <tbody id="tableBodyPerfiles">
                                
                            </tbody>
                        </table>
                    </div>
                    <nav style="margin: 0 auto; width: min-content;" >
                        <ul class="pagination m-0" id="paginationPer">  
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <div class="card-footer">
            
            <div>
                <a class="btn btn-info text-white" id="btnAgrupar">Agrupar</a>
                <a class="btn btn-white" id="btnCrear">Crear</a>
            </div>
        </div>
    </div>


@endsection
@section('aditionalScripts')

     <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

    <script>
        // var empleados = {!! json_encode($empleados) !!};
        var empleados = [];
        var perfiles = [];
        var nacimiento = "1900-01-01 - {{ date('Y-m-d') }}";
        var contratacion = "1900-01-01 - {{ date('Y-m-d') }}";
        var sucursal = '';
        var queryPer = '';
        var query = '';
        var offset = 0;
        var timer;

        $(document).ready(()=>{
            obtenerPerfiles('', '', '', '');    
            obtenerEmpleados('');
        })

        function siguientePag(value, tabla) {
            offset = value;
            if(tabla == 2){
                obtenerPerfiles(nacimiento, contratacion, sucursal, queryPer);
            }else if(tabla == 1){
                obtenerEmpleados(query);
            }
        }

        function obtenerPerfiles(nacimiento, contratacion, sucursal, nombre) {

            var params = {
                nacimiento: nacimiento,
                contratacion: contratacion,
                sucursal: sucursal,
                nombre: nombre,
                offset: offset,
                _token: "{{csrf_token()}}"
            }

             $.ajax({
                type: "POST",
                url: "{{ route('getPerfilesMicros') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                        var nav = $('#paginationPer');
                        nav.html('');
                        if(msg.paginaActual != 0){
                            nav.append(`<li class="page-item"><a class="page-link" onclick="siguientePag(${offset - 20}, 2)">Atras</a></li>`);
                        }
                            for (let i = 0; i < 3 && i + msg.paginaActual < msg.paginas; i++) {
                                nav.append(`<li class="page-item ${i + msg.paginaActual == msg.paginaActual ? 'active' : ''}" aria-current="page">
                                                <a class="page-link" onclick="siguientePag(${(msg.paginaActual + i) * 20}, 2)">${i + 1 + msg.paginaActual}</a>
                                            </li>`)
                            }

                        if(msg.paginas > 3 && msg.paginaActual < msg.paginas - 3){
                            nav.append(` <li class="page-item">
                                            ...
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" onclick="siguientePag(${(msg.paginas - 1) * 20}, 2)">${msg.paginas}</a>
                                        </li>`);
                        }
   
                        if(msg.paginas - 1 != msg.paginaActual){
                            nav.append(`<li class="page-item"><a class="page-link" onclick="siguientePag(${offset + 20}, 2)">Siguiente</a></li>`);
                        }

                            
                        var htmlTemplate = `<tr>
                                                <td><input type="checkbox" class="form-control" name="perfiles"
                                                        id="perfiles-:id" style="height: 13px"
                                                        value=":id"></td>
                                                <td class="text-left pl-2 p-0"><label for="perfiles-:id"
                                                        class="w-100 m-0">:name</label></td>
                                                <td class="text-left pl-2"><label for="perfiles-:id"
                                                        id="label-:id" class="w-100 m-0">:sucursal</label></td>
                                                <td class="text-left pl-2"><label for="perfiles-:id"
                                                        id="label-:id" class="w-100 m-0">:hire</label></td>
                                                <td class="text-left pl-2"><label for="perfiles-:id"
                                                        id="label-:id" class="w-100 m-0">:birth</label></td>
                                            </tr>`;
                        buscar(msg.data, '', '#tableBodyPerfiles', htmlTemplate);
                    } else {
                        swal(msg.msg, msg.msg2 ?? "", 'error');
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        }


          function obtenerEmpleados(nombre) {

            var params = {
                nombre: nombre,
                offset: offset,
                _token: "{{csrf_token()}}"
            }

             $.ajax({
                type: "POST",
                url: "{{ route('getEmpleadosMicros') }}",
                data: params,
                success: function(msg) {
                    if (msg.success) {
                    
                        var nav = $('#paginationEmp');
                        nav.html('');
                        if(msg.paginaActual != 0){
                            nav.append(`<li class="page-item"><a class="page-link" onclick="siguientePag(${offset - 20}, 1)">Atras</a></li>`);
                        }
                            for (let i = 0; i < 3 && i + msg.paginaActual < msg.paginas; i++) {
                                nav.append(`<li class="page-item ${i + msg.paginaActual == msg.paginaActual ? 'active' : ''}" aria-current="page">
                                                <a class="page-link" onclick="siguientePag(${(msg.paginaActual + i) * 20}, 1)">${i + 1 + msg.paginaActual}</a>
                                            </li>`)
                            }

                        if(msg.paginas > 3 && msg.paginaActual < msg.paginas - 3){
                            nav.append(` <li class="page-item">
                                            ...
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" onclick="siguientePag(${(msg.paginas - 1) * 20}, 1)">${msg.paginas}</a>
                                        </li>`);
                        }
   
                        if(msg.paginas - 1 != msg.paginaActual){
                            nav.append(`<li class="page-item"><a class="page-link" onclick="siguientePag(${offset + 20}, 1)">Siguiente</a></li>`);
                        }
                                             
                        empleados = msg.data;
                          generarTablaEmp('');
                    } else {
                        swal(msg.msg, msg.msg2 ?? "", 'error');
                    }
                },
                error: function() {
                    console.log("error");
                }
            });
        }

        $('#btnCrear').on('click', function() {
            var perfilesCheck = $('[name="perfiles"]:checked')
            var idPerfiles = [];
            var nombre = "";
            if (perfilesCheck.length > 0) {
                for (i = 0; i < perfilesCheck.length; i++) {
                    idPerfiles.push(perfilesCheck[i].value)
                    var nuevoNombre = $(`#label-${perfilesCheck[i].value}`).html();
                    if (nombre.length < nuevoNombre.length) {
                        nombre = $(`#label-${perfilesCheck[i].value}`).html()
                    }
                }
                
                swal({
                    title: 'Seleccione un puesto',
                    html: `<div class="text-left">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" id="nombre" name="nombre" value="${nombre}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="puesto">Puesto</label>
                                <select name="puesto" id="puesto" class="form-control">
                                    <option value="" disabled selected>Selecciona un puesto</option>
                                    @foreach ($puestos as $item)
                                        <option value="{{ $item->idPuesto }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="puesto">Sucursal</label>
                                <select name="sucursal" id="sucursal" class="form-control">
                                    <option value="" disabled selected>Selecciona una sucursal</option>
                                    @foreach ($sucursales as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>`,
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Continuar'
                }).then((value) => {

                    if(value.value){
                    var sucursal = $('#sucursal').val();
                    nombre = $('#nombre').val();
                    puesto =  $('#puesto').val();
                    swal({
                        title: 'Desea continuar?',
                        html: 'Antes de crar al empleado asegurese que no esta creado',
                        type: 'warning',
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Continuar'
                    }).then((value) => {
                        if (value.value) {
                            var params = {
                                sucursal: sucursal,
                                puesto: puesto,
                                nombre: nombre,
                                idPerfiles: idPerfiles,
                                _token: "{{ csrf_token() }}"
                            }

                            $.ajax({
                                type: "POST",
                                url: "{{ route('crearEmpleadoPerf') }}",
                                data: params,
                                success: function(msg) {
                                    if (msg.success) {
                                        swal(msg.msg, '', 'success');
                                        idPerfiles.forEach((e) => $(`#row-${e}`)
                                            .remove())
                                        perfiles = perfiles.filter((item) => !idPerfiles
                                            .includes(
                                                item.id
                                                .toString()))
                                        empleados.push(msg.empleado)
                                        generarTablaEmp("");
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
                })
                
            }else{
                swal({
                    title: 'Datos insuficientes',
                    html: 'Debes seleccionar por lo menos un perfil',
                    type: 'error'
                })
            }



        })

        $('#btnAgrupar').on('click', function() {
            var idEmpleado = $('[name="empleado"]:checked').val()
            var perfilesCheck = $('[name="perfiles"]:checked')
            var idPerfiles = [];

            if (perfiles.length > 0 && idEmpleado != null) {


                for (i = 0; i < perfilesCheck.length; i++) {
                    idPerfiles.push(perfilesCheck[i].value)
                }

                var params = {
                    idEmpleado: idEmpleado,
                    idPerfiles: idPerfiles,
                    _token: "{{ csrf_token() }}"
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('agruparPerfilesEmp') }}",
                    data: params,
                    success: function(msg) {
                        if (msg.success) {
                            swal(msg.msg, '', 'success');
                            idPerfiles.forEach((e) => $(`#row-${e}`).remove())
                            perfiles = perfiles.filter((item) => !idPerfiles.includes(item.id
                                .toString()))
                        } else {
                            swal(msg.msg, msg.msg2 ?? "", 'error');
                        }
                    },
                    error: function() {
                        console.log("error");
                    }
                });
            }else{
                swal({
                    title: 'Datos insuficientes',
                    html: 'Debes seleccionar un empleado y por lo menos un perfil',
                    type: 'error'
                })
            }

        })

        $('#searchEmpleado').on('keyup', function() {
            query = $(this).val();
            clearTimeout(timer);

            timer = setTimeout(() => {
                obtenerEmpleados(query);
            }, 500);
        })

        function generarTablaEmp(query) {
            var htmlTemplate = `<tr>
                                    <td class="p-0">
                                        <input type="radio" name="empleado" id="item-:id"
                                            value=":id" class="form-control" style="height: 13px">
                                    </td>
                                    <td class="text-left p-0 pl-2 w-25" style="max-width: 5% !important"
                                        for="item-:id">:id
                                    </td>
                                    <td class="text-left pl-2 p-0"><label for="item-:id"
                                            class="w-100 m-0">:name</label></td>
                                </tr>`;

            buscar(empleados, '', '#tableBodyEmpleados', htmlTemplate);
        }

        $('#searchPerfil').on('keyup', function() {
            queryPer = $(this).val();
            clearTimeout(timer);
            
            timer = setTimeout(() => {
                obtenerPerfiles(nacimiento, contratacion, sucursal, queryPer);
            }, 500);
        })

        function buscar(array, query, id, template) {
            $(id).html('');
            if (query.length > 3) {
                array.map((value) => {
                    if (value.nombre.toLowerCase().includes(query.toLowerCase()) || (query == '#tableBodyPerfiles' && value.checkName.toLowerCase().includes(query.toLowerCase()))) {
                        var html = template;
                        html = html.replaceAll(':id', value.id);
                        html = html.replaceAll(':name', value.nombre);
                        if(id == '#tableBodyPerfiles'){
                            console.log(value)
                            html = html.replaceAll(':sucursal', value.sucursal);
                            html = html.replaceAll(':hire', value.HireDate);
                            html = html.replaceAll(':birth', value.DateofBirth);
                        }
                        $(id).append(html);
                    }
                })
            } else {
                array.map((value) => {
                    var html = template;
                    html = html.replaceAll(':id', value.id);
                    html = html.replaceAll(':name', value.nombre);
                    if(id == '#tableBodyPerfiles'){
                            html = html.replaceAll(':sucursal', value.sucursal);
                            html = html.replaceAll(':hire', value.HireDate);
                            html = html.replaceAll(':birth', value.DateofBirth);
                        }
                    $(id).append(html);
                })
            }
        }

        $('#filtrosPer').on('click', function () {
            swal.fire({
                title: 'Filtrar Perfiles Micros',
                html: ` <form action="POST" id="formFilPer">
                            <div class="d-flex align-items-end">
                                <div class="form-group col-10">
                                    <label for="sucursal">Sucursal</label>
                                    <select name="sucursal" id="sucursal" class="select2-item" style="width: 100%">
                                        <option value="">Seleccione una sucursal</option>
                                        @foreach ($sucursales as $item)
                                        <option value="{{ $item->idSap }}">{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2 p-0">
                                    <a class="btn btn-sm btn-danger text-white" onClick="vaciarFiltros('select2-item')">Limpiar</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-end">
                                <div class="form-group col-10">
                                    <label for="fechaNac">Fecha Nacimiento</label>
                                    <input type="text" class="filter-components" style="width:100%;" name="nacimiento" id="fechaNac" value="1900-01-01 - {{ date('Y-m-d') }}" />
                                </div>
                                <div class="col-2 p-0">
                                    <a class="btn btn-sm btn-danger text-white" onClick="vaciarFiltros('fechaNac')">Limpiar</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-end">
                                <div class="form-group col-10">
                                    <label for="fechaCon">Fecha Contratacion</label>
                                    <input type="text" class="filter-components" style="width:100%;" name="contratacion" id="fechaCon" value="1900-01-01 - {{ date('Y-m-d') }}" />
                                </div>
                                 <div class="col-2 p-0">
                                    <a class="btn btn-sm btn-danger text-white" onClick="vaciarFiltros('fechaCon')">Limpiar</a>
                                </div>
                            </div>
                        </form>`,
                onBeforeOpen: ()=>{

                    $('#fechaNac').val(nacimiento);
                    $('#fechaCon').val(contratacion);
                    $('#sucursal').val(sucursal);

                    $('.select2-item').select2({
                        dropdownParent: $('#swal2-content')
                        });

                    $('.filter-components').daterangepicker({
                            "showDropdowns": true,
                            opens: 'right',
                            minYear: 2019,
                            maxYear: "{{ date('Y') }}",
                            locale: {
                                format: 'YYYY-MM-DD'
                            },
                            ranges: {
                                'Today': [moment(), moment()],
                                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                                'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                                'This Month': [moment().startOf('month'), moment().endOf('month')],
                                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                                    'month').endOf(
                                    'month')]
                            }
                        });
                },
                confirmButtonText: 'Filtrar',
            }).then((value) => {
                console.log(value)
                if(value.value){
                    nacimiento = $('#fechaNac').val();
                    contratacion = $('#fechaCon').val();
                    sucursal = $('#sucursal').val();

                    obtenerPerfiles(nacimiento, contratacion, sucursal,queryPer)
                }else{

                }
            })
        })

        function vaciarFiltros(id) {
            if(id.includes('fecha')){
                $(`#${id}`).val('1900-01-01 - {{ date('Y-m-d') }}');
            }else{
                $(`.${id}`).val('').trigger('change');
            }
        }

    </script>
@endsection
