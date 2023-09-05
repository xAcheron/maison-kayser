@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" href="{{route('panelAlex')}}" aria-expanded="true">
		<i class="material-icons">assessment</i>
		<p> Dashboard <b class="caret"></b> </p>
    </a>
	<div class="collapse show" id="pagesExamples" style="">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="{{route('bitacora')}}">
                    <i class="material-icons">assignment_turned_in</i>
                    <p> Bitacora </p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('compara')}}">
                    <i class="material-icons">assignment_turned_in</i>
                    <p> Comparativo </p>
                </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link" href="{{route('evaluacion')}}">
                        <i class="material-icons">check_circle</i>
                        <p> Evaluacion </p>
                    </a>
                </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('redes')}}">
                    <i class="material-icons">thumb_up_alt</i>
                    <p> Satisfaccion Cliente </p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('auditi')}}">
                    <i class="material-icons">assignment</i>
                    <p> Experiencia Cliente </p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('ubercal')}}">
                    <i class="material-icons">local_taxi</i>
                    <p> Uber </p>
                </a>
            </li>
			<li class="nav-item">
                <a class="nav-link" href="{{ route('rhemps')}}">
                    <i class="material-icons">transfer_within_a_station</i>
                    <p> RH </p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dcalidad')}}">
                    <i class="material-icons">verified_user</i>
                    <p> Calidad, Costo </p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dcalidad')}}">
                    <i class="material-icons">watch_later</i>
                    <p> Proceso de Pedidos </p>
                </a>
            </li>
        </ul>
    </div>
</li>
@endsection