
<div class="card card-primary">
    <div class="card-header">
        <h2 class="text-center">
            <a href="{{route('devoluciones.indexCompras')}}" class="btn btn-success btn-sm float-left"><i class="fa fa-arrow-left"></i></a>
            Devolución Compra {{$compra->folio}}
        </h2>
    </div>
    <div class="card-body">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                Información de la Compra
            </div>
        <form action="{{ route('devoluciones.storeMatriz', $compra->id) }}" method="POST">
        @csrf
            <div class="card-body">
                <div class="form-group" style="display:flex; align-items: center;">
                    <div class="col-md-4">
                        <strong>Folio:</strong> {{ $compra->folio }}
                    </div>
                     <div class="col-md-4">
                        <strong>Fecha recibido:</strong> {{ $compra->fecha_recibido }}
                    </div>
                     <div class="col-md-4">
                        <strong>Fecha devolución</strong> <input type="date" name="date" id="date" value="{{date('Y-m-d')}}" class="form-control" required>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Subtotal:</strong> $ {{ number_format($compra->subtotal, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Total impuestos:</strong> $ {{ number_format($compra->impuesto_productos, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Descuentos:</strong> $ {{ number_format($compra->descuentos, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Total:</strong> $ {{ number_format($compra->total, 2) }}
                    </div>
                </div>
                <hr>
                <div class="row">
                     <div class="col-md-4">
                         <select name="driver" id="driver" class="form-control" placeholder="Selecciona chofer" required>
                            <option value="">Selecciona un chofer</option>
                            @foreach($drivers ?? [] as $item)
                                <option value="{{$item->name}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <strong>Observaciones:</strong> {{ $compra->observaciones }}
                    </div>
                </div>
            </div>
        </div>
    

        <div class="table-responsive">
            <div class="col-lg-3 col-md-3 col-sm-12 float-left">
                <button type="submit" class="btn btn-primary mt-3"><i class="fa fa-check"></i> Guardar devolución</button>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-12 float-right">
            <div class="row">
                <label for="search" class="col-lg-12 col-md-12 col-sm-12">Buscar <br>
                    <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                </label>
            </div>
            </div>

           
            <table class="table table-striped table-bordered" id="table">
                <thead>
                    <tr class="text-center bg-info text-white">
                        <th colspan="9">Detalles Compra</th>
                    </tr>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Codigo Producto</th>
                        <th>Impuesto</th>
                        <th>Total Impuestos</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Cantidad a devolver</th>
                        <th>Devolver todo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compra_detalles ?? [] as $item)
                        <tr class="text-center">
                            <td>{{$item->getEntrada->recibido}}</td>
                            <td class="text-sm">{{$item->code_product}}</td>
                            <td>{{$item->taxes}}</td>
                            <td>$ {{number_format($item->impuestos, 2)}}</td>
                            <td class="text-sm">$ {{number_format($item->precio_unitario, 2)}}</td>
                            <td class="text-sm">$ {{number_format($item->subtotal, 2)}}</td>
                            <td class="text-sm">$ {{number_format($item->total, 2)}}</td>
                            <td>
                                <input type="number" name="devoluciones[{{$item->id}}][cantidad]" 
                                    class="form-control cantidad-input" 
                                    value="0" min="0" 
                                    max="{{$item->getEntrada->recibido}}">
                            </td>
                            <td>
                                <input type="checkbox" class="devolver-todo-checkbox" data-max="{{$item->getEntrada->recibido}}">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="table-warnign">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </form>
    </div>
</div>
