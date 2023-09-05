<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8" />
	<link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png" />
	<link rel="icon" type="image/png" href="../assets/img/favicon.png" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Intranet PRIGO</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta name="viewport" content="width=device-width" />
	<!-- Bootstrap core CSS     -->
    <link href="{{ asset('MaterialBSFull/css/bootstrap.min.css') }}" rel="stylesheet" />
	<!--  Material Dashboard CSS    -->
    <link href="{{ asset('MaterialBSFull/css/material-dashboard.css') }}?v=1.3.0" rel="stylesheet"/>
	<!--     Fonts and icons     -->
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons" />
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>

        .sidebar .nav li.active > a, .sidebar .nav li.active > a i{
                color: white !important;
            } 
        </style>
</head>
<body >
    <div class="wrapper">
        <div class="sidebar" data-active-color="orange" data-background-color="white" data-image="{{ asset('images/sidebar-1.jpg') }}">
            <div class="logo">
                <a href="http://intranet.prigo.com.mx" class="simple-text logo-mini">
                    <img src="{{ asset('images/logo_prigo_nano.png') }}">
                </a>
                <a href="http://intranet.prigo.com.mx" class="simple-text logo-normal">
                        PRIGO 
                </a>
            </div>
            <div class="sidebar-wrapper">
                <div class="user">
                    <div class="photo">
                        <img src="{{ asset('images/faces/baguette.png') }}" />
                    </div>
                    <div class="info">
                        <a data-toggle="collapse" href="#collapseExample" class="collapsed">
                            <span>
                                @if (!Auth::guest())
                                    {{ Auth::user()->name }}
                                @endif
                                <b class="caret"></b>
                            </span>
                        </a>
                        <div class="clearfix"></div>
                        <div class="collapse" id="collapseExample">
                            <ul class="nav">
                                <li>
                                    <a href="#">
                                        <span class="sidebar-mini"> MP </span>
                                        <span class="sidebar-normal"> Mi Perfil </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <span class="sidebar-mini"> EP </span>
                                        <span class="sidebar-normal"> Editar Perfil </span>
                                    </a>
                                </li>
                                <!--li>
                                    <a href="#">
                                        <span class="sidebar-mini"> S </span>
                                        <span class="sidebar-normal"> Settings </span>
                                    </a>
                                </li-->
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="nav">
                    <li id="appmenu" class="nav-item @if( url()->current() == "http://intranet.prigo.com.mx/home" ) active @endif">
                        <a href="{{ route('home') }}">
                            <i class="material-icons">dashboard</i>
                            <p> {{ empty($data['titulo'])?'Aplicaciones':$data['titulo']  }} </p>
                        </a>
                    </li>
                    @yield('appmenu')
                </ul>

            </div>
        </div>
        <div class="main-panel">
            <nav class="navbar navbar-transparent navbar-absolute">
                <div class="container-fluid">
                    <div class="navbar-minimize">
                        <button id="minimizeSidebar" class="btn btn-round btn-white btn-fill btn-just-icon">
                            <i class="material-icons visible-on-sidebar-regular">more_vert</i>
                            <i class="material-icons visible-on-sidebar-mini">view_list</i>
                        </button>
                    </div>
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#"> {{ empty($data['titulo'])?'Aplicaciones':$data['titulo']  }} </a>
                    </div>
                    <div class="collapse navbar-collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="material-icons">notifications</i>
                                    <span class="notification">0</span>
                                    <p class="hidden-lg hidden-md">
                                        Notificaciones
                                    <b class="caret"></b>
                                    </p>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="#">Sin Notificaciones</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="material-icons">person</i>
                                    <p class="hidden-lg hidden-md">Perfil</p>
                                </a>
                                <ul class="dropdown-menu" data-active-color="orange">
                                    @if (!Auth::guest())
                                    <li>
                                        <a href="/changePassword">
                                            Cambiar Password
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Salir
                                        </a>
                                    </li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                    @endif
                                </ul>
                            </li>
                            <li class="separator hidden-lg hidden-md"></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <nav class="pull-left">
                        <ul>
                            <li>  <a href="#">Eric Kayser </a> </li>
                            <li>  <a href="#">Carmela & Sal</a> </li>
                        </ul>
                    </nav>
                    <p class="copyright pull-right">
                        &copy; <script>document.write(new Date().getFullYear())</script> <a href="http://intranet.prigo.com.mx"> GRUPO PRIGO</a>
                    </p>
                </div>
            </footer>
        </div>
    </div>
</body>
<script src="{{ asset('MaterialBSFull/js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('MaterialBS/js/core/popper.min.js') }}"></script>
<script src="{{ asset('MaterialBSFull/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('MaterialBSFull/js/material.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('MaterialBSFull/js/perfect-scrollbar.jquery.min.js') }}" type="text/javascript"></script>
<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
<script src="{{ asset('MaterialBS/js/plugins/sweetalert2.js') }}"></script>
<!-- Library for adding dinamically elements -->
<script src="{{ asset('MaterialBSFull/js/arrive.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('MaterialBS/js/bootstrap-material-design.min.js') }}"></script>
@yield('jsimports')
<script src="{{ asset('MaterialBSFull/js/material-dashboard.js') }}?v=1.3.0"></script>
@yield('aditionalScripts')
</html>