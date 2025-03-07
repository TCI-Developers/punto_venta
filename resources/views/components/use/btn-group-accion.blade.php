<div class="btn-group">
    <button type="button" class="btn btn-primary">Acción</button>
    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <div class="dropdown-menu">
        @foreach($options as $index => $item)
        <a class="dropdown-item" href="{{$routes[$index]}}"><i class="{{$icons[$index]}}"></i>&nbsp; {{$item}}</a>
        @if(count($options)-1 > $index)<div class="dropdown-divider"></div>@endif
        @endforeach
    </div>
</div>