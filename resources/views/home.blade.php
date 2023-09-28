@extends('layouts.pro')
@section('content')
@if(!empty($apps))
<div class="row">
	@foreach($apps as $app)
    <div class="col-lg-3 col-md-6 col-sm-6">		
		<button id="btnPedidos" href="@if($app->externo == 1){{ $app->url }} @else {{ route($app->url) }} @endif" style="width: 100%" class="btn btn-warning btn-lg btn-app-link">
            <i class="material-icons">{{$app->icono}}</i>
			<br><br>{{$app->nombre}}
            <div class="ripple-container"></div>
		</button>
    </div>
	@endforeach
</div>
@endif
@endsection
@section('aditionalScripts')
<script>
	$('.btn-app-link').click(function(){
		$(location).attr("href", $(this).attr("href"));
	});
</script>
@endsection