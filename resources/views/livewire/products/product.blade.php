<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Productos
                 @if(Auth::User()->hasPermissionThroughModule('inventarios', 'punto_venta', 'create'))
                <a href="{{route('product.showUploadExcel')}}" class="btn btn-success btn-sm float-right"><i class="fa fa-upload"></i> Carga Masiva Stock/Codigos de barra</a>
                @endif
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
                        <th>Stock</th>
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

                        <td class="text-center">
                            @if(is_object($item->getPartToProduct))
                            <span class="badge badge-primary">{{number_format($item->getPartToProduct->stock ?? 0,2)}}</span>
                            @else
                            <span class="badge badge-success">{{number_format($item->existence ?? 0,2)}}</span>
                            @endif
                        </td>
                        <td class="text-center">$ {{number_format($item->precio,2)}}</td>
                        <td class="text-center">$ {{number_format($item->precio_mayoreo,2)}}</td>
                        <td class="text-center">$ {{number_format($item->precio_despiece,2)}}</td>
                        @if(Auth::User()->hasAnyRole(['root', 'admin']))
                        <td class="text-center">
                            <div class="dropdown" style="position: relative; display: inline-block;">
                                <button class="btn btn-primary btn-sm" type="button" onclick="toggleDropdown(this)">
                                    <i class="fa fa-cog"></i> 
                                </button>

                                <div class="dropdown-menu" style="position:relative !important;">
                                    <a class="dropdown-item" href="{{ route('product.create', 
                                        [$item->id, is_object($item->getPartToProduct) && is_object($item->getPartToProductDespiezado) ? 'only_edit':'']) }}">
                                        <i class="fa fa-list"></i>&nbsp; Presentaciones 
                                    </a>
                                    @if(!is_object($item->getPartToProductDespiezado))
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('product.create', [$item->id, 'despiece']) }}">
                                            <i class="fa fa-check"></i>&nbsp; Despiezado
                                        </a>
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
  </div>