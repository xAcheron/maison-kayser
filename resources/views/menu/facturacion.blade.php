@section('appmenu')
    <li class="nav-item @if ($seccion == 'index') active @endif">
        <a class="nav-link active" href="{{ route('invoicing') }}">
            <i class="material-icons">receipt</i>
            <p> Facturación </p>
        </a>
    </li>
    <li class="nav-item @if ($seccion == 'conversion') active @endif">
        <a class="nav-link" href="{{ route('consultaConversiones') }}">
            <i class="material-icons">note_add</i>
            <p> Conversion </p>
        </a>
    </li>
    @if (!empty($espPerm) && $espPerm == 1)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('contactosPage') }}">
                <i class="material-icons">contacts</i>
                <p> Contactos </p>
            </a>
        </li>
    @endif
@endsection
