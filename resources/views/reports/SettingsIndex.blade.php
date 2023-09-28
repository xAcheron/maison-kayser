@extends('layouts.pro')
@include('menu.reports', ['seccion' => 'settings'])
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card ">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-3">
                            <ul class="nav nav-pills nav-pills-warning flex-column" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#link1" role="tablist">
                                        Alignment
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#link2" role="tablist">
                                        Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#link3" role="tablist">
                                        Options
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content tab-space">
                                <div class="tab-pane active" id="link1">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
