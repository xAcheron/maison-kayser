<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" href="https://image.flaticon.com/icons/svg/1040/1040230.svg">
	<meta charset="utf-8"/>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/css/encuestas/encuestas_style.css" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}"/>
	
</head>
<body>
	<div class="container-fluid">
        @yield('content')
    </div>	
        @include('shared.scripts')
        @yield('addScripts')
</body>
</html>

