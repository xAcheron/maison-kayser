<!DOCTYPE html>
<html lang="en" class="js-focus-visible" data-js-focus-visible="">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" type="image/png" sizes="96x96" href="https://www.maison-kayser.com.mx/img/favicon-1.ico?1523004325">
		<title>Maison Kayser México</title>

		<!-- Bootstrap core CSS -->
		<link href="{{ asset('BS4/css/bootstrap.min.css') }}" rel="stylesheet">
		<link href="{{ asset('BS4/css/open-iconic-bootstrap.min.css') }}" rel="stylesheet">
		<!-- Custom styles for this template -->
		<link href="{{ asset('BS4/css/navbar-top-fixed.css') }}" rel="stylesheet">
        <link href="{{ asset('css/login-web-pick.css') }}" rel="stylesheet">
		<style type="text/css">
            .elementToFadeIn {
                animation: fadeIn 4s linear forwards;
                display: block !important;
            }

            .elementToFadeout {
                animation: fadeInOut 4s linear forwards;
                display: none !important;
            }

            @keyframes fadeIn {
                0% { opacity:0; }
                100% { opacity:1; } 
            }
            @keyframes fadeOut {
                0% { opacity:1; }
                100% { opacity:0; } 
            }
			@font-face {
			  font-weight: 400;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Book-cd7d2bcec649b1243839a15d5eb8f0a3.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 500;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Medium-d74eac43c78bd5852478998ce63dceb3.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 700;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Bold-83b8ceaf77f49c7cffa44107561909e4.woff2') format('woff2');
			}

			@font-face {
			  font-weight: 900;
			  font-style:  normal;
			  font-family: 'Circular-Loom';

			  src: url('https://cdn.loom.com/assets/fonts/circular/CircularXXWeb-Black-bf067ecb8aa777ceb6df7d72226febca.woff2') format('woff2');
			}
			.navbar-brand {
				height: 60px;
			}
			.navbar-brand img {
				max-height: 100%;
				display: inline-block;
				padding: 5px 0;
			}
			.shopHeader {
				margin-bottom: 1rem;
				position: relative;
			}
			.hdContainer {
				height: 16rem;
				background-color: #777;
				padding: 10px 30px;
				background-image: url("{{ asset('BS4/img/3-Maison_Kayser_Reforma.jpg') }}");
			}
            #map {
                height: 100%;
            }
			nav .navbar-nav li a{
			  color: black !important;
			}

			.min-border {
				height: 160px;
				border: 1px solid #D5D6D7;
				overflow: hidden;
				align-items: stretch;
				border-radius: 8px;
				padding: 0px;
				position: relative;
				transition: all;
				transition-duration: .25s;
				transition-timing-function: ease;
				cursor: pointer;
				margin-bottom: 5px;
			}
			.description-col{
				padding: 5px;
			}

			.store-box {
				float: left; margin-left: 10px;
				background-color: rgba(255, 255, 255, 0.8); 
				width: 450px;padding: 18px;height: 15rem;
				border-radius: 8px;
			}

            .loginLink {
                color: #000;
                font-weight: bold;
                margin-right: 10px;
                border: 1px solid transparent;
                text-align: center;
                padding: 6px 6px;
                border-radius: .25rem;
                text-decoration: none;
                display: inline-block;
            }
            .loginLink:hover {
/*                color: #ffc107;
                background-color: transparent;
                background-image: none;
                border-color: #ffc107;
                
                background-color: white;
                font-size: 16px;
*/
                color: black;
                border: 1px solid #ffc107;
                text-align: center;
                padding: 6px 6px;
                border-radius: .25rem;
                text-decoration: none;
                display: inline-block;
            }
            .popover {max-width:400px;}
            .back-to-top {
                position: fixed;
                bottom: 25px;
                right: 25px;
                display: none;
            }
		</style>
	</head>
	<body cz-shortcut-listen="true">
    @if(!Request::is('maison-kayser/pick-go/login*'))
    <nav class="navbar navbar-expand-md navbar-light fixed-top bg-light">
      <div class="navbar-brand"><img src="{{ asset('BS4/img/logo.png') }}"></div>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto"><!--
          <li class="nav-item active">
            <a class="nav-link" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Link</a>
          </li>
          <li class="nav-item">
            <a class="nav-link disabled" href="https://getbootstrap.com/docs/4.1/examples/navbar-fixed/#">Disabled</a>
          </li> 
          @yield('menuPickGo')-->
        </ul>
        <form class="form-inline mt-2 mt-md-0">
        @if(empty(session('idUser')))
          <a href="{{ route('loginPickGo') }}" class="loginLink" >Iniciar Sesión</a>
        @else
          <a href="#" class="loginLink" > Usuario </a>
        @endif
          <button id="cestaBtn" class="btn btn-outline-warning my-2 my-sm-0" type="button" data-container="body" data-placement="bottom" data-toggle="popover"><span class="oi oi-basket"></span> Cesta</button>
        </form>
      </div>
    </nav>
    @endif
    @yield('content')

    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{{ asset('BS4/js/jquery-3.3.1.slim.min.js' ) }}" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="{{ asset('BS4/js/popper.min.js') }}"></script>
    <script src="{{ asset('BS4/js/bootstrap.min.js') }}"></script>
    @yield('localScripts')
    <script>
        $("#cestaBtn").popover({
            html: true,
            content: function() {
                @if(!empty(session('idUser')))
                    return '<div id="cestaBody" style="width: 80px;height: 20px;"><div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div></div>';
                @else
                    return '<div id="cestaBody" style="width: 180px;height: 40px;">Por favor inicie sesion para comenzar con su pedido</div>';
                @endif
            }
        });
        $(document).ready(function(){
            $(window).scroll(function () {
                var bktop = document.getElementById('back-to-top');
                if ($(this).scrollTop() > 50) {
                    if(bktop.style.opacity == 0)
                    {
                        bktop.classList.remove("elementToFadeOut");
                        bktop.classList.add("elementToFadeIn");
                    }
                } else {
                    if(bktop.style.opacity == 1)
                    {
                        bktop.classList.remove("elementToFadeIn");
                        bktop.classList.add("elementToFadeOut");
                    }
                    
                }
            });
            // scroll body to 0px on click
            $('#back-to-top').click(function () {
                $('body,html').animate({
                    scrollTop: 0
                }, 400);
                return false;
            });
        });
    </script>
    <a id="back-to-top" href="#" class="btn btn-light btn-lg back-to-top" role="button"><span class="oi oi-chevron-top"></span></a>
</body>
</html>