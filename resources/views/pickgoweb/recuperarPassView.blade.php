@extends('layouts.appPickGoLayout')
@section('content')
<div class="maincontent pt-0 pb-0">
            <div class="d-md-flex align-items-center h-md-100 p-3 justify-content-center">
                <div class="col-md-4">
                    <div>
                        <h3 class="mb-4 text-center">Recuperar contraseña</h3>
                       <div class="form-group">
                           <input type="email" class="form-control" id="InputEmailLoginRecovery" aria-describedby="emailHelp"
                           placeholder="Correo electrónico" required="">
                       </div>
                       <button id="btnRecuperar" type="button"  class="btn btn-dark btn-round btn-block">Recuperar</button> <small class="d-block mt-4 text-center"></small>
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
    var emailRecuperar = $('#InputEmailLoginRecovery').val();
    if(emailRecuperar != ''){
        $('#modalLoadingRecovery').modal({
            backdrop: 'static',
            keyboard: false  
        });
        $("#modalLoadingRecovery").modal('show');
        $.ajax({
            type: "GET",
            url: "{{route('sendEmailForgetPass')}}",
            data: ({ email: emailRecuperar}),
                success: function(data){
                    $("#modalLoadingRecovery").modal('hide');
                    window.location.href = "{{ route('validationCodeNewPass') }}"+'/'+data.code_validation+'/'+data.email;
                },
                error: function(msg){
                    $('#modalLoadingRecovery').modal('hide'); 
                    switch(msg.responseJSON.info_status) {
                        case 'email_invalidate':
                        bootbox.alert({
                            title: 'Maison Kayser Pick & Go',
                            message: 'Ingresa un email valido.',
                        });
                        break;
                        case 'user_no_register':
                        bootbox.alert({
                            title: 'Maison Kayser Pick & Go',
                            message: 'No te encuentras registrado, hazlo ahora.',
                        });
                        break;
                    }      
                  
                }
            });
    }else{
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Ingresa el correo electrónico del que deseas recuperar la contraseña.',
        });
    }
});

});


</script>
@endsection