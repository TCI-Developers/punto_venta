@extends('adminlte::page')

@section('title', 'Facturación')

@section('content_header')
    <h1>Facturación</h1>
@stop

@section('content')
<x-header-nav route="">
    <ul class="navbar-nav mr-auto"> 
      <li class="nav-item header-option">
            <a class="btn btn-secondary" href="{{route('stock')}}">
                <i class="fas fa-plus-square"></i> Nueva venta</a>
      </li>
      <li class="nav-item header-option">
            <button class="btn btn-light" data-toggle="collapse" data-target="#filterCollapse">
                <i class="fas fa-filter"></i> Filtrar
            </button>
      </li>
      <li class="nav-item header-option">
            <button class="btn btn-light" data-toggle="modal" data-target="#staticBackdrop">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
      </li>
    </ul>
</x-header-nav>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Products'); </script>
@stop