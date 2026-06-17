@php
$groupedPermissions = $permissions->groupBy('module');

$moduleLabels = [
    'ventas'               => ['label' => 'Ventas',               'icon' => 'fa-shopping-cart'],
    'inventarios'          => ['label' => 'Inventario',           'icon' => 'fa-cubes'],
    'clientes'             => ['label' => 'Clientes',             'icon' => 'fa-users'],
    'proveedores'          => ['label' => 'Proveedores',          'icon' => 'fa-truck'],
    'compras'              => ['label' => 'Compras',              'icon' => 'fa-file-text'],
    'cuentas_por_pagar'    => ['label' => 'Cuentas por Pagar',    'icon' => 'fa-money'],
    'devoluciones'         => ['label' => 'Devoluciones',         'icon' => 'fa-undo'],
    'usuarios'             => ['label' => 'Usuarios',             'icon' => 'fa-user'],
    'sucursales'           => ['label' => 'Sucursales',           'icon' => 'fa-building'],
    'roles'                => ['label' => 'Roles y Permisos',     'icon' => 'fa-shield'],
    'empresa'              => ['label' => 'Empresa',              'icon' => 'fa-briefcase'],
    'cierre_caja'          => ['label' => 'Cierre de Caja',       'icon' => 'fa-lock'],
    'listado_cierre_caja'  => ['label' => 'Listado Cierres',      'icon' => 'fa-list'],
    'turnos'               => ['label' => 'Turnos',               'icon' => 'fa-clock-o'],
];

$actionOrder = [
    'show'    => ['label' => 'Ver',       'icon' => 'fa-eye',         'color' => 'text-info',    'tooltip' => 'Permite consultar y visualizar registros existentes.'],
    'create'  => ['label' => 'Crear',     'icon' => 'fa-plus-circle', 'color' => 'text-success', 'tooltip' => 'Permite crear nuevos registros en este módulo.'],
    'update'  => ['label' => 'Editar',    'icon' => 'fa-pencil',      'color' => 'text-warning', 'tooltip' => 'Permite modificar registros existentes.'],
    'destroy' => ['label' => 'Eliminar',  'icon' => 'fa-trash',       'color' => 'text-danger',  'tooltip' => 'Permite deshabilitar o eliminar registros.'],
    'auth'    => ['label' => 'Autorizar', 'icon' => 'fa-key',         'color' => 'text-primary', 'tooltip' => 'Acción especial según el módulo (ver descripción específica).'],
];

// Descripciones específicas por módulo y acción
$specificTooltips = [
    'ventas' => [
        'show'    => 'Permite ver el listado de ventas realizadas.',
        'create'  => 'Permite crear nuevas ventas en el punto de venta.',
        'update'  => 'Permite modificar datos de una venta (método de pago, cliente).',
        'destroy' => 'Permite cancelar ventas.',
        'auth'    => 'Permite vender productos con stock en cero y amplía la búsqueda de productos en el POS.',
    ],
    'inventarios' => [
        'show'    => 'Permite ver el listado de productos e inventario.',
        'create'  => 'Permite agregar presentaciones y promociones a productos.',
        'update'  => 'Permite editar precios, presentaciones y ajustar existencias.',
        'destroy' => 'Permite deshabilitar productos o presentaciones.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'clientes' => [
        'show'    => 'Permite ver el listado de clientes.',
        'create'  => 'Permite registrar nuevos clientes.',
        'update'  => 'Permite editar datos de clientes existentes.',
        'destroy' => 'Permite habilitar o deshabilitar clientes.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'proveedores' => [
        'show'    => 'Permite ver el listado de proveedores.',
        'create'  => 'Permite registrar nuevos proveedores.',
        'update'  => 'Permite editar datos de proveedores.',
        'destroy' => 'Permite deshabilitar proveedores.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'compras' => [
        'show'    => 'Permite ver el listado de órdenes de compra.',
        'create'  => 'Permite crear nuevas órdenes de compra.',
        'update'  => 'Permite editar una OC y registrar mercancía recibida.',
        'destroy' => 'Permite eliminar líneas de una orden de compra.',
        'auth'    => 'Permite cambiar el estatus de una OC a "Autorizada" (aprobación de compra).',
    ],
    'cuentas_por_pagar' => [
        'show'    => 'Permite ver las cuentas por pagar generadas.',
        'create'  => 'Permite registrar pagos a proveedores.',
        'update'  => 'Permite modificar cuentas por pagar.',
        'destroy' => 'Permite cancelar cuentas por pagar.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'devoluciones' => [
        'show'    => 'Permite ver el listado de devoluciones.',
        'create'  => 'Permite registrar devoluciones de clientes y a proveedores.',
        'update'  => 'Permite modificar devoluciones existentes.',
        'destroy' => 'Permite eliminar líneas de una devolución.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'usuarios' => [
        'show'    => 'Permite ver el listado de usuarios del sistema.',
        'create'  => 'Permite crear nuevos usuarios.',
        'update'  => 'Permite editar datos de usuarios existentes.',
        'destroy' => 'Permite habilitar o deshabilitar usuarios.',
        'auth'    => 'Permite asignar roles y turnos a los usuarios.',
    ],
    'sucursales' => [
        'show'    => 'Permite ver el listado de sucursales.',
        'create'  => 'Permite registrar nuevas sucursales.',
        'update'  => 'Permite editar datos de sucursales.',
        'destroy' => 'Permite deshabilitar sucursales.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'roles' => [
        'show'    => 'Permite ver el listado de roles.',
        'create'  => 'Permite crear nuevos roles.',
        'update'  => 'Permite editar nombre y descripción de un rol.',
        'destroy' => 'Permite deshabilitar roles.',
        'auth'    => 'Permite abrir el modal "Administrar Permisos" y guardar los permisos asignados a un rol.',
    ],
    'empresa' => [
        'show'    => 'Permite ver los datos de la empresa.',
        'create'  => 'No implementado aún en este módulo.',
        'update'  => 'Permite editar datos generales de la empresa.',
        'destroy' => 'No implementado aún en este módulo.',
        'auth'    => 'Permite guardar los datos de la empresa y acceder a la sección de importación desde DB externa.',
    ],
    'cierre_caja' => [
        'show'    => 'Permite ver el formulario de cierre de caja.',
        'create'  => 'Permite registrar el monto inicial al abrir turno.',
        'update'  => 'Permite realizar el cierre de turno con conteo de denominaciones.',
        'destroy' => 'No implementado aún en este módulo.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'listado_cierre_caja' => [
        'show'    => 'Permite ver el historial de cierres de caja.',
        'create'  => 'No implementado aún en este módulo.',
        'update'  => 'No implementado aún en este módulo.',
        'destroy' => 'No implementado aún en este módulo.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
    'turnos' => [
        'show'    => 'Permite ver el listado de turnos configurados.',
        'create'  => 'Permite crear nuevos turnos.',
        'update'  => 'Permite editar horarios de turnos existentes.',
        'destroy' => 'Permite habilitar o deshabilitar turnos.',
        'auth'    => 'No implementado aún en este módulo.',
    ],
];
@endphp

<input type="hidden" id="modalRoleId" value="{{ $role->id }}">
<p class="text-muted small mb-3">Activa o desactiva los permisos para el rol <strong>{{ $role->name }}</strong>. Usa "Todo" para seleccionar/deseleccionar un módulo completo.</p>

@foreach($groupedPermissions as $module => $modulePermissions)
@php
    $modInfo  = $moduleLabels[$module] ?? ['label' => ucfirst(str_replace('_', ' ', $module)), 'icon' => 'fa-circle'];
    $allChecked = $modulePermissions->every(fn($p) => $rolePermissions->contains($p->id));
@endphp
<div class="perm-card">
    <div class="perm-card-header">
        <span class="mod-title">
            <i class="fa {{ $modInfo['icon'] }} text-secondary"></i>
            {{ $modInfo['label'] }}
        </span>
        <label class="select-all-label mb-0">
            <input type="checkbox"
                   class="form-check-input select-all-module"
                   data-module="{{ $module }}"
                   {{ $allChecked ? 'checked' : '' }}>
            Todo
        </label>
    </div>
    <div class="perm-card-body">
        @foreach($actionOrder as $action => $ac)
        @php
            $permission = $modulePermissions->firstWhere('action', $action);
            $tooltip = $specificTooltips[$module][$action] ?? $ac['tooltip'];
            $notImplemented = str_starts_with($tooltip, 'No implementado');
        @endphp
        @if($permission)
        <div class="perm-item {{ $notImplemented ? 'perm-item-disabled' : '' }}">
            <input type="checkbox"
                   class="form-check-input perm-checkbox"
                   name="permissions[]"
                   value="{{ $permission->id }}"
                   id="perm_{{ $permission->id }}"
                   data-module="{{ $module }}"
                   {{ $rolePermissions->contains($permission->id) ? 'checked' : '' }}
                   {{ $notImplemented ? 'disabled' : '' }}>
            <label for="perm_{{ $permission->id }}">
                <i class="fa {{ $ac['icon'] }} {{ $notImplemented ? 'text-muted' : $ac['color'] }}"></i>
                {{ $ac['label'] }}
            </label>
            <span class="perm-help" data-tooltip="{{ $tooltip }}">?</span>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endforeach

<script>
    document.querySelectorAll('.select-all-module').forEach(function(master) {
        master.addEventListener('change', function() {
            const module = this.dataset.module;
            document.querySelectorAll('.perm-checkbox[data-module="' + module + '"]:not(:disabled)').forEach(function(cb) {
                cb.checked = master.checked;
            });
        });
    });

    document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const module = this.dataset.module;
            const all = document.querySelectorAll('.perm-checkbox[data-module="' + module + '"]:not(:disabled)');
            const allChecked = Array.from(all).every(function(c) { return c.checked; });
            const master = document.querySelector('.select-all-module[data-module="' + module + '"]');
            if (master) master.checked = allChecked;
        });
    });
</script>
