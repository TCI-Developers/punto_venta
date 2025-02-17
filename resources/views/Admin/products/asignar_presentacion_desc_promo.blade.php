@extends('adminlte::page')

@section('title', 'Productos')

@section('css')
    <style>
        .displayNone{
            display:none;
        }
    </style>
@stop

@section('js')
    @include('components.use.notification_success_error')
    <script>
        //funcion para asignar valores a los inputs
        function update(product){            
            swal.fire('Actualización habilitada.', '', 'success');
            $('input[name=part_product_id]').val(product.id);

            $('#btnCancelar').fadeOut(function(){
                $('#btnCancelarUpdate').fadeIn();
            });
            $('#titleBtnSubmit').html('Actualizar');

            //presentaciones
            $('#presentation_type_id').val(product.presentation_product_id).selectpicker('refresh');
            $('#price').val(product.price);
            $('#code_bar').val(product.code_bar);
            $('#stock').val(product.stock);
            //descuento
            $('#tipo_descuento').val(product.tipo_descuento).selectpicker('refresh');
            $('#title_monto_porcentaje').html(product.tipo_descuento == 'monto' ? 'Monto':'Porcentaje');
            $('#basic-addon1').html(product.tipo_descuento == 'monto' ? '$':'%');
            $('#monto_porcentaje').val(product.monto_porcentaje);
            $('#vigencia_cantidad_fecha').val(product.vigencia_cantidad_fecha).selectpicker('refresh');
            if(product.vigencia_cantidad_fecha == 'fecha'){
                $('#vigencia_fecha').val(product.vigencia);
                $('#title_vigencia').html('Fecha');
                    $('#vigencia_cantidad').fadeOut(function(){
                        $('#vigencia_fecha').fadeIn();
                    });
            }else{
                $('#vigencia_cantidad').val(product.vigencia);
                $('#title_vigencia').html('Cantidad');
                    $('#vigencia_fecha').fadeOut(function(){
                        $('#vigencia_cantidad').fadeIn();
                    });
            }
            //Promocion
            $('#promotion_id').val(product.promotion_id).selectpicker('refresh');
        }

        //funcion para validar campos de descuento
        function selectsDescuento(type, value){
            if(type == 'mont_porc'){
                $('#title_monto_porcentaje').html(value == 'monto' ? 'Monto':'Porcentaje');
                $('#basic-addon1').html(value == 'monto' ? '$':'%');
            }else if(type == 'cant_fecha'){
                $('#title_vigencia').html(value == 'fecha' ? 'Fecha':'Cantidad');
                if(value == 'fecha'){
                    $('#vigencia_cantidad').addClass('displayNone').fadeOut(function(){
                        $('#vigencia_fecha').removeClass('displayNone').fadeIn();
                    });
                }else{
                    $('#vigencia_fecha').addClass('displayNone').fadeOut(function(){
                        $('#vigencia_cantidad').removeClass('displayNone').fadeIn();
                    });
                }
            }
        }

        //funcion para validar si la cantidad es mayor o menor que el stock
        function validateCantidadDescuento(cant){
            let stock = $('#stock').val() ?? 0;
            
            if(stock < cant){
                swal.fire('No tienes suficiente stock disponible.', '', 'info');
                $('#vigencia_cantidad').val('');
            }
        }

        //funcion para cancelar la actualizacion
        function cancelarUpdate(){
            $('input[name=part_product_id]').val('');
            $('.inputModal').val('');
            $('.selectpicker').selectpicker('refresh');
            $('#titleBtnSubmit').html('Asignar');
            $('#btnCancelarUpdate').fadeOut(function(){
                $('#btnCancelar').fadeIn();
            });
            swal.fire('Actualización Cancelada.', '', 'success');
        }

        //funcion para mostrar modal agregar presentacion
        function selectPresentation(value){
            if(value == 'new'){
                $('#modal_presentations').modal('show');
            }
        }

        //funcion para cerrar modal de cancelar
        function cancelModal(){
            $('.inputModal').val('');
            $('#modal_presentations').modal('hide');
        }
    </script>
@stop

@section('content')
<form action="{{route('product.store', $product_id)}}" method="post" id="form">
    @csrf
    <input type="hidden" name="part_product_id">
       <div class="card card-primary">
            <div class="card card-header">
                <h1 class="text-center">Asignar presentación</h1>
            </div>
            <!-- Presentaciones -->
            <div class="card card-body"> 
            <div class="row">
                <h3 class="col-12 table-info">Presentaciones</h3>
                <label for="presentation_type_id" class="col-lg-8 col-md-8 col-sm-12">Presentación* <br>
                    <select id="presentation_type_id" name="presentation_type_id" class="form-control selectpicker inputModal" title="Selecciona una presentación" onchange="selectPresentation(this.value)">
                        <option value="new" style="background-color:#32c4fed9;">- Crear presentación</option>
                        @forelse($presentations as $item)
                            <option value="{{$item->id}}">{{$item->type}}</option>
                        @empty
                        @endforelse
                    </select>
                </label>
                <label for="price" class="col-lg-4 col-md-4 col-sm-12">Precio* <br>
                    <input type="number" name="price" id="price" class="form-control inputModal" placeholder="0">
                </label>
                <label for="code_bar" class="col-lg-8 col-md-8 col-sm-12">Codigo <br>
                    <input type="text" name="code_bar" id="code_bar" class="form-control inputModal" placeholder="Codigo" value="">
                </label>
                <label for="stock" class="col-lg-4 col-md-4 col-sm-12">Stock
                    <input type="number" class="form-control inputModal" name="stock" id="stock" placeholder="0">
                </label>
            </div>
            </div>
            <!-- Promociones -->
            {{-- <div class="card card-body">
            <div class="row">
                <h3 class="col-12 table-info">Promociones</h3>
                <label for="promotion_id" class="col-lg-12 col-md-12 col-sm-12">Promociones <br>
                    <select id="promotion_id" name="promotion_id" class="form-control selectpicker inputModal" title="Selecciona un descuento">
                        <option value=""></option>
                        @forelse($promotions as $item)
                            <option value="{{$item->id}}">{{$item->description}}</option>
                        @empty
                        @endforelse
                    </select>
                </label>
            </div>
            </div> --}}
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
                        <input type="number" class="form-control inputModal" name="monto_porcentaje" id="monto_porcentaje" placeholder="0" aria-describedby="basic-addon1">
                    </div>                                
                </label>

                <label for="vigencia_cantidad_fecha" class="col-lg-6 col-md-6 col-sm-12">Vigencia por Cantidad o Fecha
                    <select name="vigencia_cantidad_fecha" id="vigencia_cantidad_fecha" class="form-control selectpicker" title="Selecciona una opción" onchange="selectsDescuento('cant_fecha', this.value)">
                        <option value="fecha" selected>Fecha</option>
                        <option value="cantidad">Cantidad</option>
                    </select>
                </label>
                <label for="vigencia" class="col-lg-6 col-md-6 col-sm-12"><span id="title_vigencia">Fecha</span>
                    <input type="date" class="form-control inputModal" name="vigencia_fecha" id="vigencia_fecha" min="{{ date('Y-m-d') }}" value="{{date('Y-m-d')}}">
                    <input type="number" class="form-control  inputModal displayNone" name="vigencia" id="vigencia_cantidad" onchange="validateCantidadDescuento(this.value)" placeholder="0">
                </label>
            </div>
            </div>

            <div class="card-body">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <span id="titleBtnSubmit">Asignar</span></button>
                <a href="{{route('product.index')}}" class="btn btn-secondary" id="btnCancelar"><i class="fa fa-times"></i> Cancelar</a>
                <button type="button" class="btn btn-danger displayNone" id="btnCancelarUpdate" onClick="cancelarUpdate()"><i class="fa fa-times"></i> Cancelar Actualización</button>
            </div>
            <!-- tabla de presentaciones asignadas -->
            <div class="card-footer">
                    <div class="col-lg-12 col-md-12 col-sm-12 table-responsive" style="max-height:350px;">
                        <br>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Presentación</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Descuento</th>
                                    <th class="text-center">Stock/Vigencia Desc</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="body_table">
                                @forelse($part_to_products as $index => $item)
                                    <tr>
                                        <td>{{$presentation_name[$index]}}</td>
                                        <td class="text-center">$ {{$item->price}}</td>
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
                                <tr><td colspan="6" class="table-warning text-center">Sin presentaciones.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
            </div>
       </div>
</form>
@include('Admin.products._modal_part')
@stop