@extends('layouts.reports')
@include('menu.reportsPro', ['seccion' => 'MenuEngineering'])
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header card-header-icon card-header-info">
            <div class="card-icon">
                <i class="material-icons">timeline</i>
            </div>
            <h4 class="card-title">Cash
                <small> - Filters</small>
            </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        Business Dates:<br>
                        <input type="text" class="filter-components" style="width:100%;" name="daterange" id="daterange" value="{{ date("Y-m-d") }} - {{ date("Y-m-d") }}" />
                    </div>
                    <div class="col-3">
                        Location:<br>
                        <select class="select2-item" id="location" data-size="7" style="width:100%;" title="Location">
                            <option disabled selected>Select a location</option>
                            <option value="52">Tzuco</option>
                          </select>
                    </div>                    
                    <div class="col-2 align-items-center">
                        <button id="runReport" class="btn btn-info">Run Report</button>
                    </div>                    
                    <!--div class="col-2 align-items-center">
                        <button id="exportReport" class="btn">
                            <span class="btn-label">
                                <i class="material-icons">table_view</i>
                            </span>
                            Export
                        </button>
                    </div-->
                </div>        
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
            <h4 class="card-title">Report Area</h4>
            </div>
            <div class="card-body">
               <div class="row">
                   <div class="col-12"> 
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Tender</th>							
                                    <th>Net Sales</th>
                                    <th>Fecha</th>
                                </tr>
                            </head>
                            <tbody id="baseTable"> 
                                
                            </tbody>
                       </table>
                   </div>
               </div> 
            </div>
        </div>
    </div>
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
<script type="text/javascript">

$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'right',
    minYear: 2019,
    maxYear: {{ date("Y") }},
    locale: {
      format: 'YYYY-MM-DD'
    },
    ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 14 Days': [moment().subtract(13, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
  });
  
  $('.select2-item').select2();

  $("#exportReport").on("click",function(e) {

    if(validParams())
    {
        let url = "{{ route('getReport',['id' => 1, 'format' =>'xlsx']) }}";
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

        var params = { daterange: $("#daterange").val(), location: $("#location").val(), _token: "{{ csrf_token() }}" };
        
        $.ajax({
            type: "POST",
            url: "{{ route('getReport',['id' => 2, 'format' =>'json']) }}",
            data: params,
            success: function(msg) {
                if(msg.success == true)
                {
                    if(msg.data.length > 0)
                    {
                        makeTable(msg.data);
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

}

function makeTable(data)
{
    clearTable();
    let htmlTemplate = "<td class=\"text-left\"></td><td class=\"text-left\">:tender</td><td class=\"text-right\">:netSales</td><td class=\"text-right\">:fecha</td>";
    let domTarget = '#baseTable';
    let tr = document.createElement('tr');
    let target = document.querySelector(domTarget);
    let innerHTML="";
    var formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        maximumFractionDigits: 2, // (causes 2500.99 to be printed as $2,501)
    });
    for(var i=0;i<data.length;i++)
    {
        tr = document.createElement('tr');
        innerHTML=htmlTemplate;
        tr.id = "MjGrp"+data[i].idMajor;
        tr.dataset.open = 0;

        if(data[i].tender == "Total")
        {
            innerHTML = innerHTML.replace(':tender', "<b>"+data[i].tender+"</b>");
            innerHTML = innerHTML.replace(':netSales', "<b>"+formatter.format(data[i].netSales)+"</b>");
            innerHTML = innerHTML.replace(':fecha', "<b></b>");
        }
        else
        {
            innerHTML = innerHTML.replace(':tender', data[i].tender);
            innerHTML = innerHTML.replace(':netSales', formatter.format(data[i].netSales));
            innerHTML = innerHTML.replace(':fecha', data[i].fecha);
        }

        tr.innerHTML = innerHTML;
        target.appendChild(tr);
    }
}

function clearTable()
{
    var tabla = document.getElementById("baseTable");
    tabla.innerHTML = "";
    //table.textContent = "";
}

function validParams() {
    if($("#daterange").val() != "" && $("#location").val() != "" && $("#daterange").val() != null && $("#location").val() != null )
        return true;
    return false;
}

</script>
<style>
.filter-components {
    background-color: #fff !important;
    border: 1px solid #aaa !important;
    border-radius: 4px !important;
    color: #444 !important;
    line-height: 24px !important;
}
</style>
@endsection