@extends('adminlte::page')


@section('title', 'Productos')

@section('modal-title', 'Agregar producto')

@section('modal-body')
  <form action="{{route('product.store')}}" method="post">
    @CSRF
    @include('components.form-products')

    <div class="modal-footer row"> 
      <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
      <button type="submit" class="btn btn-primary" >Guardar</button>
    </div>
  </form>
@stop

@section('content_header')
    <h1>Productos</h1>
@stop

@section('content')
<x-header-nav>
    <ul class="navbar-nav "> 
      <li class="nav-item header-option">
            <button class="btn btn-secondary" data-toggle="modal" data-target="#modal-product">
                <i class="fas fa-plus-square"></i> Nuevo
            </button>
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

<table class="table">
  <thead class="thead-light">
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr><td colspan="4" class="text-center table-warning">Sin registros.</td></tr>
  </tbody>
</table>
@stop

@section('css')
    <link rel="stylesheet" href="/css/products/products.css">
@stop

@section('js')
    <script> console.log('Products'); </script>
@stop

@extends('layouts.modal')