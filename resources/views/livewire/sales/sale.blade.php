<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Ventas</h2>
        </div>
        <div class="card-body">
            <div class="row col-lg-12 col-md-12 col-sm-12">
                    <div class="col-lg-1 col-md-1 col-sm-12" wire:ignore>
                        <div class="row">
                            <label for="paginate_cant" class="float-left col-lg-12 col-md-12 col-sm-12">Mostrar <br>
                                <input type="number" class="form-control" id="paginate_cant" value="{{$paginate_cant}}" wire:model.live="paginate_cant">
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12" wire:ignore>
                        <div class="row">
                            <label for="" class="col-lg-12 col-md-12 col-sm-12"><br>
                                <a href="{{route('sale.create')}}" class="btn btn-success text-dark"
                                data-toggle="tooltip" data-placement="top" title="Nueva venta"><img src="{{asset('icons/plus.svg')}}" alt="icon plus" width="23"></a>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-8 col-sm-12" wire:ignore>
                        <div class="row text-center">
                            <label for="" class="col-lg-6 col-md-6 col-sm-6" data-toggle="tooltip" data-placement="top" title="Fitro por fecha">Fecha <br>
                                @include('components/use/daterangepicker', ['attr' => 'date', 'position' => 0])
                            </label>
                            
                            <label for="" class="col-lg-6 col-md-6 col-sm-6" data-toggle="tooltip" data-placement="top" title="Filtro por fecha de actualización">Fecha actualización <br>
                                @include('components/use/daterangepicker', ['attr' => 'date_update', 'position' => 1])
                            </label>
                        </div>
                    </div>
                
                    <div class="col-lg-2 col-md-2 col-sm-12" >
                        <div class="row text-center">
                            <label for="search" class="w-80" wire:ignore>Buscar <br>
                                <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                            </label>
                            <div class="w-20">
                            <label for="" class="col-lg-12 col-md-12 col-sm-12" data-toggle="tooltip" data-placement="top" title="Limpiar filtros"><br>
                                <button type="button" class="btn btn-danger" wire:click="showFilter"><img src="{{asset('icons/cancel.svg')}}" alt="icon cancel" width="23"></button>
                            </label>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="text-center">
                        <th class="w-15">Folio</th>
                        <th class="w-20">Fecha</th>
                        <th class="w-20">Metodo de Pago</th>
                        <th class="w-15">Monto</th>
                        <th class="w-20">Ultima actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $item)
                    <tr class="text-center {{$item->status == 2 ? 'table-success':''}}">
                        <td>{{$item->folio}}</td>
                        <td>{{date('d-m-Y', strtotime($item->date))}}</td>
                        <td>{{$item->getPaymentMethod->pay_method}}</td> 
                        <td>$ {{number_format($item->getAmount($item->id),2)}}</td>
                        <td>{{date('d-m-Y H:i:s', strtotime($item->updated_at))}}</td>
                        <td>
                            <a href="{{route('sale.show', $item->id)}}" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Ver venta"
                            ><img src="{{asset('icons/eye.svg')}}" alt="icon eye"></a>
                        </td>
                    </tr>
                    @empty
                    <tr id="trEmpty"><td colspan="6" class="table-warning text-center">Sin Ventas</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <br>
            <div class="col-lg-12 col-md-12 col-sm-12"><span>{{ $sales->links() }}</span></div>
        </div>
        @include('admin.sales._modal')
  </div>