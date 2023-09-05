<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        Intranet
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport' />
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <!-- CSS Files -->
    <link href="{{ asset('material_pro_2_1_0/assets/css/material-dashboard.css') }}?v=2.1.0" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project
  <link href="../../assets/demo/demo.css" rel="stylesheet" />-->
    <style>
        /* width */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #888;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .sidebar .nav li.active>a,
        .sidebar .nav li.active>a i {
            color: white !important;
        }

        @media (min-width: 991px) {
            .sidebar-mini .sidebar {
                display: block;
                font-weight: 200;
                z-index: 1200;
            }
        }
    </style>
</head>

<body class="">
    <div class="wrapper ">
        <div class="sidebar" style="overflow-x: hidden !important;" data-color="orange" data-background-color="white"
            data-image="{{ asset('images/sidebar-1.jpg') }}">
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
                    <div class="user-info">
                        <a data-toggle="collapse" href="#collapseExample" class="username">
                            <span>
                                @if (!Auth::guest())
                                    {{ Auth::user()->name }}
                                @endif
                                <b class="caret"></b>
                            </span>
                        </a>
                        <div class="collapse" id="collapseExample">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <span class="sidebar-mini"> MP </span>
                                        <span class="sidebar-normal"> Mi Perfil </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <span class="sidebar-mini"> EP </span>
                                        <span class="sidebar-normal"> Editar Perfil </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <ul class="nav">
                    <li id="appmenu" class="nav-item @if (url()->current() == 'http://intranet.prigo.com.mx/home') active @endif">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="material-icons">dashboard</i>
                            <p> {{ empty($data['titulo']) ? 'Aplicaciones' : $data['titulo'] }} </p>
                        </a>
                    </li>
                    @yield('appmenu')
                </ul>

            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-minimize">
                            <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                                <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                                <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
                            </button>
                        </div>
                        <a class="navbar-brand" href="#pablo">Intranet</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse"
                        aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                        <span class="navbar-toggler-icon icon-bar"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="#pablo">
                                    <i class="material-icons">dashboard</i>
                                    <p class="d-lg-none d-md-block">
                                        Stats
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="https://intranet.prigo.com.mx" id="navbarDropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="material-icons">notifications</i>
                                    <span class="notification">0</span>
                                    <p class="d-lg-none d-md-block">
                                        Some Actions
                                    </p>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right"
                                    aria-labelledby="navbarDropdownMenuLink">
                                    <a class="dropdown-item" href="#">Sin Notificaciones</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                @if (!Auth::guest())
                                    <a class="nav-link" href="#pablo" id="navbarDropdownProfile"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="material-icons">person</i>
                                        <p class="d-lg-none d-md-block">
                                            Account
                                        </p>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="navbarDropdownProfile">
                                        <a class="dropdown-item" href="/changePassword">Cambiar Password</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('logout') }}">Salir</a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <nav class="pull-left">
                        <ul>
                            <li> <a href="#">Eric Kayser </a> </li>
                            <li> <a href="#">Carmela & Sal</a> </li>
                        </ul>
                    </nav>
                    <p class="copyright pull-right">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script> <a href="http://intranet.prigo.com.mx"> GRUPO PRIGO</a>
                    </p>
                </div>
            </footer>
        </div>
    </div>

    <!--   Core JS Files   -->

    <script src="{{ asset('material_pro_2_1_0/assets/js/core/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/core/popper.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/core/bootstrap-material-design.min.js') }}" type="text/javascript">
    </script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/material-dashboard.js') }}?v=2.1.0" type="text/javascript">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js" type="text/javascript"></script>
    <script src="{{ asset('material_pro_2_1_0/assets/js/plugins/sweetalert2.js') }}" type="text/javascript"></script>

    @yield('jsimports')
    @yield('aditionalScripts')
</body>

</html>
