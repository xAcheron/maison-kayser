@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'reports'])
@section('content')
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-8 col-xl-8">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
                <h4 class="card-title">Venta Mensual
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
<style>
    .legend > tbody > tr {
        border: 1px solid rgba(0, 0, 0, 0.1);
    }     
    .legend > tbody > tr > td {
    padding: 4px;
    }
    .legendSales {
        min-width: 100px;
        width: 100px;
        text-align: right;
    }
    .legendPerc {
        min-width: 60px;
        width: 60px;
        text-align: right;
    }
</style>
<script>
function dashboard(id, fData){
    var barColor = 'steelblue';
    function segColor(c){ return {Salon:"#807dba", Vitrina:"#e08214", Catering:"#41ab5d", Delivery:"#CE0808"}[c]; }
    
    // compute total for each Month.
    fData.forEach(function(d){d.total=d.Sales.Salon+d.Sales.Vitrina+d.Sales.Catering+d.Sales.Delivery;});
    
    // function to handle histogram.
    function histoGram(fD){
        var hG={},    hGDim = {t: 60, r: 0, b: 30, l: 0};
        hGDim.w = 500 - hGDim.l - hGDim.r, 
        hGDim.h = 300 - hGDim.t - hGDim.b;
            
        //create svg for histogram.
        var hGsvg = d3.select(id).append("svg")
            .attr("width", hGDim.w + hGDim.l + hGDim.r)
            .attr("height", hGDim.h + hGDim.t + hGDim.b).append("g")
            .attr("transform", "translate(" + hGDim.l + "," + hGDim.t + ")");

        // create function for x-axis mapping.
        var x = d3.scale.ordinal().rangeRoundBands([0, hGDim.w], 0.1)
                .domain(fD.map(function(d) { return d[0]; }));

        // Add x-axis to the histogram svg.
        hGsvg.append("g").attr("class", "x axis")
            .attr("transform", "translate(0," + hGDim.h + ")")
            .call(d3.svg.axis().scale(x).orient("bottom"));

        // Create function for y-axis map.
        var y = d3.scale.linear().range([hGDim.h, 0])
                .domain([0, d3.max(fD, function(d) { return d[1]; })]);


        
        // Create bars for histogram to contain rectangles and Sales labels.
        var bars = hGsvg.selectAll(".bar").data(fD).enter()
                .append("g").attr("class", "bar");
        
        //create the rectangles.
        bars.append("rect")
            .attr("x", function(d) { return x(d[0]); })
            .attr("y", function(d) { return y(d[1]); })
            .attr("width", x.rangeBand())
            .attr("height", function(d) { return hGDim.h - y(d[1]); })
            .attr('fill',barColor)
            .on("mouseover",mouseover)// mouseover is defined beSalon.
            .on("mouseout",mouseout);// mouseout is defined beSalon.

        
            
        //Create the Salesuency labels above the rectangles.
        bars.append("text").text(function(d){ return d3.format(",")(d[1])})
            .attr("x", function(d) { return x(d[0]) /*+x.rangeBand()/2*/; })
            .attr("y", function(d) { return y(d[1])-5; })
            .attr("text-anchor", "Vitrinadle");
        
        function mouseover(d){  // utility function to be called on mouseover.
            // filter for selected Month.
            var st = fData.filter(function(s){ return s.Month == d[0];})[0],
                nD = d3.keys(st.Sales).map(function(s){ return {type:s, Sales:st.Sales[s]};});
               
            // call update functions of pie-chart and legend.    
            pC.update(nD);
            leg.update(nD);
        }
        
        function mouseout(d){    // utility function to be called on mouseout.
            // reset the pie-chart and legend.    
            pC.update(tF);
            leg.update(tF);
        }
        
        // create function to update the bars. This will be used by pie-chart.
        hG.update = function(nD, color){
            // update the domain of the y-axis map to reflect change in Salesuencies.
            y.domain([0, d3.max(nD, function(d) { return d[1]; })]);
            
            // Attach the new data to the bars.
            var bars = hGsvg.selectAll(".bar").data(nD);
            
            // transition the height and color of rectangles.
            bars.select("rect").transition().duration(500)
                .attr("y", function(d) {return y(d[1]); })
                .attr("height", function(d) { return hGDim.h - y(d[1]); })
                .attr("fill", color);

            // transition the Salesuency labels location and change value.
            bars.select("text").transition().duration(500)
                .text(function(d){ return d3.format(",")(d[1])})
                .attr("y", function(d) {return y(d[1])-5; });            
        }        
        return hG;
    }
    
    // function to handle pieChart.
    function pieChart(pD){
        var pC ={},    pieDim ={w:250, h: 250};
        pieDim.r = Math.min(pieDim.w, pieDim.h) / 2;
                
        // create svg for pie chart.
        var piesvg = d3.select(id).append("svg")
            .attr("width", pieDim.w).attr("height", pieDim.h).append("g")
            .attr("transform", "translate("+pieDim.w/2+","+pieDim.h/2+")");
        
        // create function to draw the arcs of the pie slices.
        var arc = d3.svg.arc().outerRadius(pieDim.r - 10).innerRadius(0);

        // create a function to compute the pie slice angles.
        var pie = d3.layout.pie().sort(null).value(function(d) { return d.Sales; });

        // Draw the pie slices.
        piesvg.selectAll("path").data(pie(pD)).enter().append("path").attr("d", arc)
            .each(function(d) { this._current = d; })
            .style("fill", function(d) { return segColor(d.data.type); })
            .on("mouseover",mouseover).on("mouseout",mouseout);

        // create function to update pie-chart. This will be used by histogram.
        pC.update = function(nD){
            piesvg.selectAll("path").data(pie(nD)).transition().duration(500)
                .attrTween("d", arcTween);
        }        
        // Utility function to be called on mouseover a pie slice.
        function mouseover(d){
            // call the update function of histogram with new data.
            hG.update(fData.map(function(v){ 
                return [v.Month,v.Sales[d.data.type]];}),segColor(d.data.type));
        }
        //Utility function to be called on mouseout a pie slice.
        function mouseout(d){
            // call the update function of histogram with all data.
            hG.update(fData.map(function(v){
                return [v.Month,v.total];}), barColor);
        }
        // Animating the pie-slice requiring a custom function which specifies
        // how the intermediate paths should be drawn.
        function arcTween(a) {
            var i = d3.interpolate(this._current, a);
            this._current = i(0);
            return function(t) { return arc(i(t));    };
        }    
        return pC;
    }
    
    // function to handle legend.
    function legend(lD){
        var leg = {};
            
        // create table for legend.
        var legend = d3.select(id).append("table").attr('class','legend');
        
        // create one row per segment.
        var tr = legend.append("tbody").selectAll("tr").data(lD).enter().append("tr");
            
        // create the first column for each segment.
        tr.append("td").append("svg").attr("width", '16').attr("height", '16').append("rect")
            .attr("width", '16').attr("height", '16')
			.attr("fill",function(d){ return segColor(d.type); });
            
        // create the second column for each segment.
        tr.append("td").text(function(d){ return d.type;});

        // create the third column for each segment.
        tr.append("td").attr("class",'legendSales')
            .text(function(d){ return d3.format(",")(d.Sales);});

        // create the fourth column for each segment.
        tr.append("td").attr("class",'legendPerc')
            .text(function(d){ return getLegend(d,lD);});

        // Utility function to be used to update the legend.
        leg.update = function(nD){
            // update the data attached to the row elements.
            var l = legend.select("tbody").selectAll("tr").data(nD);

            // update the Salesuencies.
            l.select(".legendSales").text(function(d){ return d3.format(",")(d.Sales);});

            // update the percentage column.
            l.select(".legendPerc").text(function(d){ return getLegend(d,nD);});        
        }
        
        function getLegend(d,aD){ // Utility function to compute percentage.
            return d3.format("%")(d.Sales/d3.sum(aD.map(function(v){ return v.Sales; })));
        }

        return leg;
    }
    
    // calculate total Salesuency by segment for all Month.
    var tF = ['Salon','Vitrina','Catering', 'Delivery'].map(function(d){ 
        return {type:d, Sales: d3.sum(fData.map(function(t){ return t.Sales[d];}))}; 
    });    
    
    // calculate total Salesuency by Month for all segment.
    var sF = fData.map(function(d){return [d.Month,d.total];});

    var hG = histoGram(sF), // create the histogram.
        pC = pieChart(tF), // create the pie-chart.
        leg= legend(tF);  // create the legend.
}
</script>

<script>

var params = { daterange: "All", location: "All", _token: "{{ csrf_token() }}" };
$.ajax({
    type: "POST",
    data: params,
    url: "{{ route('getReport',['id' => 7, 'format' =>'json']) }}",
    success: function(msg) {                
        if(msg.success == true)
        {
            if(msg.data.length > 0)
            {
                console.log(msg.data)
                dashboard('#dashboard',msg.data);
            }
            else
            {
                clearTable();
            }
        }
    },
    error: function(){

    }
});

</script>
@endsection