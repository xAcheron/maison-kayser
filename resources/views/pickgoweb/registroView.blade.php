@extends('layouts.appPickGoLayout')
@include('menu.pickGoMenuView', ['seccion' => 'registro'])
@section('content')
<div class="maincontent pt-0 pb-0">
    <div class="d-md-flex h-md-100 align-items-center">
        <div class="col-md-6 p-0 h-md-100">
            <div class="block hero2 my-auto" style="background-image:url(https://intranet.prigo.com.mx/storage/pickgo/web/reformaimg.jpg);">
                <div class="container-fluid text-center">
                    <h1 class="display-2 text-white" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-offset="0">Registro <br> Fácil y rápido</h1>
                    <p class="lead text-white" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-offset="0">Maison Kayser</p>
                    <p class="lead text-white" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-offset="0">- Pick & Go -</p>
                    <hr>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="col-md-6 p-0 h-md-100 loginarea">
            <div class="d-md-flex align-items-center h-md-100 p-3 justify-content-center">
                <div class="col-md-8">
                    <h3 class="mb-4 text-center">Registrarte</h3>
                    <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" id="InputNombreRegistro" placeholder="Nombre" required="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" id="InputApellidoRegistro" placeholder="Apellido" required="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="email" class="form-control" id="InputEmailRegistro" placeholder="Correo electrónico" required="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form-control" id="InputCelularRegistro" placeholder="Número de celular" required="" maxlength="10">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="InputPasswordRegistro"placeholder="Contraseña" required="">
                    </div>
                       <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="CheckTerminos">
                        <label class="form-check-label small text-muted" for="CheckTerminos"><a href="https://www.maison-kayser.com.mx/terminos.html" target="_blank">Acepto aviso de privacidad</a></label>
                    </div>
                       <button id="btnRegistro" type="button" class="btn btn-dark btn-round btn-block">Registrarte</button> 
                   </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalQuickView" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static"   >  
    <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Maison Kayser Pick &amp; Go</h5>
        </div>
        <div class="modal-body">
                <center><h6>¡Tu cuenta fue creada exitosamente!</h6></center>
                <h6>Para concluir, ingresa el código de verificación enviado a tu email.</h6>
                <div class="form-group">
                    <input type="hidden" id="InputCodigoListo"  maxlength="4">
                    <input type="text" class="form-control" id="InputCodigoRecibido"  required="" maxlength="4">
                </div>
        </div>
        <div class="modal-footer"><button id="btnVerificar" type="button" class="btn btn-dark btn-round btn-block">Verificar</button> </div>
    </div>
    </div>
</div>

<div class="modal fade" id="modalLoading" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static"   >  
    <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Maison Kayser Pick &amp; Go</h5>
        </div>
        <div class="modal-body">
                <center><p><i class="fa fa-spin fa-spinner"></i> Enviando datos...</p></center>
        </div>
    </div>
    </div>
</div>
@endsection
@section('localeScripts')
<script type="text/javascript">
    let statusTerminos = false;
  
    $('input[type="checkbox"]').click(function(){
        if($(this).prop("checked") == true){
            statusTerminos = true;           
        }
        else if($(this).prop("checked") == false){
            statusTerminos = false;
        }
    });

    $(document).on('click', '#btnVerificar', function(){
        var codigoRecibido = $('#InputCodigoListo').val();
        var codigoInsertado = $('#InputCodigoRecibido').val();
        if(codigoRecibido == codigoInsertado){
            window.location.href = "{{route('indexPickGo')}}";
        }else{
            alertCodigoValido();
        }
    });

    $('#btnRegistro').click(function() {

        var nombre = $('#InputNombreRegistro').val();
        var apellido = $('#InputApellidoRegistro').val();
        var email = $('#InputEmailRegistro').val();
        var celular = $('#InputCelularRegistro').val();
        var password = $('#InputPasswordRegistro').val();

        if(nombre != '' && apellido != '' && email != '' && celular != '' && password != ''){
            if(statusTerminos == true){
                $('#modalLoading').modal({
                    backdrop: 'static',
                    keyboard: false  
        });
        $("#modalLoading").modal('show');

            $.ajax({
            type: "GET",
            url: "{{route('saveUserPickGo')}}",
            data: ({ nombre: nombre, apellido:apellido,email:email,celular:celular,password: password }),
                success: function(data){
                    $("#modalLoading").modal('hide');
                    var codeReturn = data.code_validation;
                    validacionCodigoVer(codeReturn);
                },
                error: function(msg){
                    validacionResponseError(msg.responseJSON.info_status);     
                }
            });
            }else if(statusTerminos == false){
                alertAvisoPrivacidad();
            }
        } else{
            alertCompletar();
        }
    });


//Alertas
    function alertCompletar()
    {
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Ingresa todos los datos para completar el registro.',
        });
    }

    function alertAvisoPrivacidad()
    {
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
             message: 'Acepta el aviso de privacidad para continuar.',
        });
    }

    function alertCodigoValido()
    {
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
             message: 'Ingresaste un código no valido, verificalo.',
        });
    }

//Validaciones
    function validacionResponseError(status)
    {
    switch(status) {
        case 'registered_mail':
            bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Ya te encuentras registrado.',
            });
        break;
        case 'user_no_register':
            bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'No te encuentras registrado, hazlo ahora.',
            });
        break;
        case 'password_no_validate':
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Contraseña incorrecta.',
                });
        break;
        }
    }

    function validacionCodigoVer(codeReturn)
    {
    $('#modalQuickView').modal({
            backdrop: 'static',
            keyboard: false  
        });

    $(".modal-body #InputCodigoListo").val(codeReturn);
    $("#modalQuickView").modal('show');
    }
    
</script>
@endsection
