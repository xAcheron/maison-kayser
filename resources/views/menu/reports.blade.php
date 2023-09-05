@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'encuestas'])
@section('content')
<div class="row">
    <div class="col-sm-6 col-md-3">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
                <h4 class="card-title">Satisfacción del cliente
                </h4>
            </div>
            <div class="card-body">
                <div id='dashboard'>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
                <h4 class="card-title">Encuestas Negativas
                </h4>
            </div>
            <div class="card-body">
                <div id='dashboard'>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('aditionalScripts')
@endsection