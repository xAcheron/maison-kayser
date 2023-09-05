@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" href="{{ route('ubermenu') }}" aria-expanded="true">
		<i class="material-icons">local_dining</i>
		<p> Uber Eats <b class="caret"></b> </p>
    </a>
	<div class="collapse show" style="">
		<ul class="nav">
			<li class="nav-item @if($seccion == 'menu') active @endif ">
				<a class="nav-link" href="{{ route('menuubereats') }}">
					<i class="material-icons">shopping_basket</i>
					<p> Menu</p>
				</a>
			</li>
		</ul>
	</div>
</li>
@endsection