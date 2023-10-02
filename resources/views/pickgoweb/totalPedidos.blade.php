@extends('layouts.appPickGoLayout')
@include('menu.pickGoMenuView', ['seccion' => 'pedidos'])
@section('content')
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<div class="container">
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="table-responsive">
				<table class="table orders-list" id="tableorders">
					<thead>
						<tr>
                            <th><span>Núm. Pedido</span></th>
                            <th><span>Sucursal</span></th>
							<th><span>Creado</span></th>
							<th><span>Estado</span></th>
							<th><span>Precio total</span></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
				</table>
			</div>
			{{-- <ul class="pagination pull-right">
				<li><a href="#"><i class="fa fa-chevron-left"></i></a></li>
				<li><a href="#">1</a></li>
				<li><a href="#">2</a></li>
				<li><a href="#">3</a></li>
				<li><a href="#">4</a></li>
				<li><a href="#">5</a></li>
				<li><a href="#"><i class="fa fa-chevron-right"></i></a></li>
			</ul> --}}
		</div>
	</div>
</div>
</div>

<style>



</style>
@endsection
@section('localeScripts')
<script type="text/javascript">
$(document).ready(function() {
    $.ajax({
        type: "GET",
        url: "{{route('getDataOrdersByClientWeb')}}",
        success: function(data){
            if(data.success  == true){
                if(data.ordersClient.length > 0){
                    for(let i = 0; i < data.ordersClient.length; i++) {
                        $("#tableorders").append('<tbody><tr><td><span class="label label-default">#'+data.ordersClient[i].idPedido+'</span></td><td><span class="label label-default">'+data.ordersClient[i].sucursal+'</span></td><td><span class="label label-default">'+data.ordersClient[i].fechaHoraRegistro.slice(0, -3)+' hrs</span></td><td><span class="label label-default">'+data.ordersClient[i].status+'</span></td><td><span class="label label-default">$'+data.ordersClient[i].montoTotal.toFixed(2) +'</span></td><td style="width: 20%;"><a href="#" class="table-link danger"><span class="fa-stack">	<i class="fa fa-external-link fa-stack-1x"></i></span></a></td></tr></tbody>');        
                    }
                }
      
            }

        },

        error: function(msg){   
            
                          
        }       
    });
});
</script>
@endsection