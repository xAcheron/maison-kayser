@section('menuPickGo')
<nav class="navbar navbar-expand-md navbar-light fixed-top bg-white"> 
    <a class="navbar-brand" href="{{ route('indexPickGo') }}"><img src="https://www.maison-kayser.com.mx/img/maison-kayser_small.png"></a>
    <button
    class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarpickgo" aria-controls="navbarpickgo" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
    </button>
        <div class="collapse navbar-collapse" id="navbarpickgo">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item @if($seccion == 'index') active @endif"><a class="nav-link" href="{{ route('indexPickGo') }}">Inicio</a></li>
                @if (empty($idUser))
                <li class="nav-item @if($seccion == 'login') active @endif"><a class="nav-link" href="{{ route('loginPickGo') }}">Inicia sesión</a></li>
                <li class="nav-item @if($seccion == 'registro') active @endif"><a class="nav-link" href="{{ route('registerPickGo') }}">Registrarte</a></li>
                @else
                <li class="nav-item @if($seccion == 'menu') active @endif"><a class="nav-link" href="{{ route('menuPickGo') }}">Menú</a></li>
                <li class="nav-item @if($seccion == 'pedidos') active @endif"><a class="nav-link" href="{{ route('getOrdersView') }}">Pedidos</a></li>
                @endif     
            </ul> 
            <ul class="navbar-nav">
                @if (empty($idUser))
                @else
                <li class="nav-item active"><button type="button" id="btnCesta" class="btn btn-outline-primary btn-round btn-block">Mi cesta <i class="fa fa-shopping-basket"></i></button></li>
                &nbsp;
                <li class="nav-item active"><button type="button" id="btnLogout" class="btn btn-outline-secondary btn-round btn-block">Salir</button></li>
                @endif 
            </ul>
        </div>
</nav>
@endsection
