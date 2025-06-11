<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            @if(isset($compra) && $compra->status == 2)
                <h2>Orden de Compra</h2>
            @else
                <h2>{{isset($compra) ? 'Actualizar compra':'Nueva compra'}}</h2>
            @endif
        </div>
        @if(isset($compra) && $compra->status == 4)
            <form action="{{route('compra.storeRecibido', $compra->id)}}" method="post" id="formAction">
        @else
            <form action="{{route('compra.store', isset($compra) ? $compra->id:null )}}" method="post" id="formAction">
        @endif

            @csrf
            <div class="card-body">
                <div class="form-group align-items-center">
                    <a href="{{route('compra.index')}}" class="col-lg-1 col-sm-12 btn btn-success"><i class="fa fa-arrow-left"></i></a>
                    @if(isset($compra))
                    <label for="folio" class="col-lg-2 col-sm-12 float-right">
                        <input type="text" class="form-control text-center" name="folio" id="folio" placeholder="Folio" value="{{isset($compra) ? $compra->folio:''}}" readonly>
                    </label>
                    @if($compra->status == 1)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-warning text-lg mt-1">STATUS: PENDIENTE</label>
                        @elseif($compra->status == 2)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-success text-lg mt-1">STATUS: AUTORIZADO</label>
                        @elseif($compra->status == 3)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-info text-lg mt-1">STATUS: SOLICITADO</label>
                        @elseif($compra->status == 4)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-primary text-lg mt-1">STATUS: RECIBIDO</label>
                        @elseif($compra->status == 5)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-success text-lg mt-1">STATUS: CERRADA</label>
                        @elseif($compra->status == 0)
                        <label class="col-lg-2 col-sm-12 float-right badge badge-danger text-lg mt-1">STATUS: RECHAZADA</label>
                    @endif
                    @endif
                    <hr>
                </div>

                <div class="row form-group">
                    <label for="proveedor_id" class="col-lg-4 col-sm-12" wire:ignore>Proveedor*
                        <select class="form-control" name="proveedor_id" id="proveedor_id" data-live-search="true"
                            onchange="diasCredito(this)" {{$status ?? ''}} required>
                            <option value=""></option>
                            @forelse($proveedores as $item)
                                <option value="{{$item->id}}" days_credit="{{$item->credit_days}}" {{isset($compra) && $compra->proveedor_id == $item->id ? 'selected':'' }}>{{$item->name}} - {{$item->credit_days}}</option>
                            @empty
                            @endif
                        </select>
                    </label>

                    <label for="plazo" class="col-lg-2 col-sm-12">Plazo
                        <input type="number" class="form-control" name="plazo" id="plazo" placeholder="0" value="{{isset($compra) ? $compra->plazo:''}}" {{$status ?? ''}}>
                    </label>

                    <label for="moneda" class="col-lg-2 col-sm-12" wire:ignore>Moneda
                        <select class="form-control selectpicker" name="moneda" id="moneda" data-live-search="true" required {{$status ?? ''}}>
                            <option value="MXN" {{isset($compra) && $compra->moneda == 'MXN' ? 'selected':'selected' }}>Peso</option>
                            <option value="USD" {{isset($compra) && $compra->moneda == 'USD' ? 'selected':'' }}>Dolar</option>
                        </select>
                    </label>
                    <label for="tipo" class="col-lg-4 col-sm-12" wire:ignore>Tipo Compra
                        <select class="form-control selectpicker" name="tipo" id="tipo" data-live-search="true" required {{$status ?? ''}}>
                            <option value="OC" {{isset($compra) && $compra->tipo == 'OC' ? 'selected':'selected' }}>Orden de compra</option>
                            <option value="S" {{isset($compra) && $compra->tipo == 'S' ? 'selected':'' }}>Servicio</option>
                        </select>
                    </label>

                    @if(isset($compra) && $compra->status != 1)
                    <label for="programacion_entrega" class="col-lg-4 col-sm-12">Programación de entrega
                        <input type="date" class="form-control" name="programacion_entrega" id="programacion_entrega" min="{{date('Y-m-d')}}" {{$compra->status != 2 ? $status:''}}
                        wire:change="setFechaEntrega($event.target.value)" value="{{isset($compra) ? $compra->programacion_entrega:date('Y-m-d')}}">
                    </label>
                    @endif

                    @if(isset($compra))
                    @if($compra->status == 4 || $compra->status == 5)
                    <label for="fecha_recibido" class="col-lg-4 col-sm-12 float-right">Fecha Recibido
                        <input type="date" class="form-control" name="fecha_recibido" id="fecha_recibido" placeholder="" value="{{isset($compra) ? $compra->fecha_recibido:''}}" disabled>
                    </label>
                    @endif
                    @endif

                    @if(isset($compra))
                    @if($compra->status == 4 || $compra->status == 5)
                    <label for="fecha_vencimiento" class="col-lg-4 col-sm-12">Vencimiento
                        <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" value="{{isset($compra) ? $compra->fecha_vencimiento:''}}" disabled>
                    </label>
                    @endif
                    @endif

                    <label for="observaciones" class="col-12">Observaciones
                        <textarea class="form-control" name="observaciones" id="observaciones" {{$status ?? ''}}>{{isset($compra) ? $compra->observaciones:''}}</textarea>
                    </label>


                    @if(isset($compra))
                    <label for="subtotal" class="col-lg-4 col-sm-12">Subtotal Productos
                        <input type="text" class="form-control" name="subtotal" id="subtotal" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->subtotal, 2):''}}" disabled>
                    </label>
                    <label for="impuesto_productos" class="col-lg-4 col-sm-12">Impuestos Productos
                        <input type="text" class="form-control" name="impuesto_productos" id="impuesto_productos" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->impuesto_productos, 2):''}}" disabled>
                    </label>
                    <label for="total" class="col-lg-4 col-sm-12">Total
                        <input type="text" class="form-control" name="total" id="total" placeholder="0.00" value="$ {{isset($compra) ? number_format($compra->total, 2):''}}" disabled>
                    </label>
                    @endif
                </div>
            </div>
            <div class="card-footer text-right">
                @if(isset($compra))
                    @if($compra->status == 1)
                    <a href="{{route('compra.status', [$compra->id, 2])}}" class="btn btn-success"><i class="fa fa-circle"></i> Autorizar</a>
                    @elseif($compra->status == 2)
                    <button type="button" class="btn btn-info" onclick="btnSolicitar({{$compra_id}})"><i class="fa fa-circle"></i> Solicitar</button>
                    <a href="{{route('compra.status', [$compra->id, 0])}}" class="btn btn-danger"><i class="fa fa-circle"></i> Rechazar</a>
                    @elseif($compra->status == 3)
                    <a href="{{route('compra.status', [$compra->id, 4])}}" class="btn btn-primary"><i class="fa fa-circle"></i> Recibido</a>
                    <a href="{{route('compra.status', [$compra->id, 0])}}" class="btn btn-danger"><i class="fa fa-circle"></i> Rechazar</a>
                    @elseif($compra->status == 4)
                    <button type="button" class="btn btn-primary" onclick="btnCerrar()"><i class="fa fa-circle"></i> Cerrar compra</button>
                    @endif
                @endif

                @if(isset($compra) && $compra->status == 1)
                <button type="button" class="btn btn-primary" onClick="btnUpdate()"><i class="fa fa-check"></i> Actualizar</button>
                @elseif(!isset($compra))
                <button type="submit" class="btn btn-primary" onclick="validateForm()"><i class="fa fa-check"></i> {{isset($compra) ? 'Actualizar':'Guardar'}}</button>
                @endif
            </div>

            @include('Admin.compras.section_products')
        </form>
</div>