@php
// Agrupar permisos por módulo
$groupedPermissions = $permissions->groupBy('module');
@endphp

<div class="permissions-container">
    <input type="hidden" id="modalRoleId" value="{{ $role->id }}">
    @foreach($groupedPermissions as $module => $modulePermissions)
    <div class="permission-module">
        <div class="permission-module-header">
            {{ ucfirst($module) }}
        </div>
        <div class="permission-items">
            @foreach($modulePermissions as $permission)
            <div class="permission-item form-check">
                <input type="checkbox" 
                       name="permissions[]" 
                       value="{{ $permission->id }}" 
                       id="perm_{{ $permission->id }}"
                       class="form-check-input"
                       {{ $rolePermissions->contains($permission->id) ? 'checked' : '' }}>
                        <label for="perm_{{ $permission->id }}" class="form-check-label">
                    {{ $permission->description }} 
                    <small class="text-muted">({{ $permission->action }})</small>
                </label>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

