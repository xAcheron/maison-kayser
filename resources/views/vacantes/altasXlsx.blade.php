@extends('layouts.pro')
@include('menu.vacantes', ['seccion' => 'empleados'])
@section('content')
    <style>
        .file-select {
            position: relative;
            display: inline-block;
        }

        .file-select::before {
            background-color: #00bcd4;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 3px;
            content: 'Seleccionar';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
        }

        .file-select input[type="file"] {
            opacity: 0;
            width: 200px;
            height: 32px;
            display: inline-block;
        }

        #src-file1::before {
            content: 'Examinar';
        }
    </style>
    <div class="card">
        <div class="card-body">
            <form id="formXlsx">
                <div class="d-flex align-items-center" style="gap: 10px">
                    <div class="file-select col-md-2 col-sm-12" id="src-file1">
                        <input type="file" id="file" class="" name="file" accept=".xlsx">
                    </div>
                    <label for="file" class="col-md-10 col-sm-12">
                        <div id="nombreArchivo" class="border d-flex align-items-center" style="height: 32px">
                            Seleccione un archivo
                        </div>
                    </label>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @if (in_array(Auth::id(), [1, 6]))
                    <div class="form-check mt-4">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" value="" id="inicial">
                            Primer carga
                            <span class="form-check-sign">
                                <span class="check"></span>
                            </span>
                        </label>
                    </div>
                @endif
                {{-- <div class="form-check mt-4">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" value="" id="duplicados"> Actualizar
                        duplicados
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                    </label>
                </div> --}}
                <button type="button" class="btn btn-success" id="upload">Cargar archivo</button>
                <a class="btn btn-success" href="/Laravel/resources/views/vacantes/PLANTILLA_EMPLEADOS_KAYSER.xlsx"
                    download="PLANTILLA_EMPLEADOS_KAYSER.xlsx" target="_blank">Descargar plantilla</a>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body" id="tableDiv">
        </div>
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
        $('#upload').on('click', function() {
            swal({
                title: 'Seguro que quieres subir el archivo?',
                html: 'Si aceptas el archivo se cargara en sistema',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: `Cancelar`,
            }).then((value) => {
                if (value.value) {
                    showLoading();
                    var data = new FormData();

                    data.append('file', $('#file').prop('files')[0])
                    data.append('_token', "{{ csrf_token() }}")
                    // data.append('duplicados', $('#duplicados').prop('checked'))
                    data.append('inicial', $('#inicial').prop('checked'))

                    $.ajax({
                        type: "POST",
                        url: "{{ route('altaXlsx') }}",
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        mimeType: "multipart/form-data",
                        success: function(msg) {
                            swal.close();
                            msg = JSON.parse(msg)

                            if (msg.success) {
                                $('#tableDiv').html(msg.msg);
                                swal('Terminado', 'El archivo se subio con exito!', 'success');
                            } else {
                                swal({
                                    type: 'error',
                                    title: 'Oops...',
                                    text: 'Algo ha salido mal!',
                                    footer: 'Problemas? sit@prigo.com.mx',
                                });
                            }
                        },
                        error: function(msg) {
                            console.log(msg)
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: 'Algo ha salido mal!',
                                footer: 'Problemas? sit@prigo.com.mx',
                            });
                        }
                    });
                }
            })
        })


        function showLoading() {
            swal({
                title: 'Guardando...',
                allowEscapeKey: false,
                allowOutsideClick: false,
                showCancelButton: false,
                showConfirmButton: false,
                text: 'Espere un momento...'
            });
        }

        document.getElementById('file').onchange = function() {
            document.getElementById('nombreArchivo').innerHTML = document.getElementById('file').files[0].name;
        }
    </script>
@endsection
