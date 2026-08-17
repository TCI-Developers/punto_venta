<link rel="stylesheet" href="{{asset('css/style_nav_slider.css')}}">
<script src="{{asset('js/app_update.js')}}"></script>

<!-- Aviso de actualizacion disponible/descargando/lista (poll a /update-status, ver app_update.js) -->
<div id="updateBanner" style="display:none; align-items:center; justify-content:center; gap:1rem; background:#0d6efd; color:#fff; padding:6px 16px; font-size:0.9rem; text-align:center;">
    <span id="updateBannerText"></span>
    <button id="updateBannerBtn" type="button" class="btn btn-light btn-sm" style="display:none;" onclick="installUpdate()">Reiniciar y actualizar</button>
</div>
<form id="updateInstallForm" action="{{ route('update.install') }}" method="POST" style="display:none;">
    @csrf
</form>

@if(auth()->user()->hasPermissionThroughModule('empresa','punto_venta','auth'))
<!-- Aviso de cambios pendientes en el catalogo de Matriz (solo para quien puede sincronizarlo) -->
<script src="{{asset('js/catalog_sync.js')}}"></script>
<div id="catalogBanner" style="display:none; align-items:center; justify-content:center; gap:1rem; background:#6c757d; color:#fff; padding:6px 16px; font-size:0.9rem; text-align:center;">
    <span id="catalogBannerText"></span>
    <button id="catalogBannerBtn" type="button" class="btn btn-light btn-sm" onclick="syncCatalogNow()">Sincronizar ahora</button>
</div>
<form id="catalogSyncCsrfForm" style="display:none;">
    @csrf
</form>
@endif

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

        {{--@if(!auth()->user()->hasPermissionThroughModule('cierre_caja') || Auth::User()->hasRole('root') || Auth::User()->name == 'TCI_DEV')--}}
        <div class="dropdown-menu">
            <form method="POST" action="{{ route('update.check') }}">
                @csrf
                <button type="submit">
                    <i class="fa fa-refresh"></i> Buscar actualizaciones
                </button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fa fa-power-off"></i> Cerrar Sesión
                </button>
            </form>
        </div>
       {{-- @endif --}}
    </div>
</header>

  <!-- Sidebar -->
  <aside id="sidebar" class="main-sidebar">
    <nav class="sidebar-menu">
      <ul>
         @if(auth()->user()->hasPermissionThroughModule('ventas'))
        <li class="nav-item"> <!-- Ventas -->
            <a href="{{route('sale.index')}}">
              <i class="fa fa-cart-plus"></i>
                Ventas
            </a>
        </li>
        <li class="nav-item"> <!-- Facturas -->
            <a href="{{route('facturas.index')}}">
              <i class="fa fa-file-text-o"></i>
                Facturas
            </a>
        </li>
        @endif
        @if(auth()->user()->hasPermissionThroughModule('inventarios'))
        <li class="nav-item"> <!-- Productos -->
            <a href="{{route('product.index')}}">
              <i class="fa fa-folder"></i>
              Productos
            </a>
        </li>
        @endif
        @if(auth()->user()->hasPermissionThroughModule('clientes'))
        <li class="nav-item"> <!-- Clientes -->
            <a href="{{route('customer.index')}}">
                <i class="fa fa-users"></i>
                Clientes
            </a>
        </li>
        @endif
        @if(auth()->user()->hasPermissionThroughModule('proveedores'))
        <li class="nav-item"> <!-- Proveedores -->
            <a href="{{route('proveedor.index')}}">
                <i class="fa fa-user"></i>
                Proveedores
            </a>
        </li>
        @endif
         @if(auth()->user()->hasPermissionThroughModule('compras'))
        <li class="nav-item"> <!--Compras -->
            <a href="{{route('compra.index')}}">
              <i class="fa fa-shopping-cart"></i>
                Compras
            </a>
        </li>
        @endif
         @if(auth()->user()->hasPermissionThroughModule('cuentas_por_pagar'))
        <li class="nav-item"> <!--Cuentas por pagar -->
            <a href="{{route('cxp.index')}}">
                <i class="fa fa-address-book"></i>
                Cuentas por pagar
            </a>
        </li>
        @endif
         @if(auth()->user()->hasPermissionThroughModule('gastos'))
        <li class="nav-item"> <!--Gastos de caja -->
            <a href="{{route('gasto.index')}}">
                <i class="fa fa-money"></i>
                Gastos
            </a>
        </li>
        @endif
         @if(auth()->user()->hasPermissionThroughModule('devoluciones'))
        <li class="nav-item"> <!--Devoluciones -->
            <a href="{{route('devoluciones.index')}}">
                <i class="fa fa-refresh"></i>
                Devoluciones
            </a>
        </li>
        @endif

        @if(auth()->user()->hasPermissionThroughModule('cierre_caja'))
        <li class="nav-item"> <!-- Corte Caja -->
            <a href="{{route('box.turnOff')}}">
                <i class="fa fa-window-close" aria-hidden="true"></i>
                Cierre de Turno
            </a>
        </li>
        @endif  

        <!--Habilitar Promociones despues de las validaciones en ventas-->
        @if(auth()->user()->hasPermissionThroughModule('listado_cierre_caja'))
        <li class="nav-item"> <!-- Cortes de Caja -->
            <a href="{{route('box.index')}}">
              <i class="fa fa-th"></i>
                Cierres de Turno
            </a>
        </li>
        @endif   

        @if(auth()->user()->hasPermissionThroughModule('sucursales'))
            <li class="nav-item"> <!-- Sucursales -->
                <a href="{{route('branchs.index')}}">
                    <i class="fa fa-home"></i>
                    Sucursales
                </a>
            </li>
        @endif               
        @if(auth()->user()->hasPermissionThroughModule('turnos'))
             <li class="nav-item"> <!-- Turnos -->
                <a href="{{route('turnos.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Turnos
                </a>
            </li> 
        @endif               
        @if(auth()->user()->hasPermissionThroughModule('roles'))
             <li class="nav-item"> <!-- Roles -->
                <a href="{{route('roles.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Roles
                </a>
            </li>
        @endif               
        @if(auth()->user()->hasPermissionThroughModule('usuarios'))
            <li class="nav-item"> <!-- Usuarios -->
                <a href="{{route('users.index', 1)}}">
                    <i class="fa fa-circle"></i>
                    Usuarios
                </a>
            </li> 
        @endif               

        @if(auth()->user()->hasPermissionThroughModule('empresa'))
            <li class="nav-item"> <!-- Empresa -->
                <a href="{{route('admin.empresa')}}">
                    <i class="fa fa-id-card"></i>
                    Empresa
                </a>
            </li>
        @endif

        <li class="nav-item">
            <a href="{{ route('manual.index') }}">
                <i class="fa fa-book"></i> Manual
            </a>
        </li>

        @if(Auth::User()->hasRole('root') || Auth::User()->name == 'TCI_DEV')
            <li class="nav-header text-center">--- Opciones de Root ---</li>
            <li class="nav-item"> <!-- Empresa --> 
                <a href="{{route('permission.index')}}">
                    <i class="fa fa-hand-paper-o" aria-hidden="true"></i>
                    Permisos
                </a>
            </li>
            <li class="nav-item"> <!-- importacion -->
                <a href="{{route('root.index')}}">
                    <i class="fa fa-file"></i>
                    Importación
                </a>
            </li>
            <li class="nav-item"> <!-- Logs -->
               <a href="{{route('logs')}}">
                   <i class="fa fa-exclamation-triangle"></i>
                   Logs
               </a>
           </li>
        @endif
      </ul>
    </nav>
  </aside>

  <script src="{{asset('js/nav_slider.js')}}"></script>