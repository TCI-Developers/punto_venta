<link rel="stylesheet" href="{{asset('css/style_nav_slider.css')}}">

<!-- Header -->
  <header class="main-header d-flex align-items-center justify-content-between px-3 py-2">
    <div class="d-flex align-items-center">
        <button id="toggleSidebar" class="hamburger mr-2">&#9776;</button>
        <img src="{{ asset('img/logo.png') }}" alt="logo" width="35" class="mr-2">
        <span class="logo">POS TCI</span>
    </div>

    <!-- Authentication -->
    <div class="user-dropdown">
        <div class="dropdown-toggle">
            <i class="fa fa-user-circle"></i> {{ Auth::user()->name }}
        </div>
        @if(Auth::User()->hasAnyRole(['root', 'admin']))
        <div class="dropdown-menu">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fa fa-power-off"></i> Cerrar Sesión
                </button>
            </form>
        </div>
        @endif
    </div>
</header>

  <!-- Sidebar -->
  <aside id="sidebar" class="main-sidebar">
    <nav class="sidebar-menu">
      <ul>
        <li class="nav-item"> <!-- Ventas -->
            <a href="{{route('sale.index')}}">
              <i class="fa fa-cart-plus"></i>
                Ventas
            </a>
        </li>
        <li class="nav-item"> <!-- Productos -->
            <a href="{{route('product.index')}}">
              <i class="fa fa-folder"></i>
              Productos
            </a>
        </li>
        <li class="nav-item"> <!-- Clientes -->
            <a href="{{route('customer.index')}}">
                <i class="fa fa-users"></i>
                Clientes
            </a>
        </li>
        <li class="nav-item"> <!-- Proveedores -->
            <a href="{{route('proveedor.index')}}">
                <i class="fa fa-user"></i>
                Proveedores
            </a>
        </li>
        {{--<li class="nav-item"> <!-- Presentación Productos -->
            <a href="{{route('product.indexPartProduct')}}">
              <i class="fa fa-archive"></i>
                Presentación productos
            </a>
        </li>--}}
        <li class="nav-item"> <!--Compras -->
            <a href="{{route('compra.index')}}">
              <i class="fa fa-shopping-cart"></i>
                Compras
            </a>
        </li>
        <li class="nav-item"> <!--Cuentas por pagar -->
            <a href="{{route('cxp.index')}}">
                <i class="fa fa-address-book"></i>
                Cuentas por pagar
            </a>
        </li>
        <li class="nav-item"> <!--Devoluciones -->
            <a href="{{route('devoluciones.index')}}">
                <i class="fa fa-refresh"></i>
                Devoluciones
            </a>
        </li>
        @if(!Auth::User()->hasAnyRole(['root','admin']))
        <li class="nav-item"> <!-- Corte Caja -->
            <a href="{{route('box.turnOff')}}">
                <i class="fa fa-window-close" aria-hidden="true"></i>
                Cierre de Turno
            </a>
        </li>
        @endif  
        <!--Habilitar Promociones despues de las validaciones en ventas-->
        @if(Auth::User()->hasAnyRole(['root','admin']))
        <li class="nav-item"> <!-- Cortes de Caja -->
            <a href="{{route('box.index')}}">
              <i class="fa fa-th"></i>
                Cierres de Turno
            </a>
        </li>
        @endif               

        @if(Auth::User()->hasRole('root') || Auth::User()->name == 'TCI_DEV')
            <li class="nav-item"> <!-- Roles -->
                <a href="{{route('roles.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Roles
                </a>
            </li>
            <li class="nav-item"> <!-- Empresa -->
                <a href="{{route('root.index')}}">
                    <i class="fa fa-file"></i>
                    Importación
                </a>
            </li>
        @endif

        @if(Auth::User()->hasAnyRole(['root','admin']))
        <li class="nav-header text-center">--- Opciones de Admin ---</li>

            <li class="nav-item"> <!-- Turnos -->
                <a href="{{route('turnos.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Turnos
                </a>
            </li>
            <li class="nav-item"> <!-- Usuarios -->
                <a href="{{route('users.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Usuarios
                </a>
            </li>
            <li class="nav-item"> <!-- Sucursales -->
                <a href="{{route('branchs.index')}}">
                    <i class="fa fa-home"></i>
                    Sucursales
                </a>
            </li>
            
            <li class="nav-item"> <!-- Empresa -->
                <a href="{{route('admin.empresa')}}">
                    <i class="fa fa-id-card"></i>
                    Empresa
                </a>
            </li>
        @endif
      </ul>
    </nav>
  </aside>

  <script src="{{asset('js/nav_slider.js')}}"></script>