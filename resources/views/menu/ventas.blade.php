@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" data-toggle="collapse" href="#pagesExamples" aria-expanded="true">
		<i class="material-icons">assessment</i>
		<p> Dashboard <b class="caret"></b> </p>
    </a>
	<div class="collapse show" id="pagesExamples" style="">
		<ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('ventaMensual') }}">
                    <i class="material-icons">paid</i>
                    <p> Venta Mensual</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('getLastYear') }}">
                    <i class="material-icons">trending_up</i>
                    <p> Last Year</p>
                </a>
            </li>
		</ul>
	</div>
</li>
@endsection