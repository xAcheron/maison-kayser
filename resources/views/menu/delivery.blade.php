@section('appmenu')
    <style>
        .sidebar .nav li.active>[data-toggle="collapse"] {
            background-color: #ff9800;
            color: #3C4858;
            box-shadow: none;
        }
    </style>
    <li class="nav-item active">
        <a class="nav-link" data-toggle="collapse" href="#pagesExamples" aria-expanded="true">
            <i class="material-icons">motorcycle</i>
            <p> Delivery <b class="caret"></b> </p>
        </a>
        <div class="collapse show" id="pagesExamples" style="">
            <ul class="nav">

            </ul>
        </div>
    </li>
@endsection
