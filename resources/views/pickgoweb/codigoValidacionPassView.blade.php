@extends('layouts.appPickGoLayout')
@section('content')
<div class="maincontent pt-0 pb-0">
            <div class="d-md-flex align-items-center h-md-100 p-3 justify-content-center">
                <div class="col-md-4">
                    <div>
                        <h3 class="mb-4 text-center">Recuperar contraseña</h3>
                        <h6 class="mb-4 text-center">Ingresa el código de verificación que fue enviado a la direccón de correo electrónico ingresada anteriormente y la nueva contraseña.</h6>
                        <div class="form-group">
                            <input type="text" class="form-control" id="InputCodigoIngresado" placeholder="Código de verificación" required="" maxlength="4">
                        </div>
                       <div class="form-group">
                           <input type="hidden" class="form-control" id="InputCodigoGenerado" value="{{ $codigoValidacion }}" required="">
                           <input type="hidden" class="form-control" id="emailCliente" value="{{ $email }}" required="">
                           <input type="password" class="form-control" id="InputNewPass" placeholder="Nueva contraseña" required="" maxlength="12">
                       </div>
                       <div class="form-group">
                            <button id="btnRecuperar" type="button"  class="btn btn-dark btn-round btn-block">Recuperar </button> <small class="d-block mt-4 text-center"></small>
                       </div>
                   </div>
                </div>
            </div>
</div>
<div class="modal" id="modalLoadingRecovery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"  >  
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
<script>
$(document).ready(function(){
    $('#btnRecuperar' ).click(function() {
    var codigoIngresado = $('#InputCodigoIngresado').val();
    var codigoGenerado = $('#InputCodigoGenerado').val();
    var emailRecuperar = $('#emailCliente').val();
    var newPass = $('#InputNewPass').val();

    if(emailRecuperar != '' && codigoGenerado != '' && codigoIngresado != '' && newPass  != '' ){
        $('#modalLoadingRecovery').modal({
            backdrop: 'static',
            keyboard: false  
        });
        $("#modalLoadingRecovery").modal('show');
        $.ajax({
            type: "GET",
            url: "{{route('saveNewPassPickGo')}}",
            data: ({ codigoIngresado: codigoIngresado, codigoGenerado: codigoGenerado , emailRecuperar:emailRecuperar, newPass:newPass}),
                success: function(data){
                    $("#modalLoadingRecovery").modal('hide');
                    if(data.success == true && data.info_status == 'update_pass_user'){
                        bootbox.alert({
                            title: 'Maison Kayser Pick & Go',
                            message: 'Se actualizó la nueva contraseña con éxito, ahora puedes iniciar sesión.',
                        });
                        window.setTimeout(function() {
                            window.location.href = "{{ route('succesNewPass') }}";
                        }, 2000);
                    }
                },
                error: function(msg){
                    $('#modalLoadingRecovery').modal('hide'); 
                    console.log(msg);
                    switch(msg.responseJSON.info_status) {
                        case 'code_bad':
                        bootbox.alert({
                            title: 'Maison Kayser Pick & Go',
                            message: 'El código de verificación es incorrecto.',
                        });
                        break;
                        case 'error_update':
                        bootbox.alert({
                            title: 'Maison Kayser Pick & Go',
                            message: 'Hubo un error, intentalo de nuevo.',
                        });
                        break;
                    }      
                  
                }
            });
    }else{
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Ingresa el todos los datos para recuperar la contraseña.',
        });
    }
});

});


</script>
@endsection