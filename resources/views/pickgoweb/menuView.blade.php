@extends('layouts.appPickGoLayout')
@include('menu.pickGoMenuView', ['seccion' => 'menu', 'idUser' => $idUser])
@section('content')
<style>
    .desText {
        max-height: 50px;
        font-size: 12px;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<main role="main">
    <div class="shopHeader">
        <div class="hdContainer">
            <div class="store-box">
                <h3>Maison Kayser {{ $sucursal->nombre}}</h2>
                <h5 class="lead">{{ $sucursal->direccion}}</h5>
                <h6>Horario de servicio 8:00 a 20:00 hrs</h6>
                <a class="btn btn-sm btn-warning" href="{{ route('indexPickGo') }}" role="button"><span class="oi oi-map-marker"></span> Cambiar de sucursal</a>
            </div>
        </div>
    </div>
    <div class="container">
    @if(!empty($secciones))
        <nav class="navbar navbar-expand">
            <div class="navbar-collapse collapse" id="navbarSecciones" style="overflow-x: hidden; ">
                <ul class="navbar-nav mr-auto">
                @foreach( $secciones as $seccion)
                    <li class="nav-item">
                        <a class="nav-link" href="#ekm{{ $seccion->id }}">{{ $seccion->nombre }}</a>
                    </li>
                @endforeach
                </ul>
            </div>
        </nav>
        @foreach( $secciones as $seccion)
        <h4 class="m-4" id="ekm{{ $seccion->id }}">{{ $seccion->nombre }}</h4>
        <div class="row" id="row_ekm{{ $seccion->id }}">
            <div id="loading_ekm{{ $seccion->id }}" class="col-4">
                <div class="progress">
                 <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                </div>
                cargando...
            </div>
        </div>
        @endforeach
    @else

    @endif
    </div>
</main>



<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <img class="card-img-top" id="ItemIamge" style="max-height: 250px;" src="https://intranet.prigo.com.mx/storage/platillos/imagenes/26/full.png" alt="Card image cap">
        <div class="card-body">
            <h5 class="card-title" id="ItemName">Menu Item Name</h5>
            <p class="card-text" id="ItemDes">Menu Item Descriotion</p>
            <div class="w-100 mb-1">
                OPCIONES
                <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>
            </div>
            <div class="w-100">
                <div style="width: 30%;" class="float-left input-group mb-3">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" type="button" id="button-addon1"> - </button>
                    </div>
                    <input type="text" class="form-control" placeholder="" aria-label="Example text with button addon" aria-describedby="button-addon1">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="button-addon2"> + </button>
                    </div>
                </div>
                <div style="width: 70%;" class="float-right pl-2">
                    <a href="#" class="btn btn-warning">Agregar</a>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

@endsection
@section('localScripts')
<script>
    $('#productModal').on('show.bs.modal', function (e) {
        if(e.relatedTarget.dataset.reference > 0)
        {
            let id= e.relatedTarget.dataset.reference;
            let image= e.relatedTarget.dataset.image;
            let itemname = e.relatedTarget.dataset.itemname;
            let itemdes = e.relatedTarget.dataset.itemdes;
            document.getElementById("ItemIamge").src = 'https://intranet.prigo.com.mx/storage/'+(image=='default_full.png'?'pickgo/web/defaulmenu.png':'platillos/imagenes/'+id+'/'+image);
            document.getElementById("ItemName").innerHTML = itemname;
            document.getElementById("ItemDes").innerHTML = itemdes;
        }
    });

    function loadSection(id, n)
    {
        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {

            var results = JSON.parse(this.responseText);
            var html="";

            document.getElementById("row_ekm"+results.id).innerHTML = "";
            for(item of results.items)
            {
                html += '<div class="col-12 col-md-6 col-lg-4">\
					<div class="min-border" data-toggle="modal" data-target="#productModal" data-reference="'+item.idplatillo+'" data-itemname="'+item.nombre+'" data-itemdes="'+item.descripcion+'" data-image="'+item.full+'">\
						<div class="d-flex flex-row flex-nowrap h-100">\
							<div class="h-100">\
								<div class="description-col">\
									<h6>'+item.nombre+'</h6>\
									<p class="desText">'+item.descripcion+'</p>\
									<span class="badge badge-secondary">$ '+item.costo+'</span>\
								</div>\
							</div>\
							<div class="ml-auto h-100">\
								<img class="align-middle" style="max-height: 160px;" src="https://intranet.prigo.com.mx/storage/'+(item.full=='default_full.png'?'pickgo/web/defaulmenu.png':'platillos/imagenes/'+item.idplatillo+'/'+item.full)+'">\
							</div>\
						</div>\
					</div>\
				</div>';
            }
            
            document.getElementById("row_ekm"+results.id).innerHTML=html;
            
            return 1;
          }
          else
          {
            /*n++;
            if(n<3)
                loadSection(id, n);*/
            return 0;
          }
        };

        xhttp.open("GET", "{{ route('sectionsMenu') }}?idMenu={{ $sucursal->idMenu }}&idSec="+ id, true);
        xhttp.send();
    }

    @if(!empty($secciones))
        @foreach($secciones as $seccion)
            loadSection({{ $seccion->id }}, 0);
        @endforeach
    @endif

</script>
@endsection