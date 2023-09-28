<div class="col-12">
    <div class="card">
        <div class="card-header">
        <h4 class="card-title">Evaluacion</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12"> 
                    <div class="container-fluid p-2" style="overflow:auto !important; height: 20vh !important;">
                        <table class="table table-condensed table-striped">
                            <thead>
                            </thead>
                            <tbody id="bmesTable"> 
                                @foreach( $content as $h)
                                    <tr>
                                    @foreach( $h as $c)
                                        <td>{{ $c }}</td>
                                    @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>