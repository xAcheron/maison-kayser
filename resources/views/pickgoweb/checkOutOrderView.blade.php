@extends('layouts.appPickGoLayout')
@section('content')
<div>
    <div class="container"> 
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <h3 id="title">Checkout Orden</h3>
                <table id="tableSaucers" class="table table-responsive">
                    <thead class="text-muted">
                        <tr class="small text-uppercase">
                          <th scope="col" width="250">Platillo</th>
                          <th scope="col" width="120">Cantidad</th>
                          <th scope="col" width="120">Precio</th>
                          <th scope="col" class="text-right" width="200"> </th>
                        </tr>
                    </thead>
                </table> 
            </div>
            <div class="col-md-3">
                <div class="card" style="border-radius: 10px !important;">
                    <div class="card-body">
                        <div class="dlist-align">
                            <center><div id="nombresuc"></div></center>
                            <div id="idSuc"></div>

                            
                        </div>
                        <div class="dlist-align">
                            <center><div id="tiempoprep"> </div></center>
                        </div>
                        <hr>
                        <dl class="dlist-align">
                          <dt>Subtotal:</dt>
                          <div id="subtotal" class="text-right">$0.00</div>
                        </dl>
                        <dl class="dlist-align">
                          <dt>Descuento:</dt>
                          <div id="descuento" class="text-right"  style="color:#be8040 !important;">- $0.00</div>
                        </dl>
                        <dl class="dlist-align">
                          <dt>Total:</dt>
                          <dd id="total" class="text-right text-dark b"><strong>$59.97</strong></dd>
                        </dl>
                        <hr>
                        <dl class="dlist-align">
                            <dt>Hora programada en tienda:</dt>
        
                        </dl>
                        <div id="horasTienda" class="container">
       
                        </div>
                        <hr>
                        <center>
                            <h6>- Metodo de pago -</h6>
                            <h6>Pick & Go <i class="fa fa-check fa-1x" style="color:#be8040 !important;"></i> <br>Ó<br></h6>
                        </center>
                        <img alt="Visa Checkout" class="v-button" role="button" src="https://sandbox.secure.checkout.visa.com/wallet-services-web/xo/button.png"/>
                        <hr>
                        <button id="procesarOrden" class="btn btn-primary btn-block"> Procesar orden </button>
                        <a href="https://intranet.prigo.com.mx/maison-kayser/pick-go/menu" class="btn btn-light btn-block">Continuar ordenando</a>
                    </div> 
                </div> 
            </div>    
        </div> 
</div>
@endsection
@section('localeScripts')
<script type="text/javascript"
src="https://sandbox-assets.secure.checkout.visa.com/
checkout-widget/resources/js/integration/v1/sdk.js">
</script>
<script>
    var hoy = new Date();
    var minutosTotalesAlimentos = 0;
    var minutosActuales = hoy.getMinutes()+minutosTotalesAlimentos;
    var hora2 = minutosActuales+15;
    var hora3 = minutosActuales+30;
    var hora4 =  minutosActuales+45;
    var horaProgramada = 'not_select';


    function roundHours(hour){
        if(hour>60){
          return Math.round(hour/2);
        }else{
            return hour;
        }
       
    }

    function roundMinutes(date) {
        date.setHours(date.getHours() + Math.round(date.getMinutes()/60));
        date.setMinutes(10);
        return date;
    }

    roundHours(hora2);
    roundMinutes(hoy);
    var horaActual = hoy.getHours() + ':' + hoy.getMinutes();
    var horaPickGo2 = hoy.getHours() + ':' + roundHours(hora2);
    var horaPickGo3 = hoy.getHours() + ':' + roundHours(hora3);
    var horaPickGo4= hoy.getHours() + ':' + roundHours(hora4);

$('#procesarOrden').click(function() {
    if(horaProgramada == 'not_select'){
        bootbox.alert({
            title: 'Maison Kayser Pick & Go',
            message: 'Selecciona una hora para recoger tu orden.',
        });
    }else{
        var idSuc = $('input[id="idSuc"]').val();
        var descuentoOrden = $('input[id="descuentoOrden"]').val();
        var totalOrden = $('input[id="totalOrden"]').val();
        if(idSuc != '' && descuentoOrden != '' && totalOrden !=''){
            $.ajax({
            type: "GET",
            url: "{{route('saveNewOrderPickGo')}}",
            data: ({ idSuc: idSuc, discountOrder:descuentoOrden, totalOrder:totalOrden, schueledHour:horaProgramada}),
            success: function(data){
                if(data.success == true){
                    bootbox.alert({
                        title: 'Maison Kayser Pick & Go',
                        message: 'Tu orden fue enviada al restaurante.',
                        callback: function () {
                            window.location.href = "{{route('getOrdersView')}}";
                        }
                    });
                } 
            },
            error: function(msg){
                bootbox.alert({
                        title: 'Maison Kayser Pick & Go',
                        message: 'Error al crear tu orden, intentalo de nuevo.',
                        callback: function () {
                            window.location.href = "{{route('indexPickGo')}}";
                        }
                });             
            }
                    
        });

    }
}


});
$(document).on("click", "#eliminarPlatilloCheckOut", function(){
    var data = $(this).data("target");
        $.ajax({
            type: "GET",
            url: "{{route('deleteSaucerBasketWeb')}}",
            data: ({ idSaucer: data}),
            success: function(data){
                if(data.info_status == 'Saucer delete success')
                {
                    window.location.href = '{{route('getCheckOutView')}}';
                    bootbox.alert({
                        title: 'Maison Kayser Pick & Go',
                        message: 'Platillo eliminado correctamente.',
                    });
    
                }
            },
            error: function(msg){
                bootbox.alert({
                        title: 'Maison Kayser Pick & Go',
                        message: 'Error al eliminar el platillo, intentalo de nuevo.',
                    });
                            
            }
                    
        });

});

$(document).on("change", ".form-check-input", function(){ 
    if($(this).is(':checked')){
         horaProgramada = $(this).val();
        $('input[type="checkbox"]').not(this).prop('checked', false);
    }
});

$(document).ready(function() {
    $.ajax({
        type: "GET",
        url: "{{route('getDataCheckOut')}}",
        success: function(data){
            if(data.info_status != 'Empty basket'){
                minutosTotalesAlimentos = data.timePreparation;
                $("#idSuc").html('<input type="hidden" id="idSuc" value="'+data.idSuc+'">');
                $("#nombresuc").html('<h6> Maison Kayser Pick Go <br>'+data.nameSuc+'</h6>');
                $("#subtotal").html('<h6> $'+data.subTotalPricePreOrder+'</h6>'+'<input type="hidden" id="subtotalOrden" value="'+data.subTotalPricePreOrder+'">');
                $("#descuento").html('<h6> - $'+data.discountPrice+'</h6>'+'<input type="hidden" id="descuentoOrden" value="'+data.discountPrice+'">');
                $("#total").html('<h6>$'+data.totalPricePreorder+'</h6>'+'<input type="hidden" id="totalOrden" value="'+data.totalPricePreorder+'">');
                $("#tiempoprep").html('<p class="small"> Tiempo de entrega aproximado : '+data.timePreparation+' min </p>');
                $("#horasTienda").html('<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="hora1" value="'+horaPickGo2+'"><label class="form-check-label" for="hora1">'+horaPickGo2+'</label></div>'+'<br>'+'<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="hora1" value="'+horaPickGo3+'"><label class="form-check-label" for="hora1">'+horaPickGo3+'</label></div>'+'<br>'+'<div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="hora1" value="'+horaPickGo4+'"><label class="form-check-label" for="hora1">'+horaPickGo4+'</label></div>');
                for(let i = 0; i < data.dataSaucers.length; i++) {
                    $("#tableSaucers").append('<tbody><tr><td><figure class="itemside align-items-center"><div class="aside"><img width="60px"src="https://intranet.prigo.com.mx/storage/platillos/imagenes/'+data.dataSaucers[i].idSaucer+'/thumb.png" class="img-sm"></div><figcaption class="info"><h5 class="title text-dark">'+data.dataSaucers[i].nameSaucer+'</h5></figcaption></figure></td><td><h5>'+data.dataSaucers[i].amountSaucer+'</h5></td><td><div class="price-wrap"><var class="price">$ '+data.dataSaucers[i].totalPrice+'</var></div></td><td class="text-right"> <button id="eliminarPlatilloCheckOut" class="btn btn-light"  data-target="'+data.dataSaucers[i].idSaucer+'"> Eliminar</button></td></tr></tbody>');
                }

                V.init({
                    apikey: "4PHD0NZQZGHUMWH0K8S121hhGRXaHAmE0qMecky6BVuBRV6Qo", encryptionKey: "XCNQ2B6JE3AKYE8ARF0H13n5j98XJaJ9Id_gbglUAGj6Yqbsc",
                    paymentRequest: {
                        currencyCode: "MXN",
                        subtotal: data.totalPricePreorder
                    }
                });
                V.on("payment.success", function(payment)
                {alert(JSON.stringify(payment)); });
                V.on("payment.cancel", function(payment)
                {alert(JSON.stringify(payment)); });
                V.on("payment.error", function(payment, error)
                {alert(JSON.stringify(error)); });
                
            }else{
                $('#tableSaucers').empty();
                $('#title').empty();
                $('#title').append('Cesta vacia');
            }


        },

        error: function(msg){   
            
                          
            }       
        });
});
</script>
@endsection