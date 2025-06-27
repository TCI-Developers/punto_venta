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
                                data-toggle="tooltip" data-placement="top" title="Nueva venta"><i class="fa fa-plus"></i></a>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-8 col-sm-12" wire:ignore>
                       {{-- <div class="row text-center">
                            <label for="" class="col-lg-6 col-md-6 col-sm-6" data-toggle="tooltip" data-placement="top" title="Fitro por fecha">Fecha <br>
                                <div id="">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </label>
                            
                            <label for="" class="col-lg-6 col-md-6 col-sm-6" data-toggle="tooltip" data-placement="top" title="Filtro por fecha de actualización">Fecha actualización <br>
                                @include('components/use/daterangepicker', ['attr' => 'date_update', 'position' => 1])
                            </label>
                        </div> --}}
                    </div>
                
                    <div class="col-lg-2 col-md-2 col-sm-12" >
                        <div class="row text-center">
                            <label for="search" class="" wire:ignore>Buscar <br>
                                <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                            </label>
                            {{--<div class="w-20">
                            <label for="" class="col-lg-12 col-md-12 col-sm-12" data-toggle="tooltip" data-placement="top" title="Limpiar filtros"><br>
                                <button type="button" class="btn btn-danger" wire:click="showFilter">
                                    <i class="fa fa-times"></i></button> 
                            </label>
                            </div>--}}
                        </div>
                    </div>
            </div>
            <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="text-center">
                        @if(Auth::User()->hasAnyRole(['root','admin']))
                        <th class="w-10">Usuario</th>
                        @endif
                        <th class="w-10">Folio</th>
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
                        @if(Auth::User()->hasAnyRole(['root','admin']))<td class="text-sm">{{$item->getUser->name}}</td>@endif
                        <td>{{$item->folio}}</td>
                        <td>{{date('d-m-Y', strtotime($item->date))}}</td>
                        <td>{{$item->getPaymentMethod->pay_method}}</td> 
                        {{-- <td>$ {{number_format($item->getAmount($item->id),2)}}</td> --}}
                        <td>$ {{number_format($item->total_sale,2)}}</td>
                        <td>{{date('d-m-Y H:i:s', strtotime($item->updated_at))}}</td>
                        <td>
                            @if(auth()->user()->hasPermissionThroughModule('ventas', 'punto_venta', 'update'))
                            <a href="{{route('sale.show', $item->id)}}" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Ver venta">
                                <i class="fa fa-eye"></i></a>
                            @endcan

                            <a href="{{route('ticket.sale', $item->id)}}" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                target="_blank" data-placement="top" title="Ver ticket">
                                <i class="fa fa-file"></i></a>
                            @if(!count($item->getDetails))
                            <a href="{{route('sale.destroy', $item->id)}}" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar venta">
                                <i class="fa fa-trash"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="trEmpty"><td colspan="{{Auth::User()->hasAnyRole(['root','admin']) ? '7':'6'}}" class="table-warning text-center">Sin Ventas</td></tr>
                    @endforelse
                    
                    @if(!Auth::User()->hasAnyRole(['admin', 'root']))
                    <tr>
                        <td colspan="3" class="text-bold text-right">Total Tarjeta: $ {{number_format($total_tarjeta,2) ?? 0}}</td>
                        <td colspan="3" class="text-bold text-right">Total Efectivo: $ {{number_format($total_efectivo,2) ?? 0}}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            </div>
            <br>
            <div class="col-lg-12 col-md-12 col-sm-12"><span>{{ $sales->links() }}</span></div>
        </div>
        @include('admin.sales._modal')
  </div>