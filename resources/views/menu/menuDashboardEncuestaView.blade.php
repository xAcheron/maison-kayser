@section('appmenu')
<style>
	.sidebar .nav li.active > a, .sidebar .nav li.active > a i{
		color: #ff9800;
	}   
</style>
<li class="nav-item">
	<a class="nav-link"  aria-expanded="true">
		<i class="material-icons">fact_check</i>
		<p> Dashboard Encuesta</p>
    </a>
	<div class="collapse show">
		<ul class="nav">
            <li class="nav-item">
				<a class="nav-link"  href="{{ route('getFormResults') }}">
					<i class="material-icons">visibility</i>
					<p>Visualizar Resultados</p>
				</a>
            </li>
            <li class="nav-item">
				<a class="nav-link"  href="{{ route('dashboardRanking') }}">
					<i class="material-icons">moving</i>
					<p>Ranking</p>
				</a>
            </li>
		</ul>
	</div>
</li>
@endsection
