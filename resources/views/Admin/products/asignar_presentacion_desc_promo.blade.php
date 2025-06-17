<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentaciones</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.use.link_scripts_glabal')
    <script src="{{asset('js/products/asignar_presentacion_desc_promo,js')}}"></script>
</head>
<body>
    <main class="content">
         @include('components.use.nav-slider')
        @include('components.use.notification_success_error')

    <form action="{{route('product.store', $product->id)}}" method="post" id="form">
    @csrf
        <input type="hidden" name="part_product_id">
        <div class="card card-primary">
            <div class="card card-header">
                <h3 class="text-center">Asignar presentación {{$type}}</h3>
                <h4 class="text-center">{{$product->code_product}}</h4>
            </div>
            <!-- Presentaciones -->
            <div class="card card-body"> 
            <div class="row">
                <h3 class="col-12 table-info">Presentaciones</h3>
                <label for="unidad_sat_id" class="col-lg-4 col-md-4 col-sm-12">Presentación* <br>
                    <select id="unidad_sat_id" name="unidad_sat_id" class="form-control inputModal" 
                            title="Selecciona una unidad" data-live-search="true">
                        <option value="" {{$type == 'only_edit' ? 'selected':''}}></option>
                        @forelse($unidades_sat as $item)
                            <option value="{{$item->id}}" {{$type != 'only_edit' && $product->unit === $item->clave_unidad ? 'selected':''}}>{{$item->clave_unidad}} - {{$item->name}}</option>
                        @empty
                        @endforelse
                    </select>
                </label>

                @if($type == 'only_edit')
                    @include('Admin.products.inputs._inputs_presentacion'){{-- Campos presentacion--}}
                    @include('Admin.products.inputs._inputs_despiezado'){{-- Campos de despieze--}}
                @elseif(!$type) {{-- Campos sin despieze--}}
                    @include('Admin.products.inputs._inputs_presentacion'){{-- Campos presentacion--}}
                @else 
                    @include('Admin.products.inputs._inputs_despiezado'){{-- Campos de despieze--}}
                @endif
                <label for="code_bar" class="col-lg-4 col-md-4 col-sm-12">Codigo <br>
                    <input type="text" name="code_bar" id="code_bar" class="form-control inputModal" placeholder="Codigo" value="">
                </label>
                <label for="stock" class="col-lg-4 col-md-4 col-sm-12">Stock General
                    <input type="number" class="form-control inputModal" name="stock" id="stock" step="0.01" value="{{isset($type) ? $product->getPartToProduct->stock??0:0}}" required {{isset($type) ? 'readonly':''}}>
                </label>                
            </div>
            </div>
            <!-- Descuentos -->
            <div class="card card-body">
            <div class="row">
                <h3 class="col-12 table-info">Descuentos</h3>
                <label for="tipo_descuento" class="col-lg-6 col-md-6 col-sm-12">Monto o Porcentaje
                    <select name="tipo_descuento" id="tipo_descuento" class="form-control selectpicker" title="Selecciona una opción" onchange="selectsDescuento('mont_porc', this.value)">
                        <option value="monto" selected>Monto</option>
                        <option value="porcentaje">Porcentaje</option>
                    </select>
                </label>
                <label for="monto_porcentaje" class="col-lg-6 col-md-6 col-sm-12"><span id="title_monto_porcentaje">Monto</span>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">$</span>
                        </div>
                        <input type="number" class="form-control inputModal" name="monto_porcentaje" id="monto_porcentaje" placeholder="0" aria-describedby="basic-addon1" step="0.01">
                    </div>                                
                </label>

                <label for="vigencia_cantidad_fecha" class="col-lg-6 col-md-6 col-sm-12">Vigencia por Cantidad o Fecha
                    <select name="vigencia_cantidad_fecha" id="vigencia_cantidad_fecha" class="form-control" title="Selecciona una opción" onchange="selectsDescuento('cant_fecha', this.value)">
                        <option value="fecha" selected>Fecha</option>
                        <option value="cantidad">Cantidad</option>
                    </select>
                </label>
                <label for="vigencia" class="col-lg-6 col-md-6 col-sm-12"><span id="title_vigencia">Fecha</span>
                    <input type="date" class="form-control inputModal" name="vigencia_fecha" id="vigencia_fecha" min="{{ date('Y-m-d') }}" value="{{date('Y-m-d')}}">
                    <input type="number" class="form-control  inputModal d-none" name="vigencia" id="vigencia_cantidad" onchange="validateCantidadDescuento(this.value)" placeholder="0">
                </label>
            </div>
            </div>

            <div class="card-body text-right">
                <a href="{{route('product.index')}}" class="btn btn-success float-left"><i class="fa fa-arrow-left"></i></a>
                <button type="{{$type == 'only_edit' ? 'button':'submit'}}" class="btn btn-primary {{ $type=='only_edit' ? 'd-none':''}}" id="btnSubmit"><i class="fa fa-check"></i> <span id="titleBtnSubmit">Asignar</span></button>
                <a href="{{route('product.index')}}" class="btn btn-secondary" id="btnCancelar"><i class="fa fa-times"></i> Cancelar</a>
                <button type="button" class="btn btn-danger d-none" id="btnCancelarUpdate" onClick="cancelarUpdate()"><i class="fa fa-times"></i> Cancelar Actualización</button>
            </div>
            <!-- tabla de presentaciones asignadas -->
            <div class="card-footer">
                    <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="max-height:350px;">
                        <br>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th>Codigo</th>
                                    <th>Presentación</th>
                                    <th>Precio</th>
                                    <th>Mayoreo</th>
                                    <th>Stock</th>
                                    <th>Descuento</th>
                                    <th>Stock/Vigencia Desc</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="body_table">
                                @forelse($part_to_products as $index => $item)
                                    <tr>
                                        <td>{{$item->code_bar}}</td>
                                        <td>{{$item->getUnidadSat->clave_unidad}} - {{$item->getUnidadSat->name}}</td>
                                        <td class="text-center">$ {{$item->price}}</td>
                                        <td class="text-center">{{$item->cantidad_mayoreo > 0 ? '$ '.$item->price_mayoreo:'N/A'}}</td>
                                        <td class="text-center">{{$item->stock}}</td>
                                        <td class="text-center">
                                            @if($item->monto_porcentaje > 0)
                                                {{ $item->tipo_descuento == 'monto' ? '$ '.$item->monto_porcentaje: '% '.$item->monto_porcentaje }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">{{$item->vigencia}}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm" 
                                            onClick="update({{$item}})"><i class="fa fa-edit"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                <tr><td colspan="8" class="table-warning text-center">Sin presentaciones.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
            </div>
       </div>
    </form>
    @include('Admin.products._modal_part')
</main>   
</body>
</html>