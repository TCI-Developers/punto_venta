<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                
                <li class="nav-item"> <!-- Productos -->
                    <a class="nav-link" href="{{route('branchs.index')}}">
                        <i class="fa fa-home"></i>
                        <p>Sucursales</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Productos -->
                    <a class="nav-link" href="{{route('product.index')}}">
                        <img src="{{asset('icons/folder.svg')}}" alt="Icono de archivo" width="23">
                        <p>Productos</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Clientes -->
                    <a class="nav-link" href="{{route('customer.index')}}">
                        <img src="{{asset('icons/users.svg')}}" alt="Icono de clientes" width="23">
                        <p>Clientes</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Ventas -->
                    <a class="nav-link" href="{{route('sale.index')}}">
                        <img src="{{asset('icons/cart.svg')}}" alt="Icono de ventas" width="23">
                        <p>Ventas</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Presentación Productos -->
                    <a class="nav-link" href="{{route('product.indexPartProduct')}}">
                        <img src="{{asset('icons/archive.svg')}}" alt="Icono de presentación" width="23">
                        <p>Presentación productos</p>
                    </a>
                </li>
                
                <li class="nav-item"> <!-- Corte Caja -->
                    <a class="nav-link" href="{{route('box.turnOff')}}">
                        <img src="{{asset('icons/close.svg')}}" alt="Icono de close" width="23">
                        <p>Cierre de Turno</p>
                    </a>
                </li>

                <!--Habilitar Promociones despues de las validaciones en ventas-->
                @if(Auth::User()->hasAnyRole(['root','admin']))
                {{--<li class="nav-item"> 
                    <a class="nav-link" href="{{route('promos.index', 1)}}">
                        <i class="fa fa-money-bill"></i>
                        <p>Promociones</p>
                    </a>
                </li> --}}
                <li class="nav-item"> <!--Devoluciones -->
                    <a class="nav-link" href="{{route('devoluciones.index')}}">
                        <i class="fas fa-undo"></i>
                        <p>Devoluciones</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Cortes de Caja -->
                    <a class="nav-link" href="{{route('box.index')}}">
                        <img src="{{asset('icons/list.svg')}}" alt="Icono de close" width="23">
                        <p>Cierres de Turno</p>
                    </a>
                </li>
                @endif

                <li class="nav-header text-center">Opciones de Admin</li>

                <li class="nav-item"> <!-- Turnos -->
                    <a class="nav-link" href="{{route('turnos.index', 1)}}">
                        <img src="{{asset('icons/roles.svg')}}" alt="Icono de roles" width="23">
                        <p>Turnos</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Roles -->
                    <a class="nav-link" href="{{route('roles.index', 1)}}">
                        <img src="{{asset('icons/turnos.svg')}}" alt="Icono de turnos" width="23">
                        <p>Roles</p>
                    </a>
                </li>
                <li class="nav-item"> <!-- Usuarios -->
                    <a class="nav-link" href="{{route('users.index', 1)}}">
                        <img src="{{asset('icons/users.svg')}}" alt="Icono de usuarios" width="23">
                        <p>Usuarios</p>
                    </a>
                </li>
                @if(Auth::User()->hasAnyRole(['root','admin']))
                <li class="nav-item"> <!-- Sucursales -->
                    <a class="nav-link" href="{{route('branch.index')}}">
                        <img src="{{asset('icons/home.svg')}}" alt="Icono de close" width="23">
                        <p>Opciones Sucursal</p>
                    </a>
                </li>
                @endif

               
            </ul> 
        </nav>
    </div>
</aside>
