<div class="card card-primary">
        <div class="form-group card-header with-border text-center">
            <h2>Clientes {{$status ? '':'Deshabilitados'}}</h2>
        </div>
        <div class="card-body">
            <div class="row col-lg-12 col-md-12 col-sm-12">
                <div class="col-lg-1 col-md-1 col-sm-4">
                <div class="row">
                    <label for="paginate_cant" class="float-left col-lg-12 col-md-12 col-sm-12">Mostrar <br>
                        <input type="number" class="form-control" id="paginate_cant" value="{{$paginate_cant}}" wire:model.live="paginate_cant">
                    </label>
                </div>
                </div>

                <div class="col-lg-1 col-md-1 col-sm-4"> <br>
                    <button type="button" class="btn btn-success text_color" onClick="showModal()"><img src="{{asset('icons/plus.svg')}}" width="23" alt="icon plus"
                    data-toggle="tooltip" data-placement="top" title="Nuevo Cliente"></button>
                </div>

                <div class="col-lg-8 col-md-8 col-sm-4">
                <div class="row float-right">
                    <label class="col-lg-12 col-md-12 col-sm-12"><br>
                        @if($status == 1)
                        <button type="button" class="btn btn-light" wire:click="onOff"
                        data-toggle="tooltip" data-placement="top" title="Ver deshabilitados"><img class="icon_img" src="{{asset('icons/archive.svg')}}" alt="icon archive" >Deshabilitados</button>
                        @else
                        <button type="button" class="btn btn-info" wire:click="onOff"
                        data-toggle="tooltip" data-placement="top" title="Ver habilitados"><img class="icon_img" src="{{asset('icons/archive.svg')}}" alt="icon archive"
                        >Habilitados</button>
                        @endif
                    </label>
                </div>
                </div>

                <div class="col-lg-2 col-md-2 col-sm-12 float-right">
                <div class="row">
                    <label for="search" class="col-lg-12 col-md-12 col-sm-12">Buscar <br>
                        <input type="text" class="form-control" id="search" value="{{$search}}" placeholder="Buscar" wire:model.live="search" step="10">
                    </label>
                </div>
                </div>

            </div>
            <div class="table-responsive">
            <table class="table table-striped table-bordered datatable">
                <thead>
                    <tr class="text-center">
                        <th>Nombre</th>
                        <th>Razón Social</th>
                        <th>RFC</th>
                        <th>CP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $item)
                    <tr>
                        <td>{{$item->name}}</td>
                        <td class="text-center">{{$item->razon_social}}</td>
                        <td class="text-center">{{$item->rfc}}</td>
                        <td class="text-center">{{$item->postal_code}}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm" wire:click="btnEdit({{$item->id}})"><img src="{{asset('icons/edit.svg')}}" alt="icon edit"
                            data-toggle="tooltip" data-placement="top" title="Editar"></button>
                            @if($status)
                            <a href="{{route('customer.destroy', [$item->id, 0])}}" class="btn btn-light btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Deshabilitar"><img src="{{asset('icons/trash.svg')}}" alt="icon trash"></a>
                            @else
                            <a href="{{route('customer.destroy', [$item->id, 1])}}" class="btn btn-primary btn-sm"
                            data-toggle="tooltip" data-placement="top" title="Habilitar"><img src="{{asset('icons/update.svg')}}" alt="icon update"></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="table-warning text-center">Sin Clientes</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <br>
            <div class="col-lg-12 col-md-12 col-sm-12"><span>{{ $customers->links() }}</span></div>
        </div>
        @include('Admin.customers._modal')
  </div>