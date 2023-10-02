@extends('layouts.appPickGoLayout')
@section('content')
<main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
      <div class="card login-card">
        <div class="row no-gutters">
          <div class="col-md-5">
            <img src="https://intranet.prigo.com.mx/storage/pickgo/web/reformaimg.jpg" alt="login" class="login-card-img">
          </div>
          <div class="col-md-7">
            <div class="card-body">
              <div class="brand-wrapper">
                <img src="https://intranet.prigo.com.mx/BS4/img/logo.png" alt="logo" class="logo">
              </div>
              <p class="login-card-description">Iniciar sesión</p>
              <form action="#!">
                  <div class="form-group">
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="InputEmailLogin" class="form-control" placeholder="Email address">
                  </div>
                  <div class="form-group mb-4">
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="InputPasswordLogin" class="form-control" placeholder="***********">
                  </div>
                  <input name="login" id="btnLogin" onclick="login()" class="btn btn-block login-btn mb-4" type="button" value="Login">
                </form>
                <a href="{{route('getForgetPass')}}" class="forgot-password-link">¿Olvidaste tu contraseña?</a>
                <p class="login-card-footer-text">¿Aun no tienes una cuenta? <a href="#!" class="text-reset">Registrate aqui</a></p>
                <nav class="login-card-footer-nav">
                  <a href="#!">Terminos y condiciones</a>
                  <a href="#!">Politica de Privacidad</a>
                </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
</main>
@endsection
@section('localScripts')
<script src="{{ asset('js/bootbox.js') }}"></script>
<script>

function login()
{
    
    var emailLogin = $('#InputEmailLogin').val();
    var passLogin = $('#InputPasswordLogin').val();

    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            window.location.href = "{{route('indexPickGo')}}";
        }
        else
        {
            var msg = JSON.parse(this.responseText);
            switch(msg.responseJSON.info_status) {
                case 'email_invalid':
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
                case 'password_no_validate':
                bootbox.alert({
                    title: 'Maison Kayser Pick & Go',
                    message: 'Contraseña incorrecta.',
                });
                break;
            }
        }
    };

    xhttp.open("GET", "{{ route('loginUserPickGo') }}?email="+ emailLogin +"&password="+passLogin, true);

    xhttp.send();

}
</script>
@endsection
