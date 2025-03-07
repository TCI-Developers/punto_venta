<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Productos
                <a href="{{route('product.showUploadExcel')}}" class="btn btn-success btn-sm float-right"><i class="fa fa-upload"></i> Carga Masiva Precios/Stock</a>
            </h2>
        </div>
        <div class="card-body">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <label for="paginate_cant" class="float-left">Mostrar <br>
                    <input type="number" class="form-control" id="paginate_cant" value="{{$paginate_cant}}" wire:model.live="paginate_cant">
                </label>
                <label for="search" class="float-right">Buscar <br>
                    <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                </label>
            </div>
            <div class="table-responsive">
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Codigo Producto</th>
                        <th>Descripción</th>
                        <th>Linea</th>
                        <th>Unidad</th>
                        <th>Existencias</th>
                        <th>Precio Unitario</th>
                        <th>Precio Mayoreo</th>
                        <th>Precio Despiece</th>
                        @if(Auth::User()->hasAnyRole(['root', 'admin']))
                        <th>Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $item)
                    <tr id="tr-{{$item->id}}">
                        <td class="code_product">{{$item->id}} - {{$item->code_product}}</td>
                        <td class="text-center">{{$item->description}}</td>
                        <td class="text-center">{{$item->getBrand->name}}</td>
                        <td class="text-center">{{$item->unit}}</td>
                        <td class="text-center">{{number_format($item->existence,2)}}</td>
                        <td class="text-center">$ {{number_format($item->precio,2)}}</td>
                        <td class="text-center">$ {{number_format($item->precio_mayoreo,2)}}</td>
                        <td class="text-center">$ {{number_format($item->precio_despiece,2)}}</td>
                        @if(Auth::User()->hasAnyRole(['root', 'admin']))
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary">Acción</button>
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    @if(is_object($item->getPartToProduct) && is_object($item->getPartToProductDespiezado))
                                        @php $status = 'only_edit'; @endphp
                                    @endif

                                    <a class="dropdown-item" href="{{route('product.create', [$item->id, $status ?? ''])}}"><i class="fa fa-list"></i>&nbsp; Presentaciones</a>
                                    @if(!is_object($item->getPartToProductDespiezado)){{--Si ya tiene despiezado, no te da opcion de despiezar mas este proudcto--}}
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{route('product.create', [$item->id, 'despiece'])}}"><i class="fa fa-check"></i>&nbsp; Despiezado</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="9" class="table-warning text-center">Sin categorias</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <br>
            <div class="col-lg-12 col-md-12 col-sm-12"><span>{{ $products->links() }}</span></div>
        </div>
        @include('Admin.products._modal_product_presentation')
  </div>