@section('appmenu')
<li class="nav-item active">
	<a class="nav-link" data-toggle="collapse" href="#pagesExamples" aria-expanded="true">
		<i class="material-icons">store</i>
		<p> Pedidos <b class="caret"></b> </p>
    </a>
	<div class="collapse show" id="pagesExamples" style="">
		<ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('costoVenta') }}">
                    <i class="material-icons">shopping_basket</i>
                    <p> Costo</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('ventaTeorica') }}">
                    <i class="material-icons">shopping_basket</i>
                    <p> Venta Teorica</p>
                </a>
            </li>
		</ul>
	</div>
</li>
@endsection