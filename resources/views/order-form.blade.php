@extends('adminlte::page')

@section('title', 'Venta')

@section('content')
<x-header-nav>
    <ul class="navbar-nav"> 
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
    <div class="container-order">
        <div class="row">
            <div class="col-sm-4">
            <div class="form-group">
                <label for="sltCustomer">Cliente</label>
                <select class="custom-select select-customer" id="sltCustomer" required onChange="changeCustomer(this.value)">
                    <option value="1">Publico en general</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                </select>
                </div>
            </div>
            
            
            <div class="form-group col-md-4">
                    <label for="txtInformation">Información del cliente</label>
                    <textarea class="form-control" id="txtInformation" rows="2" readonly></textarea>
            </div> 
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group col-md-12">
                    <label for="listPrice">Vendedor</label>
                    <select class="custom-select select-seller" id="listPrice" required>
                        <option disabled selected>Elige una opción</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
            </div>
            
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/order/order-form.css">
@stop

@section('js')
    <script src="/js/order.js"></script>
@stop

