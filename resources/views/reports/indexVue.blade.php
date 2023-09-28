@extends('layouts.vue')
@section('aditionalScripts')
<script type="module" crossorigin src="/assets/index.js"></script>
<script>
	const menuStruct = {!! json_encode($menu) !!}
</script>
<link rel="stylesheet" href="/assets/index.css"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,1,0" />
<style>
    :root{ 
		--_ui5_card_header_padding: 1rem 1rem 0rem 1rem !important; 
		--_ui5_card_header_subtitle_margin_top: 0rem !important;
	}
	body, h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4 {
		line-height: 1em !important;
	}
	.ui5-card-footer{padding: 0.75rem 1.25rem;!important}
</style>
@endsection