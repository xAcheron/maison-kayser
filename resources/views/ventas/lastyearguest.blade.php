@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'lastyear'])
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <label class="col-xl-1 col-lg-2 col-md-3 col-sm-6 col-form-label">Fecha</label>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <div class="form-group">
                            <div class='input-group date'>
                                <input type="text" id="fecha" class="form-control datepicker" value="">
                                <span class="input-group-addon">
                                <span class="fa fa-calendar">
                                </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <label class="col-xl-1 col-lg-2 col-md-3 col-sm-6 col-form-label">VS Año</label>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <select id ="anio" name="anio" class="selectpicker" style="max-width: 100px !important;" data-style="btn-info select-with-transition" data-size="5" tabindex="-98">
                            @for($anio=2019;$anio<date("Y");$anio++)
                            <option value="{{ $anio }}">{{ $anio }}</option>
                            @endfor
                        </select>
                    </div>
                    <label class="col-xl-1 col-lg-2 col-md-3 col-sm-6 col-form-label">Compañia</label>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <select id = "compania" name="compania" class="selectpicker" data-style="btn select-with-transition" title="Seleccione una compania" data-size="7" tabindex="-98">
                            <option value="0">Global</option>
                            <option value="1">Maison Kayser Mexico</option>
                            <option value="2">Carmela y Sal</option>
                            <option value="3">Tzuco</option>
                            <option value="4">Maison Kayser España</option>
                        </select>
                    </div>
                    <div class="col-xl-1 col-lg-3 col-md-3 col-sm-6">
                        Incluir RVCs <input type="checkbox" name="rvc" id="rvc" />
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                        <div class="btn-group">
                            <button id="findPListbtn" onclick="reload(event)" class="btn btn-white btn-just-icon">
                                <i class="material-icons">search</i>
                                <div class="ripple-container"></div>
                            </button>
                            <button id="xlsPListbtn" onclick="xlsExport(event)" class="btn btn-white btn-just-icon">
                                <i class="material-icons">table_view</i>
                                <div class="ripple-container"></div>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-6 col-form-label">{{ $fechaActual }} VS {{ $fechaVS }}</label>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach($ventas AS $idEmpresa => $venta)
<div class="row">
    <div class="@if(!empty($includeRVC)) col-12 @else col-xl-6 col-lg-6 col-md-6 col-sm-12 @endif">
        <div class="card">
            <div class="card-header card-header-orange card-header-text">
                <div class="card-text">
                <h4 class="card-title">{{ $venta[0] }} - Mismas tiendas</h4>
                    <input type="hidden" value="" id="artId" />
                    <input type="hidden" value="" id="artName" />
                    <input type="hidden" value="" id="artUnit" />
                </div>
            </div>
            <div class="card-body" style="overflow-y: auto;">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Sucursal</th><th>Guests</th><th>LY %</th><th>Avg Check</th><th>LY %</th>@if(!empty($includeRVC))<th>Venta LY</th><th></th><th>Vitrina</th><th>Venta LY</th><th>LY %</th><th></th><th>Salon</th><th>Venta LY</th><th>LY %</th><th></th><th>Delivery</th><th>Venta LY</th><th>LY %</th><th></th><th>Catering</th><th>Venta LY</th><th>LY %</th>@endif</tr>
                    </thead>
                    <tbody>
                        @foreach($venta[1] as $sucursal)
                        <tr><td clas="text-left">{{$sucursal->sucursal}}</td><td class="text-right">{{$sucursal->Currentguests}}</td><td class="{{($sucursal->LY >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->LY}}%</td><td class="text-right">{{$sucursal->CurrentavgCheck}}</td><td class="{{($sucursal->LYAvgCheck >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->LYAvgCheck}}%</td>@if(!empty($includeRVC))<td class="text-right">{{$sucursal->LYNetSales}}</td><td></td><td class="text-right">{{$sucursal->Vitrina}}</td><td class="text-right">{{$sucursal->VitrinaLY}}</td><td class="{{($sucursal->VLY >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->VLY}}</td><td></td><td class="text-right">{{$sucursal->Salon}}</td><td class="text-right">{{$sucursal->SalonLY}}</td><td class="{{($sucursal->SLY >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->SLY}}</td><td></td><td class="text-right">{{$sucursal->Delivery}}</td><td class="text-right">{{$sucursal->DeliveryLY}}</td><td class="{{($sucursal->DLY >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->DLY}}</td><td></td><td class="text-right">{{$sucursal->Institucional}}</td><td class="text-right">{{$sucursal->InstitucionalLY}}</td><td class="{{($sucursal->ILY >= 100 ? 'text-success' : 'text-danger')}} text-right">{{$sucursal->ILY}}</td>@endif</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if(!empty($venta[2]))
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header card-header-orange card-header-text">
                <div class="card-text">
                    <h4 class="card-title">Aperturas/Cierres</h4>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Sucursal</th><th>Guests</th><th>LY Guests</th><th>Avg Check</th><th>LY AvgCheck</th></tr>
                    </thead>
                    <tbody>
                    @php
                            //dd($venta[2]);
                        @endphp
                        @foreach($venta[2] as $sucursal)
                        <tr><td clas="text-left">{{$sucursal->sucursal}}</td><td class="text-right">{{$sucursal->Currentguests}}</td><td class="text-right">{{$sucursal->LYNetguests}}</td><td class="text-right">{{$sucursal->CurrentavgCheck}}</td><td class="text-right">{{$sucursal->LYNetavgCheck}}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endforeach
@endsection
@section('jsimports')
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/moment.min.js') }}"></script>
<!--script src="{{ asset('material_pro_2_1_0/assets/js/plugins/bootstrap-selectpicker.js') }}"></script-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/chartist.min.js') }}"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/chartistAddons/chartist-plugin-fill-donut.js') }}"></script>
@endsection
@section('aditionalScripts')
<script type="text/javascript">

$(function () {
    $('#fecha').datetimepicker({
        viewMode: 'months',
        format: 'YYYY-MM'
    });

});

function reload(event) {
    event.preventDefault();
    window.location = "{{ route('getLastYearGuest') }}?fecha="+$('#fecha').val()+"&vsa="+$('#anio').val()+"&compania="+$('#compania').val()+"&rvc="+document.getElementById("rvc").checked;
}

function xlsExport(event)
{
        var form = document.createElement("form");
        var element1 = document.createElement("input"); 
        var element2 = document.createElement("input");  
        var element3 = document.createElement("input");              

        form.method = "POST";
        form.id="getLastYearXls";
        form.action = "{{ route('getLastYearGuestXls') }}";
        form.target ="_blank";

        element1.value=$("#fecha").val();
        console.log($("#fecha").val());
        element1.name="fecha";
        element1.type="hidden";
        form.appendChild(element1);
        
        element2.value="{{ csrf_token() }}";
        element2.name="_token";
        element2.type="hidden";
        form.appendChild(element2);

        document.body.appendChild(form);
        console.log(form);
        form.submit();
}
</script>
@endsection