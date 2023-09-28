@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'ProductMix'])
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
            <div class="card-icon">
                <i class="material-icons">timeline</i>
            </div>
            <h4 class="card-title">Evaluacion
                <small> - Filters</small>
            </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        Mes:<br>
                        <select class="select2-item" id="daterange" name="daterange"  data-size="7" style="width:100%;" title="Location">
                            <option value="0" disabled selected>Selecciona un mes</option>
                            @if(!empty($meses))
                                @foreach($meses as $mes)
                                <option value="{{ $mes->id }}">{{ $mes->mes }}</option>
                                @endforeach
                            @else
                            <option value="2022-08-01">Agosto 2022</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-3">
                        Location:<br>
                        <select class="select2-item" id="location" data-size="7" style="width:100%;" title="Location">
                            <option value="0" disabled selected>Select a location</option>
                            @if(!empty($hierachy))
                                @foreach($hierachy as $location)
                                <option value="{{ $location->id }}" data-type="{{ $location->tipo }}">{{ $location->nombre }}</option>
                                @endforeach
                            @else
                            <option value="tzuco">Tzuco</option>
                            @endif
                        </select>
                    </div>                
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <div class="btn-group">
                            <button id="runReport" class="btn btn-white btn-just-icon">
                                <i class="material-icons">search</i>
                                <div class="ripple-container"></div>
                            </button>
                            <button  id="exportReport" class="btn btn-white btn-just-icon">
                                <i class="material-icons">table_view</i>
                                <div class="ripple-container"></div>
                            </button>
                        </div>
                    </div>
                </div>        
            </div>
        </div>
    </div>
</div>
<div class="row" id="reportArea">

</div>
<div id="formsarea"></div>
@endsection
@section('aditionalScripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.29.2/sweetalert2.all.js"></script> 
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
<script type="text/javascript">
$(function() {
    $('.select2-item').select2({
        templateResult: formatState
    });
    function formatState (state) {
    if(state.disabled == false && (state.id>0 || state.id != "" ))
    {    
        if(state.element.attributes['data-type'] != null && state.element.attributes['data-type'] != undefined)
        {
            tipo = state.element.attributes['data-type'].value
            return $('<span class="'+( tipo==2 ?"ml-2" :"font-weight-bold")+'">'+state.text+'</span>');
        }
        else
        {
            return state.text;
        }
    }
    else
    {
        return state.text;
    }
    
  }

  $("#exportReport").on("click",function(e) {

    if(validParams())
    {
        let url = "{{ route('getReport',['id' => 11, 'format' =>'xlsx']) }}";
        var myForm = document.createElement('form');
        myForm.setAttribute('action', url);
        myForm.setAttribute('method', 'post');
        myForm.setAttribute('hidden', 'true');
        myForm.setAttribute('target', '_blank');
        var daterange = document.createElement('input');
        daterange.setAttribute('type', 'hidden');
        daterange.setAttribute('name', 'daterange');
        daterange.setAttribute('value', $("#daterange").val());
        myForm.appendChild(daterange);
        var location = document.createElement('input');
        location.setAttribute('type', 'hidden');
        location.setAttribute('name', 'location');
        location.setAttribute('value', $("#location").val());
        myForm.appendChild(location);
        var token = document.createElement('input');
        token.setAttribute('type', 'hidden');
        token.setAttribute('name', '_token');
        token.setAttribute('value', "{{ csrf_token() }}");
        myForm.appendChild(token);
        document.getElementById("formsarea").appendChild(myForm);
        myForm.submit();
        document.getElementById("formsarea").innerHTML="";
    }
    else
    {
        swal({
            title: "Error",
            text: 'All filter params are mandatory!',
            type: 'error',
            showConfirmButton: true,
            confirmButtonText: 'OK'
        });
    }

    });

  $('#runReport').on("click",function(e){
    if(validParams())
    {
        loadingData();

        var params = { daterange: $("#daterange").val(), location: $("#location").val(),_token: "{{ csrf_token() }}" };
        
        $.ajax({
            type: "POST",
            url: "{{ route('getReport',['id' => 13, 'format' =>'html']) }}",
            data: params,
            success: function(msg) {
                console.log(msg);
                if(msg.success == true)
                {
                    if(msg.data.Bmes.length > 0)
                    {
                        //makereport();
                        //makeTable(msg.data);
                    }
                    else
                    {
                        //clearTable();
                    }
                }
            },
            error: function(){

            }

        });

    }
    else
    {
        swal({
            title: "Error",
            text: 'All filter params are mandatory!',
            type: 'error',
            showConfirmButton: true,
            confirmButtonText: 'OK'
        });
    }
  });  
});
function loadingData(){
    Swal.showLoading();
}

function endLoadingData(){
    Swal.close();
}

function clearTable()
{
    var tabla = document.getElementById("bmesTable");
    tabla.innerHTML = "";
    tabla = document.getElementById("bdiaTable");
    tabla.innerHTML = "";
    tabla = document.getElementById("bsemTable");
    tabla.innerHTML ="";
}

function makeTable(data)
{
    clearTable();
    
    let htmlTemplate = "<td class=\"text-left\">:suc</td><td class=\"text-right\">:budget</td><td class=\"text-right\">:venta</td><td class=\"text-right\">:diff</td>";
    let domTarget = '#bmesTable';
    let tr = document.createElement('tr');
    let target = document.querySelector(domTarget);
    let innerHTML="";
    var formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
    });
    var formatter2 = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });

    for(var i=0;i<data.Bmes.length;i++)
    {
        tr = document.createElement('tr');
        innerHTML=htmlTemplate;

        innerHTML = innerHTML.replace(':suc', data.Bmes[i].nombre);
        innerHTML = innerHTML.replace(':venta', formatter2.format(data.venta[i].netSales));
        innerHTML = innerHTML.replace(':budget',formatter2.format(data.Bmes[i].monto));
        innerHTML = innerHTML.replace(':diff',formatter2.format(data.venta[i].netSales-data.Bmes[i].monto));
        tr.innerHTML = innerHTML;
        target.appendChild(tr);
    }

    htmlTemplate ="<td class=\"text-left\">:suc</td><td class=\"text-left\">:sem</td><td class=\"text-right\">:vsa</td><td class=\"text-right\">:bsa</td><td class=\"text-right\">:lunes</td><td class=\"text-right\">:martes</td><td class=\"text-right\">:miercoles</td><td class=\"text-right\">:jueves</td><td class=\"text-right\">:viernes</td><td class=\"text-right\">:sabado</td><td class=\"text-right\">:domingo</td><td class=\"text-right\">:vsac</td><td class=\"text-right\">:pbsa</td>";
    domTarget = '#bsemTable';
    tr = document.createElement('tr');
    target = document.querySelector(domTarget);
    for(var i=0;i<data.Bsem.length;i++)
    {
        tr = document.createElement('tr');
        innerHTML=htmlTemplate;
        innerHTML = innerHTML.replace(':suc', data.Bdia[i].nombre);
        innerHTML = innerHTML.replace(':sem', data.SemAct);
        innerHTML = innerHTML.replace(':vsa', formatter2.format(data.Bsem[i].VentaAnterior));
        innerHTML = innerHTML.replace(':bsa', formatter2.format(data.Bsem[i].budget));
        innerHTML = innerHTML.replace(':lunes', formatter2.format(data.Bsem[i].L));
        innerHTML = innerHTML.replace(':martes', formatter2.format(data.Bsem[i].M));
        innerHTML = innerHTML.replace(':miercoles', formatter2.format(data.Bsem[i].Mr));
        innerHTML = innerHTML.replace(':jueves', formatter2.format(data.Bsem[i].J));
        innerHTML = innerHTML.replace(':viernes', formatter2.format(data.Bsem[i].V));
        innerHTML = innerHTML.replace(':sabado', formatter2.format(data.Bsem[i].S));
        innerHTML = innerHTML.replace(':domingo', formatter2.format(data.Bsem[i].D));
        innerHTML = innerHTML.replace(':vsac', formatter2.format(data.Bsem[i].VentaActual));        
        innerHTML = innerHTML.replace(':pbsa', formatter.format(data.Bsem[i].VentaActual/data.Bsem[i].budget*100));

        tr.innerHTML = innerHTML;
        target.appendChild(tr);
    }
    htmlTemplate = "<td class=\"text-left\">:suc</td><td class=\"text-left\">:fecha</td><td class=\"text-right\">:budget</td><td class=\"text-right\">:venta</td><td class=\"text-right\">:diff</td>";
    domTarget = '#bdiaTable';
    tr = document.createElement('tr');
    target = document.querySelector(domTarget);    

    for(var i=0;i<data.Bdia.length;i++)
    {
        tr = document.createElement('tr');
        innerHTML=htmlTemplate;

        innerHTML = innerHTML.replace(':suc', data.Bdia[i].nombre);
        innerHTML = innerHTML.replace(':fecha', data.Bdia[i].fecha);
        if(data.ventaDia[i])
            innerHTML = innerHTML.replace(':venta', formatter2.format(data.ventaDia[i].netSales));
        else
            innerHTML = innerHTML.replace(':venta', "--");
        innerHTML = innerHTML.replace(':budget',formatter2.format(data.Bdia[i].budget));
        if(data.ventaDia[i])
            innerHTML = innerHTML.replace(':diff',formatter2.format(data.ventaDia[i].netSales-data.Bdia[i].budget));
        else
            innerHTML = innerHTML.replace(':diff',formatter2.format(data.Bdia[i].budget));
        tr.innerHTML = innerHTML;
        target.appendChild(tr);
    }

    endLoadingData();
}

function validParams() {
    if($("#daterange").val() != "" && $("#location").val() != "" && $("#daterange").val() != null && $("#location").val() != null )
        return true;
    return false;
}

</script>
@endsection