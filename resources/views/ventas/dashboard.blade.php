@extends('layouts.pro')
@include('menu.ventas')
@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
            <div class="card-header">
                <p class="card-category">NetSales</p>
                <h3 class="card-title">184</h3>
            </div>
            <div class="card-footer">
                <div class="stats">
                <i class="material-icons text-danger">warning</i>
                <a href="#pablo">Get More Space...</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <H3 class="card-title col-6 mr-auto">NetSales</H3>
                    <div class="col-5 ml-auto">
                        <select class="selectpicker" data-width="fit" data-style="btn btn-sm">
                            <option>Yesterday</option>
                            <option>Week</option>
                            <option>Month</option>
                            <option>Year</option>
                        </select></div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <h3 class="card-title"><i class="material-icons text-danger">trending_down</i>184</h3>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                <i class="material-icons text-danger">warning</i>
                <a href="#pablo">Get More Space...</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div id="chart-e1" class="ct-chart" style="position: relative;"></div>
            </div>
            <div class="card-footer">
                <div class="stats">
                <i class="material-icons text-danger">warning</i>
                <a href="#pablo">Get More Space...</a>
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection
@section('jsimports')
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/moment.min.js') }}"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/bootstrap-selectpicker.js') }}"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/chartist.min.js') }}"></script>
<script src="{{ asset('material_pro_2_1_0/assets/js/plugins/chartistAddons/chartist-plugin-fill-donut.js') }}"></script>
@endsection
@section('aditionalScripts')
<link rel="stylesheet" href="css/chartist.min.css">
<style type="text/css">
            .ct-chart-donut .ct-series-a .ct-slice-donut {
                stroke: #d70206;
            }
            .ct-chart-donut .ct-series-b .ct-slice-donut {
                stroke: rgba(0,0,0,.4);
                opacity: 0.0;
            }
            .ct-chart-donut .ct-fill-donut .ct-slice-donut{
                stroke: rgba(0,0,0,.4);
                opacity: 1;
            }
            .ct-fill-donut-label h3{
                font-weight: bolder;
            }
            .ct-fill-donut-label .small {
                font-size: 0.6em;
            }
            .ct-fill-donut-label i { 
                font-size: 1.5em;
                color: rgba(0,0,0,.4);
            }
        </style>
<script type="text/javascript">
    $(".selectpicker").selectpicker();


    var chart = new Chartist.Pie('#chart-e1', {
                    series: [160, 60 ],
                    labels: ['', '']
                }, {
                    donut: true,
                    donutWidth: 20,
                    startAngle: 210,
                    total: 260,
                    showLabel: false,
                    plugins: [
                        Chartist.plugins.fillDonut({
                            items: [{
                                content: '<i class="fa fa-tachometer text-muted"></i>',
                                position: 'bottom',
                                offsetY : 10,
                                offsetX: -2
                            }, {
                                content: '<h3>160<span class="small">mph</span></h3>'
                            }]
                        })
                    ],
                });

                chart.on('draw', function(data) {
                    if(data.type === 'slice' && data.index == 0) {
                        // Get the total path length in order to use for dash array animation
                        var pathLength = data.element._node.getTotalLength();

                        // Set a dasharray that matches the path length as prerequisite to animate dashoffset
                        data.element.attr({
                            'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                        });

                        // Create animation definition while also assigning an ID to the animation for later sync usage
                        var animationDefinition = {
                            'stroke-dashoffset': {
                                id: 'anim' + data.index,
                                dur: 1200,
                                from: -pathLength + 'px',
                                to:  '0px',
                                easing: Chartist.Svg.Easing.easeOutQuint,
                                // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
                                fill: 'freeze'
                            }
                        };

                        // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
                        data.element.attr({
                            'stroke-dashoffset': -pathLength + 'px'
                        });

                        // We can't use guided mode as the animations need to rely on setting begin manually
                        // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
                        data.element.animate(animationDefinition, true);
                    }
                });
</script>
@endsection