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
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
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
	
	.body-bg {
		background: #f7f7f7;
	}
	/*.ui5-menu-rp {
		z-index: 9999!important;
	}*/
  </style>
</head>

<body class="body-bg">
	<div id="app" id="app" data-csrf="{{ csrf_token() }}">
	@yield('content')
	</div>
	@yield('jsimports')
	@yield('aditionalScripts')
</body>
</html>