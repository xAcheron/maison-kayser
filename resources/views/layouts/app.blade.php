<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!-- Favicons -->
    <link rel="apple-touch-icon" href="MaterialBS/img/apple-icon.png" />
    <link rel="icon" href="MaterialBS/img/favicon.png" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Intranet Prigo') }}</title>

    {{-- <script src="https://cdn.tiny.cloud/1/1fz0bsn6ahmtvfy6hkkijqelq1kb7zkm3j0hy20fzwtn21rg/tinymce/5/tinymce.min.js"
referrerpolicy="origin"></script>
<script src="https://cdn.tiny.cloud/1/abcd1234/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> --}}

    <!-- Styles -->
    @include('shared.styles')


</head>

<body>
    <div class="wrapper">
        @if (Auth::id() != null)
            <div class="sidebar" data-color="orange" data-background-color="white" data-image="images/sidebar-1.jpg">
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
                            <img src="images/faces/baguette.png">
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
                                            <span class="sidebar-normal"> My Profile </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">
                                            <span class="sidebar-mini"> EP </span>
                                            <span class="sidebar-normal"> Edit Profile </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">
                                            <span class="sidebar-mini"> S </span>
                                            <span class="sidebar-normal"> Settings </span>
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
                <div class="sidebar-background"
                    style="background-image: url({{ asset('MaterialBS/img/sidebar-1.jpg') }} ) "></div>
            </div>
        @endif

        <div class="main-panel">
            @if (Auth::id() != null)
                <nav class="navbar navbar-expand-lg navbar-transparent  navbar-absolute fixed-top">
                    <div class="container-fluid">
                        <div class="navbar-wrapper">
                            <div class="navbar-minimize">
                                <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                                    <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                                    <i
                                        class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
                                </button>
                            </div>
                            <a class="navbar-brand" href="#">Aplicaciones</a>
                        </div>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
                            aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link" href="https://creative-tim.com" id="navbarDropdownMenuLink"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="material-icons">notifications</i>
                                        <span class="notification">0</span>
                                        <p>
                                            <span class="d-lg-none d-md-block">Some Actions<b class="caret"></b></span>
                                        </p>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="navbarDropdownMenuLink">
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-toggle="dropdown"
                                        id="navbarDropdownMenuLink2" aria-haspopup="true" aria-expanded="false">
                                        <i class="material-icons">person</i>
                                        <p><span class="d-lg-none d-md-block">Account</span></p>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="navbarDropdownMenuLink2">
                                        @if (!Auth::guest())
                                            <a class="dropdown-item" href="/changePassword">
                                                Change Password
                                            </a>
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                Logout
                                            </a>
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                style="display: none;">
                                                {{ csrf_field() }}
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            @endif

            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            <footer class="footer ">
                <div class="container">
                    <nav class="pull-left">
                        <ul>
                            <li>
                                <a href="http://www.maison-kayser.com.mx">
                                    Maison - Kayser
                                </a>
                            </li>
                            <li>
                                <a href="http://www.maison-kayser.com.mx">
                                    Carmela & Sal
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="copyright pull-right">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script>, PRIGO
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @include('shared.scripts');

    @yield('aditionalScripts')

</body>

</html>
