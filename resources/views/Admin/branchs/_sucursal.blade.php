<div class="col-lg-4 col-md-4 col-sm-6 item flex-column"> 
    <div class="col-lg-12 col-md-12 col-sm-12">
        @if(auth()->user()->hasPermissionThroughModule('sucursales','punto_venta','show') || auth()->user()->hasPermissionThroughModule('sucursales','punto_venta','update'))
        <a href="{{route('branchs.show', $item->id)}}" class="btn fs-6"><i class="fa fa-edit text-info"></i></a>
        @endif
         @if(auth()->user()->hasPermissionThroughModule('sucursales','punto_venta','destroy'))
        <a class="btn fs-6 float-right" href="{{route('branchs.destroy', [$item->id, $status == 0 ? 1:0] )}}"><i class="fa {{$status == 0 ? 'fa-upload text-success':'fa-trash text-danger'}} "></i></a>
        @endif
    </div>
    
    <a href="{{route('branchs.setSucursalUser', $item->id)}}" class="btn w-100 item flex-grow-1 flex-column">
        <div class="app-content format flex-grow-1 flex-column justify-content-center">
            <h3 class="text-center">{{$item->name}}</h3>
            <div class="lorem">{{$item->address}}</div>
        </div>
    </a>
</div>