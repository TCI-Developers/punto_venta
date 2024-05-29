@extends('adminlte::page')

@section('title', 'Ventas')


@section('content_header')
    <h1>Ventas</h1>
@stop

@section('content')
<x-header-nav >
    <ul class="navbar-nav"> 
      <li class="nav-item header-option">
            <a class="btn btn-secondary" href="{{route('order')}}">
                <i class="fas fa-plus-square"></i> Nuevo</a>
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
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>Larry</td>
      <td>the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Products'); </script>
@stop