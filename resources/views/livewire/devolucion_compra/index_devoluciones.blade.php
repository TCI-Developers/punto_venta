<div class="card card-primary" style="height:90vh;">
        <div class="form-group card-header with-border text-center">
            <h2>Devoluciones {{$status == 0 ? 'Inhabilitadas':''}}</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                @if(auth()->user()->hasPermissionThroughModule('devoluciones', 'punto_venta', 'create'))
                <a class="btn btn-primary" href="{{route('devoluciones.indexCompras')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva devolución"><i class="fa fa-plus"> Matriz</i></a>

                <a class="btn btn-success" href="{{route('devoluciones.showListadoVentas')}}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Nueva devolución"><i class="fa fa-plus"> Venta</i></a>
                
                <!-- <a href="{{route('devoluciones.index', $status == 0 ? 1:0)}}" class="btn {{$status == 0 ? 'btn-success':'btn-secondary'}} float-right" data-bs-toggle="tooltip" data-bs-placement="top" 
                    title="Usuarios {{$status == 0 ? 'Habilitados':'Inhabilitados'}}"><i class="fa fa-folder"></i></a> -->
                @endif
            </div>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{$activo_sale}}" style="cursor:pointer;" wire:click="pestaña('sale')">Ventas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$activo_matriz}}" style="cursor:pointer;" wire:click="pestaña('matriz')">Matriz</a>
                </li>
            </ul>

            <div class="table-responsive">
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Folio Venta</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                        <th>Descuento</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoluciones as $index => $item)
                       <tr>
                            <td class="text-center">{{$type == 'sale' ? $item->getSale->folio : $item->getCompra->folio}} </td>
                           <td class="text-center">{{date('d/m/Y', strtotime($type == 'sale' ? $item->fecha_devolucion : $item->date))}}</td>
                           <td class="text-center">{{$item->cantidad}}</td>
                           <td>{{$item->description}}</td>
                           <td class="text-center">${{number_format($item->total_descuentos, 2)}}</td>
                           <td class="text-center">${{number_format($type == 'sale' ? ($item->total_devolucion - $item->total_descuentos):($item->total - $item->total_descuentos), 2)}}</td>
                            <td class="text-center">
                                @if($type == 'sale')
                                <a href="{{route('devoluciones.showDevSale', $item->id)}}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                                <a href="{{route('ticket.devolution', $item->id)}}" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                    target="_blank" data-placement="top" title="Ver ticket">
                                    <i class="fa fa-file"></i></a>
                                @else
                                <a href="{{route('devoluciones.showDevMatriz', $item->id)}}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                                <a href="{{route('ticketMatriz.devolution', $item->id)}}" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                    target="_blank" data-placement="top" title="Ver ticket">
                                    <i class="fa fa-file"></i></a>
                                @endif
                            </td>
                       </tr>
                    @empty
                    <tr><td colspan="7" class="table-warning text-center">Sin devoluciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
  </div>