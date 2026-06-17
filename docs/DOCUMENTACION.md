# Documentación Técnica — POSTCI POS

> Sistema punto de venta desarrollado en Laravel 10 + Livewire 3 + NativePhP/Electron.  
> Base de datos local: SQLite. Base de datos externa (sincronización): MySQL en 193.203.166.213.

---

## Índice

1. [Login y Autenticación](#1-login-y-autenticación)
2. [Módulo de Ventas](#2-módulo-de-ventas)
3. [Módulo de Productos](#3-módulo-de-productos)
4. [Módulo de Clientes](#4-módulo-de-clientes)
5. [Módulo de Compras](#5-módulo-de-compras)
6. [Módulo de Cuentas por Pagar](#6-módulo-de-cuentas-por-pagar)
7. [Módulo de Devoluciones](#7-módulo-de-devoluciones)
8. [Módulo de Cierre de Turno](#8-módulo-cierre-de-turno-box--caja)
9. [Módulo de Sucursales](#9-módulo-de-sucursales)
10. [Módulo de Turnos](#10-módulo-de-turnos)
11. [Módulo de Roles y Permisos](#11-módulo-de-roles-y-permisos)
12. [Módulo de Usuarios](#12-módulo-de-usuarios)
13. [Módulo de Empresa](#13-módulo-de-empresa)
14. [Módulo de Permisos](#14-módulo-de-permisos)
15. [Módulo de Importación](#15-módulo-de-importación)
16. [Módulo de Logs](#16-módulo-de-logs)

---

## 1. Login y Autenticación

**Archivo principal:** `app/Http/Controllers/Admin/UserController.php`  
**Vista:** `resources/views/auth/login.blade.php`  
**Ruta:** `POST /login` → `UserController@loginUser`

### Flujo de login

```
Usuario ingresa teléfono + contraseña
        │
        ▼
¿Es el usuario de soporte (NAME_ROOT en .env)?
        │ Sí → consulta DB externa → autentica → redirige a sucursales
        │ No ↓
        ▼
Busca usuario local (phone, status=1)
        │
        ▼
¿Credenciales incorrectas?
        │ Sí → "Credenciales Incorrectas" (siempre, sin importar vigencia)
        │ No ↓
        ▼
¿Licencia vencida? (solo si NO es root)
        │ Sí → "Licencia vencida, contacta al proveedor"
        │ No ↓
        ▼
Auth::login()
        │
        ▼
¿Rol root o admin?
        │ Sí → redirige a sale.index
        │ No → redirige a admin.startAmountBox (monto inicial de caja)
```

### Vigencia de licencia

- Se almacena encriptada con `Crypt::encrypt()` en `empresa_details.vigencia`
- Se compara la fecha local con la fecha remota al hacer login
- Si la fecha remota es mayor, actualiza la local (nunca al revés)
- Solo los usuarios con rol `root` pueden ver y editar la vigencia (vista Empresa)
- El rol `root` **siempre bypasea** el chequeo de vigencia

**Método:** `UserController@vigencia()` — retorna `true` si la licencia está vencida.

### Roles y comportamiento post-login

| Rol | Redirige a |
|-----|-----------|
| `root` | Listado de ventas |
| `admin` | Listado de ventas |
| cualquier otro | Monto inicial de caja |

### Usuario de soporte (backdoor)

- Controlado por la variable de entorno `NAME_ROOT`
- Si no está definida, la funcionalidad está deshabilitada
- Requiere conexión a internet para autenticarse (consulta DB externa)
- Al autenticarse redirige directamente a sucursales, no pasa por vigencia

---

## 2. Módulo de Ventas

**Controlador:** `app/Http/Controllers/Admin/SaleController.php`  
**Livewire:** `app/Livewire/Sales/Sale.php`  
**Vistas:** `resources/views/Admin/sales/` y `resources/views/livewire/sales/`  
**JS:** `public/js/sales/create.js`

### Flujo general

```
Listado de ventas (sale.index)
        │
        ▼
Click "+" → SaleController@create
        │   Crea Sale en BD con valores por defecto:
        │   customer_id=1, PUE, efectivo, MXN, status=1
        │
        ▼
Vista de venta (sale.show) — POS
        │
        ├─ Agregar productos (scanner físico o modal manual F2)
        ├─ Habilitar Edición → cambiar cliente / método de pago / tipo de pago
        │
        ▼
Click "Cobrar"
        │
        ▼
Proceso de cobro (ver sección 2.3)
        │
        ▼
Ticket en modal → Click "Cerrar" → Regresa a sale.index
```

### 2.1 Estados de una venta (`sales.status`)

| Valor | Significado |
|-------|-------------|
| `0` | Cancelada / eliminada (soft delete) |
| `1` | Abierta (en proceso) |
| `2` | Cobrada / cerrada |

### 2.2 Agregar productos

#### Por scanner físico
El input `#presentation_id` captura el código de barras. Al escribir dispara `scaner_codigo()` en Livewire.

#### Por modal manual (F2)
Abre el modal de búsqueda. La búsqueda filtra presentaciones por descripción, código de producto o código de barras con `wire:model.live="search"`.

#### Permiso `ventas / auth`
Controla si el cajero puede vender productos sin existencia:

| Tiene permiso `auth` | Sin existencia (`existence <= 0`) |
|---------------------|----------------------------------|
| No | Bloqueado — SweetAlert amarillo con nombre del producto |
| Sí | Permitido — se agrega normalmente |

El modal de búsqueda también filtra:
- **Sin permiso:** solo muestra productos con `existence > 0`
- **Con permiso:** muestra todos los productos

#### Precios mayoreo / menudeo
Si la presentación tiene `cantidad_mayoreo > 0` y `price_mayoreo > 0`, y la cantidad acumulada en la venta alcanza `cantidad_mayoreo`, el sistema automáticamente recalcula la línea a precio mayoreo eliminando los registros anteriores de `sale_detail_cant`.

#### Descuentos por vigencia
Las presentaciones pueden tener descuentos con vigencia por:
- **Cantidad:** `vigencia_cantidad_fecha = 'cantidad'` — se descuenta un uso por unidad vendida
- **Fecha:** `vigencia_cantidad_fecha = 'fecha'` — vigente hasta la fecha en `vigencia`

### 2.3 Proceso de cobro

#### Efectivo

```
Click "Cobrar"
        │ Muestra campos: Monto recibido / Total / Cambio
        │
        ▼
Cajero escribe monto del cliente
        │ onChange → calcula cambio automáticamente
        │
        ▼
Click "Aceptar"
        │
        ▼
SweetAlert de confirmación:
  Total: $XXX  |  Recibido: $XXX  |  Cambio: $XXX
  [Cobrar] [Cancelar]
        │ Confirma ↓
        ▼
Livewire cobrar() — valida servidor:
  • ¿Tiene productos? → si no: error
  • ¿Monto cubre total? → si no: error
  • Guarda: amount_received, total_sale, change, status=2
        │
        ▼
Abre modal con ticket (iframe)
        │
        ▼
Click "Cerrar" → window.location → sale.index
```

**Nota sobre redondeo (efectivo):** El umbral de validación aplica `ajustarMonto()` que redondea al $0.50 más cercano, acorde al manejo de centavos en efectivo:
- Decimal ≤ $0.25 → redondea hacia abajo
- Decimal entre $0.26 y $0.75 → $0.50
- Decimal > $0.75 → redondea hacia arriba

#### Tarjeta

Mismo flujo, excepto:
- `amount_received` se auto-llena con el total exacto y queda bloqueado (no editable)
- **No se aplica** `ajustarMonto()` — el monto debe ser exactamente igual al total
- `change` = $0.00

### 2.4 Reglas de negocio importantes

- Una venta solo puede eliminarse si **no tiene productos** (`sale_detail` vacío)
- Una venta cobrada (`status=2`) no puede editarse (el botón "Habilitar Edición" no aparece)
- Al agregar un producto se reduce `product.existence` inmediatamente
- Al eliminar un producto de la venta se restaura `product.existence`
- Las ventas abandonadas (sin productos, `status=1`, creadas hace más de 2 horas) se auto-eliminan al visitar el listado

### 2.5 Sincronización con DB externa

Al cerrar una venta (`cobrar()`), si hay conexión a internet, se sincronizan automáticamente:
- Ventas del día (`getSalesDbExt`)
- Devoluciones de los últimos 8 días (`getDevolutionDBExt`)
- Compras de los últimos 8 días (`getComprasDbExt`)

El check de internet tiene un **timeout de 2 segundos** para no bloquear el proceso de cobro.

### 2.6 Modelos relacionados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `Sale` | `sales` | Cabecera de la venta |
| `SaleDetail` | `sale_details` | Línea por presentación |
| `SaleDetailCant` | `sale_detail_cants` | Cantidad y descuento por línea |
| `PartToProduct` | `part_to_products` | Presentación del producto (código de barras, precio, mayoreo) |
| `Product` | `products` | Producto con `existence` |

---

---

## 3. Módulo de Productos

**Controlador:** `app/Http/Controllers/Admin/ProductController.php`  
**Livewire:** `app/Livewire/Products/Product.php`  
**Vistas:** `resources/views/Admin/products/`  
**JS:** `public/js/products/asignar_presentacion_desc_promo.js`

### Flujo general

```
Listado de productos (product.index)
        │  Búsqueda Livewire en tiempo real (description, code_product, unit, existence)
        │  Solo muestra productos con activo = 1
        │
        ▼
Click "Asignar Presentación" → ProductController → vista asignar_presentacion_desc_promo
        │
        │  type = null        → solo campos de presentación (sin despieze)
        │  type = 'despiezado'→ campos de despieze (cantidad_despiezado)
        │  type = 'only_edit' → solo editar presentaciones existentes (sin crear nuevas)
        │
        ▼
Tabla de presentaciones asignadas al producto
        │
        ├─ Editar presentación (botón naranja)
        └─ Eliminar presentación (soft-delete: status = 0)
```

### 3.1 Modelos relacionados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `Product` | `products` | Producto base con `existence`, `activo`, `code_product` |
| `PartToProduct` | `parts_to_product` | Presentación del producto (precio, código de barras, mayoreo, descuento) |
| `PresentationProduct` | `presentation_products` | Tipo de presentación (pieza, caja, docena, etc.) |
| `UnidadSat` | `unidad_sats` | Unidades del SAT (clave + nombre) |
| `Promotion` | `promotions` | Promociones disponibles para asignar |

### 3.2 Presentaciones (`PartToProduct`)

Cada producto puede tener múltiples presentaciones. Campos principales:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `product_id` | FK | Producto al que pertenece |
| `presentation_product_id` | FK | Tipo de presentación (pieza, caja…) |
| `code_bar` | string | Código de barras único para escanear |
| `price` | decimal | Precio de menudeo |
| `price_mayoreo` | decimal | Precio de mayoreo |
| `cantidad_mayoreo` | int | Cantidad mínima para precio mayoreo |
| `cantidad_despiezado` | int | Piezas por unidad (para despieze de stock) |
| `monto_porcentaje` | decimal | Valor del descuento (monto o %) |
| `tipo_descuento` | enum | `'monto'` o `'porcentaje'` |
| `vigencia_cantidad_fecha` | enum | `'fecha'` o `'cantidad'` |
| `vigencia` | string | Fecha límite o cantidad restante del descuento |
| `status` | int | `1` = activa, `0` = eliminada (soft delete) |

### 3.3 Sistema de descuentos y vigencia

Al guardar una presentación con descuento (`saveDescuentos()` en Livewire):

```
¿monto_porcentaje > 0?
        │ No → retorna false (sin descuento)
        │ Sí ↓
        ▼
¿vigencia_cantidad_fecha == 'fecha'?
        │ Sí → ¿modal_vigencia_fecha > hoy? → válido
        │      ¿modal_vigencia_fecha <= hoy? → error de validación
        │ No → ¿modal_vigencia_cantidad > 0? → válido
        │      ¿modal_vigencia_cantidad == 0? → error de validación
        ▼
Guarda: tipo_descuento, monto_porcentaje, vigencia_cantidad_fecha, vigencia
```

**Uso del descuento en ventas:** al agregar un producto con descuento vigente, `SaleDetailCant` registra el descuento y se aplica al calcular el subtotal de la línea.

### 3.4 Relaciones en `PartToProduct`

```php
getProduct()       → hasOne(Product,             'id', 'product_id')
getPresentation()  → hasOne(PresentationProduct, 'id', 'presentation_product_id')
getUnidadSat()     → hasOne(UnidadSat,           'id', 'unidad_sat_id')
getPromotion()     → hasOne(Promotion,           'id', 'promotion_id')
```

### 3.5 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | Path del script JS tenía coma en lugar de punto | `asignar_presentacion_desc_promo.blade.php` | `asignar_presentacion_desc_promo,js` → `.js` |
| 2 | Búsqueda mostraba productos inactivos (`activo=0`) | `app/Livewire/Products/Product.php` | Agregado `where('activo', 1)` en ambas ramas del render |
| 3 | `date($this->modal_vigencia_fecha)` interpretaba la fecha como formato PHP | `app/Livewire/Products/Product.php` | Cambiado a comparación directa `$this->modal_vigencia_fecha > date('Y-m-d')` |
| 4 | Condición de validación de campos repetía `presentation_type_id` tres veces | `app/Livewire/Products/Product.php` | Corregido a `presentation_type_id \|\| price \|\| code_bar` |
| 5 | `$item->getPresentation` usaba relación no definida en el modelo | `app/Models/PartToProduct.php` | Agregado `getPresentation()` con import de `PresentationProduct` |

---

---

## 4. Módulo de Clientes

**Controlador:** `app/Http/Controllers/Admin/CustomerController.php`  
**Livewire:** `app/Livewire/Customers/Customer.php`  
**Vistas:** `resources/views/Admin/customers/` y `resources/views/livewire/customers/`

### Flujo general

```
Listado de clientes (customer.index)
        │  Livewire — búsqueda en tiempo real por nombre, razón social, RFC, CP
        │  Toggle activos / deshabilitados (soft-delete vía status)
        │
        ├─ Click "+" → modal Agregar Cliente
        │       └─ POST customer.store → CustomerController@store → redirect back
        │
        ├─ Click editar → Livewire btnEdit() → evento showModalEdit → modal pre-cargado
        │       └─ POST customer.store (con campo id habilitado) → CustomerController@store → redirect back
        │
        └─ Click deshabilitar/habilitar → GET customer.destroy/{id}/{status}
                └─ CustomerController@destroy → status = 0 (deshabilitar) / 1 (habilitar)
```

### 4.1 Modelo `Customer`

**Tabla:** `customers`

| Campo | Descripción |
|-------|-------------|
| `name` | Nombre del cliente (requerido) |
| `razon_social` | Razón social para facturación |
| `rfc` | RFC |
| `postal_code` | Código postal |
| `regimen_fiscal` | Régimen fiscal SAT |
| `status` | `1` = activo, `0` = deshabilitado |

> **Nota:** El cliente con `id=1` es el cliente genérico "Público general", asignado por defecto a todas las ventas nuevas.

### 4.2 Crear / Actualizar

El mismo método `store()` maneja ambos casos:

```
¿Llega campo id en el request?
        │ No  → new Customer() → mensaje "agregado"
        │ Sí  → Customer::find($id) → mensaje "actualizado"
        ▼
Asigna: name, razon_social, rfc, postal_code, regimen_fiscal
        ▼
$customer->save()
        ▼
redirect()->back() con flash 'success'
```

El campo `id` en el modal parte como `disabled` (no se envía en el submit). Al editar, el JS lo habilita con el id correcto antes de que el usuario confirme.

### 4.3 Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver listado | *(ninguno — la vista es accesible)* |
| Crear cliente | `clientes / punto_venta / create` |
| Editar cliente | `clientes / punto_venta / update` |
| Deshabilitar / Habilitar | `clientes / punto_venta / destroy` |

### 4.4 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | Búsqueda con `orWhere` sueltos ignoraba el filtro `status`, mostrando clientes deshabilitados en la lista activa | `app/Livewire/Customers/Customer.php` | Agrupados los `orWhere` dentro de un closure |
| 2 | `step="10"` en el input de búsqueda (`type="text"`) — atributo inválido | `resources/views/livewire/customers/customer.blade.php` | Atributo eliminado |

---

---

## 5. Módulo de Compras

**Controlador:** `app/Http/Controllers/Admin/CompraController.php`  
**Livewire:** `app/Livewire/Compras/Compra.php`  
**Vistas:** `resources/views/Admin/compras/` y `resources/views/livewire/compras/`  
**JS:** `public/js/compras/create.js`

### Flujo general de estados

```
Nueva compra (status = 1) PENDIENTE
        │
        ▼ compra.status/{id}/2  — requiere permiso compras/auth
status = 2  AUTORIZADA
        │
        ▼ compra.status/{id}/3  — requiere fecha programación entrega
status = 3  SOLICITADA
        │
        └─ compra.status/{id}/4  →  fecha_recibido = hoy
               │                    fecha_vencimiento = hoy + plazo días
               ▼
        status = 4  RECIBIDA
               │   Se ingresan cantidades recibidas por producto
               │
               ▼ compra.storeRecibido
        status = 5  CERRADA
               │   Crea CuentaPagar automáticamente
               │   Suma cantidades a product.existence (si tipo OC o T)
               │   Sincroniza CXP con DB externa (si hay internet)

En cualquier status: compra.status/{id}/0 → RECHAZADA
```

### 5.1 Tipos de compra (`compras.tipo`)

| Valor | Descripción | Afecta existence |
|-------|-------------|-----------------|
| `OC` | Orden de compra | Sí — suma al cerrar |
| `S` | Servicio | No |
| `T` | Traspaso de matriz | Sí — suma al importar desde DB externa |

Los traspasos (`T`) se importan automáticamente al abrir el listado de compras (`traspasosMatriz()`), consultando la DB externa los últimos 30 días.

### 5.2 Modelos relacionados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `Compra` | `compras` | Cabecera de la compra |
| `DetalleCompra` | `detalles_compra` | Línea por producto (precio, impuestos, subtotal) |
| `DetalleCompraEntrada` | `detalle_compra_entradas` | Cantidad solicitada / recibida por línea |
| `CuentaPagar` | `cuentas_pagar` | Generada al cerrar — vincula compra con pago |
| `Proveedor` | `proveedores` | Proveedor de la compra |

### 5.3 Folio

El folio se asigna en dos pasos para garantizar que el `id` ya exista en la BD:

```php
$compra->folio = 0;        // 1. guarda con folio temporal
$compra->save();
$compra->folio = $compra->addFolio($compra->id);  // 2. folio = tipo + '-' + id
$compra->save();
```

Ejemplo: `OC-42`, `S-15`, `T-7`.

### 5.4 Cálculo de totales al cerrar (`storeRecibido`)

Al cerrar una compra (`status = 4 → 5`) con las cantidades realmente recibidas:

```
Por cada DetalleCompraEntrada con cantidad recibida:
    subtotal = precio_unitario × recibido
    impuestos = subtotal × amount_taxes
    total = subtotal + impuestos
    Si tipo OC o T: product.existence += recibido
Totales de compra = suma de todos los detalles
Crea CuentaPagar con esos totales
```

### 5.5 Permiso `compras / auth`

Controla quién puede **autorizar** una compra (pasar de status 1 → 2). Sin este permiso, el botón "Autorizar" no aparece y la ruta `compra.status/{id}/2` retorna error.

### 5.6 Sincronización con DB externa

| Evento | Acción |
|--------|--------|
| Crear / actualizar compra | `saveCompraDBExt()` si hay internet |
| Cerrar compra (storeRecibido) | `saveCXPDBExt()` si hay internet |
| Abrir listado (index) | `traspasosMatriz()` — importa traspasos de los últimos 30 días |

### 5.7 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `status()` siempre mostraba "autorizada" sin importar la acción realizada | `CompraController.php` | Mapa `[0=>'rechazada', 2=>'autorizada', 3=>'solicitada', 4=>'recibida']` |
| 2 | `@forelse` cerraba con `@endif` en lugar de `@endforelse` | `resources/views/Admin/compras/index.blade.php` | Cambiado a `@endforelse` |
| 3 | Livewire `render()` accedía `$this->compra->status` antes de verificar que no fuera null | `app/Livewire/Compras/Compra.php` | `is_object()` check movido antes de acceder al atributo |
| 4 | `Product::get()` traía todos los productos incluyendo inactivos | `app/Livewire/Compras/Compra.php` | Agregado `where('activo', 1)` |
| 5 | `destroy()` sin null check — crash si el detalle no existía | `CompraController.php` | Agrupado retorno de error antes de acceder al objeto |

---

---

## 6. Módulo de Cuentas por Pagar

**Controlador:** `app/Http/Controllers/Admin/CuentaPagarController.php`  
**Vistas:** `resources/views/Admin/cuentas_pagar/`

### Flujo general

```
Listado de CXP (cxp.index)
        │  Muestra todas las cuentas de la sucursal con status != 0
        │  Excluye traspasos (tipo = 'T') — solo OC y S generan CXP
        │  Toggle: Pendientes (status=1) / Pagadas (status=2)
        │
        ▼
Ver cuenta (cxp.show)
        │  Muestra encabezado: vencimiento, subtotal, impuestos, total
        │  Muestra tabla de pagos parciales acumulados con saldo restante
        │
        ├─ Registrar pago parcial → POST cxp.store/{cxp_id}
        │       Actualiza status de la CXP según si el saldo queda en 0
        │
        └─ Eliminar pago → GET cxp.destroy/{detail_id}
                Recalcula saldo y actualiza status de la CXP
```

### 6.1 Origen de las CXP

Las cuentas por pagar **no se crean manualmente**. Se generan automáticamente al cerrar una compra (`storeRecibido()` en `CompraController`):

```php
$cxp = new CuentaPagar();
$cxp->compra_id  = $compra->id;
$cxp->branch_id  = $empresa->branch_id;
$cxp->fecha_vencimiento = $compra->fecha_vencimiento;
$cxp->subtotal   = $compra->subtotal;
$cxp->impuestos  = $compra->impuesto_productos;
$cxp->total      = $compra->total;
$cxp->save();
```

Solo las compras de tipo `OC` (Orden de compra) y `S` (Servicio) generan CXP. Los traspasos (`T`) no aparecen en el listado.

### 6.2 Modelos relacionados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `CuentaPagar` | `cuentas_pagar` | Cabecera — total a pagar, vencimiento, status |
| `CuentaPagarDetail` | `cuentas_pagar_detail` | Registro de cada pago parcial (fecha + importe) |

### 6.3 Estados de una CXP (`cuentas_pagar.status`)

| Valor | Significado |
|-------|-------------|
| `1` | Pendiente — saldo > 0 |
| `2` | Pagada — saldo ≤ 0 |
| `0` | Cancelada / eliminada |

El status se recalcula automáticamente en cada operación de pago:
```php
$cuenta->status = ($cuenta->total - $total_pagado) <= 0 ? 2 : 1;
```

### 6.4 Registro de pagos parciales (`store`)

```
¿Llega cxp_detail_id en el request?
        │ Sí → actualiza el detalle existente (requiere permiso update)
        │ No → crea nuevo CuentaPagarDetail
        ▼
Recalcula saldo:
    total_debe = cxp.total - (sum_anterior - old_importe + nuevo_importe)
        ▼
¿total_debe ≤ 0?
    Sí → cxp.status = 2 (Pagada)
    No → cxp.status = 1 (Pendiente)
```

### 6.5 Eliminación de pagos (`destroy`)

El orden de operaciones es crítico para el cálculo correcto del status:

```
1. Guardar cxp_id antes del delete
2. Borrar el detalle (delete)
3. Re-sumar los detalles restantes (post-delete)
4. Recalcular y guardar status de la CXP
```

### 6.6 Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver listado | `cuentas_por_pagar` (módulo) |
| Ver detalle de cuenta | `cuentas_por_pagar / punto_venta / show` |
| Registrar pago | `cuentas_por_pagar / punto_venta / create` |
| Editar pago existente | `cuentas_por_pagar / punto_venta / update` |
| Eliminar pago | `cuentas_por_pagar / punto_venta / destroy` |

> Los botones de editar/eliminar en la tabla de pagos solo se muestran a roles `admin` y `root`.

### 6.7 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `index()` crasheaba si `EmpresaDetail::first()` devolvía null | `CuentaPagarController.php` | Agregado null check con redirect y mensaje de error |
| 2 | `@forelse` en listado cerraba con `@endif` | `cuentas_pagar/index.blade.php` | Cambiado a `@endforelse` |
| 3 | `@forelse` en detalle cerraba con `@endif` | `cuentas_pagar/show.blade.php` | Cambiado a `@endforelse` |
| 4 | `destroy()` calculaba el status con el sum ANTES del delete — marcaba la cuenta como Pagada incorrectamente | `CuentaPagarController.php` | Movido el `sum()` a después del `delete()` |
| 5 | `show()` sin null check — crasheaba si la cuenta no existía | `CuentaPagarController.php` | Agrupado redirect con error antes de acceder al modelo |

---

---

## 7. Módulo de Devoluciones

**Controlador:** `app/Http/Controllers/Admin/DevolucionController.php`  
**Livewire:** `app/Livewire/DevolucionCompra/DevolucionIndexMatriz.php`, `DevolucionCompra.php`, `DevolucionShowMatriz.php`  
**Vistas:** `resources/views/Admin/devoluciones/` y `resources/views/Admin/devoluciones_matriz/`  
**JS:** `public/js/devoluciones/create_devolucion.js`

El módulo tiene dos subtipos independientes:

| Tipo | Descripción | Modelo |
|------|-------------|--------|
| **Devolución de venta** | Cliente devuelve producto → regresa al inventario | `Devolucion` |
| **Devolución a matriz** | Sucursal devuelve producto al proveedor/matriz → sale del inventario | `DevolucionMatriz` |

---

### 7.1 Devolución de venta

#### Flujo

```
Listado de ventas de los últimos 7 días (devoluciones.showListadoVentas)
        │
        ▼
Seleccionar venta → devoluciones.createSaleToDevolucion
        │  Muestra productos vendidos en esa venta con sus cantidades
        │
        ▼
Cajero selecciona productos y cantidad a devolver (modal por producto)
        │  JS calcula: subtotal, impuestos, descuentos, total por línea
        │
        ▼
POST devoluciones.store
        │  Por cada producto devuelto:
        │    → Crea SaleDetail (status=0) vinculado a la venta original
        │    → Crea SaleDetailCant (status=0) con la cantidad devuelta
        │    → Suma cantidad a product.existence (regresa al inventario)
        │    → Si el descuento era por cantidad: restaura vigencia de la presentación
        │
        ▼
Crea / actualiza registro Devolucion con totales acumulados
        │
        ▼
Redirige a devoluciones.showDevSale → muestra ticket de devolución
```

#### Impacto en inventario

- **Al crear devolución:** `product.existence += cant_devuelta`
- **Al eliminar un ítem de la devolución:** `product.existence -= cant_devuelta`
- Si la presentación tenía descuento por cantidad (`vigencia_cantidad_fecha = 'cantidad'`): la vigencia se ajusta simétricamente

#### Impacto en cierre de caja

Las devoluciones del período del turno se descuentan del total del sistema:

```
total_sistema = (ventas_tarjeta + ventas_efectivo)
              - devoluciones_efectivo
              - devoluciones_tarjeta
```

El tipo (efectivo/tarjeta) se determina por `devolucion.getSale->type_payment` de la venta original.

---

### 7.2 Devolución a matriz (`DevolucionMatriz`)

#### Flujo

```
Listado de compras cerradas (status=5, status_devolucion=0)
        │  Solo compras que aún no tienen devolución registrada
        │
        ▼
Seleccionar compra → formulario con productos de esa compra
        │  Cajero ingresa cantidad a devolver por producto
        │
        ▼
POST devoluciones.storeMatriz
        │  Por cada producto:
        │    → Obtiene producto desde DetalleCompra (por detalle_compra_id)
        │    → Crea DevolucionMatriz con cantidades, impuestos, descuentos
        │    → Resta cantidad de product.existence (sale del inventario)
        │
        ▼
Marca compra.status_devolucion = 1 (ya no aparece en el selector)
```

---

### 7.3 Modelos relacionados

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `Devolucion` | `devoluciones` | Cabecera de devolución de venta |
| `DevolucionMatriz` | `devoluciones_matriz` | Devolución de producto a proveedor/matriz |
| `SaleDetail` (status=0) | `sales_detail` | Línea devuelta vinculada a la venta original |
| `SaleDetailCant` (status=0) | `sale_detail_cants` | Cantidad devuelta por línea |

### 7.4 Campos clave de `Devolucion`

| Campo | Descripción |
|-------|-------------|
| `sale_id` | Venta original |
| `user_dev` | Usuario que procesó la devolución |
| `cantidad` | Total de unidades devueltas (acumulado) |
| `total_descuentos` | Suma de descuentos de los productos devueltos |
| `total_devolucion` | Monto total devuelto (sin aplicar descuentos) |
| `branch_id` | Sucursal |
| `fecha_devolucion` | Fecha de la devolución |

---

### 7.5 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `showDevSale()` segundo null check verificaba `$devolution` en lugar de `$sale` — crash si la venta fue eliminada | `DevolucionController.php` | Corregido a `if(!is_object($sale))` |
| 2 | `showDevMatriz()` usaba `$devolution->getCompra->id` antes del null check | `DevolucionController.php` | Null check movido antes de acceder al objeto |
| 3 | `storeMatriz()` usaba `$request->code_product` (valor único) para todos los productos del loop — mismo producto en todos los registros | `DevolucionController.php` | Producto obtenido desde `DetalleCompra::find($key)->producto_id` por iteración |
| 4 | `storeMatriz()` asignaba `$product->product_id` (campo inexistente) en lugar de `$product->id` — guardaba null | `DevolucionController.php` | Cambiado a `$product->id` |
| 5 | `deleteDetailDev()` leía `total_descuento` (sin 's') — siempre null, los descuentos del registro nunca se restaban | `DevolucionController.php` | Corregido a `total_descuentos` |
| 6 | Búsqueda en `DevolucionIndexMatriz` ignoraba filtros de `status` y `branch_id` en `orWhere` sueltos | `DevolucionIndexMatriz.php` | Agrupados en closure |
| 7 | Búsqueda en `DevolucionCompra` ignoraba `status=5` y `status_devolucion=0` en `orWhere` suelto | `DevolucionCompra.php` | Agrupado en closure |
| 8 | `store()` creaba `SaleDetail` sin setear `product_id` (NOT NULL en BD) — crash al confirmar la devolución | `DevolucionController.php` | Agrupado `PartToProduct::find()` para obtener `product_id` |
| 9 | `BoxController::store()` crasheaba si la venta original de una devolución fue eliminada | `BoxController.php` | Agrupado `continue` si `getSale` retorna null |
| 10 | `BoxController::store()` no restaba `$total_devolucion_tarjeta` del total del sistema | `BoxController.php` | Agrupado `- $total_devolucion_tarjeta` en la fórmula del total |

---

---

## 8. Módulo: Cierre de Turno (Box / Caja)

### 8.1 Descripción general

Gestiona la apertura y cierre de turnos (cajas). Cada turno registra el monto inicial, todas las ventas y devoluciones del período, y al cerrarse captura el conteo físico de billetes y monedas. Permite detectar descuadres entre lo vendido en sistema y lo contado físicamente.

**Permisos requeridos:** `caja / punto_venta / create` (abrir turno), `caja / punto_venta / destroy` (cerrar turno).

### 8.2 Flujo de apertura de turno

1. Usuario accede a **Abrir turno** (`GET /start-amount-box`).
2. Ingresa el monto inicial en efectivo.
3. Si ya existe un cierre anterior con `monto_dejado_caja`, el sistema valida que el monto coincida (tolerancia exacta). Si no coincide y el usuario no marca "continuar de todas formas", regresa con error.
4. Se crea un `Box` con `status=0` (abierto), `start_date` y `start_amount_box`.
5. Redirige a `sale.index` — el turno queda activo.

**Protección de doble turno:** si ya existe un `Box` con `status=0` para ese usuario, redirige con mensaje informativo sin crear un segundo registro.

### 8.3 Flujo de cierre de turno

```
GET /turn-off
│
├─ Busca Box(status=0, user_id) → si no existe Y no hay session('ticket') → error "No tienes turno abierto"
├─ Calcula ventas del período (entre start_date y now)
├─ Clasifica: todas cerradas (status=2) → status=1, alguna pendiente → status=2
└─ Muestra formulario con totales del sistema

POST /turn-off-store (store)
│
├─ Calcula totales del sistema:
│    total_efectivo  ← Sum(Sale status=2, type=efectivo, del período)
│    total_tarjeta   ← Sum(Sale status=2, type=tarjeta, del período)
│    total_devolucion_efectivo ← Sum(Devolucion.total_devolucion donde venta fue efectivo)
│    total_devolucion_tarjeta  ← Sum(Devolucion.total_devolucion donde venta fue tarjeta)
│
├─ Calcula total a ingresar en efectivo:
│    total_efect = start_amount_box + total_efectivo - total_devolucion_efectivo
│
├─ Valida montos ingresados por el usuario (tolerancia de $1 en efectivo)
├─ Valida conteo de billetes y monedas vs total_efect
│
├─ Guarda en Box:
│    amount_cash_system, amount_credit_system, total_system (sistema)
│    amount_cash_user, amount_credit_user, total_user (ingresado por empleado)
│    ticket_1000..ticket_20, coin_20..coin_50_cen (denominaciones)
│    monto_dejado_caja (efectivo que se deja en caja para el siguiente turno)
│    status = 1 (cuadrado) ó 2 (descuadre)
│
├─ Sincroniza con BD externa:
│    getSalesDbExt() → ventas del día
│    getDevolutionDBExt() → devoluciones de los últimos 8 días
│    getComprasDbExt() → compras de los últimos 8 días
│
└─ redirect()->back()->with('ticket', 'ok')
   → turnOff() detecta session('ticket'), muestra modal con ticket imprimible
```

### 8.4 Ticket de cierre

Al guardar exitosamente el cierre, se muestra un modal con un `<iframe>` que carga:

```
GET /ticket-box/{user_id}/true
```

- El parámetro `true` activa auto-impresión del ticket.
- El iframe carga perezosamente (solo cuando el modal se abre) para evitar tiempo de espera antes de que el formulario termine.
- Un spinner cubre el iframe hasta que el evento `load` del iframe dispara — a partir de ahí se oculta el spinner y el ticket aparece con transición de opacidad.
- Los dos botones del modal ("Regresar" y "Cerrar Turno") redirigen a `box.statusBox` que cierra la sesión y redirige al login.

### 8.5 Modelo `Box`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `user_id` | FK | Usuario dueño del turno |
| `status` | int | `0`=abierto, `1`=cerrado cuadrado, `2`=cerrado con descuadre |
| `start_date` | datetime | Apertura del turno |
| `end_date` | datetime | Cierre del turno |
| `start_amount_box` | decimal | Monto inicial en efectivo |
| `amount_cash_system` | decimal | Total efectivo según sistema |
| `amount_credit_system` | decimal | Total tarjeta según sistema |
| `total_system` | decimal | Total ventas neto (efectivo + tarjeta − devoluciones) |
| `amount_cash_user` | decimal | Efectivo contado por el empleado |
| `amount_credit_user` | decimal | Tarjeta contado por el empleado |
| `total_user` | decimal | Total ingresado por empleado |
| `monto_dejado_caja` | decimal | Efectivo que queda en caja para el siguiente turno |
| `ticket_1000..ticket_20` | int | Conteo de billetes por denominación |
| `coin_20..coin_50_cen` | int | Conteo de monedas por denominación |

### 8.6 Reglas de validación del cierre

- **Efectivo (si hay descuadre):** `monto_efectivo` debe ser igual a `total_efect` (start + ventas − devoluciones). Tolerancia: hasta $1 de diferencia se acepta sin bloquear.
- **Tarjeta:** `monto_tarjeta` debe ser exactamente igual al `total_tarjeta` del sistema.
- **Conteo de denominaciones:** la suma de todos los billetes + monedas debe igualar `total_efect`. Si no coincide y hay descuadre, se bloquea.
- El usuario puede marcar "Aceptar" (`$request->acept`) para omitir validaciones (override de supervisor).

### 8.7 Listado de cierres (Livewire `Box`)

Componente [app/Livewire/Boxes/Box.php](app/Livewire/Boxes/Box.php) — lista paginada de cierres con `end_date ≤ hoy`, ordenados por fecha. Desde la lista se puede abrir un modal de denominaciones (`openModalMoney`) para revisar el conteo de billetes/monedas de cada cierre histórico.

### 8.8 Bugs corregidos en esta sesión

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | Flash key incorrecto en validación de monto inicial — mensaje no aparecía en la vista | `BoxController.php` | Cambiado a `->with('monto', ...)` coincidiendo con la vista |
| 2 | `store()` crasheaba si no existía turno abierto (acceso directo a la ruta) | `BoxController.php` | Null check con redirect a `sale.index` |
| 3 | `turnOff()` redirigía con "no tienes turno" después de un cierre exitoso — `store()` cierra el box (status≠0), luego `redirect()->back()` llama `turnOff()` que ya no encuentra box abierto | `BoxController.php` | Guard: `if(!is_object($box) && !session()->has('ticket'))` — si viene del cierre deja pasar |
| 4 | Devoluciones en tarjeta no se restaban del total del sistema al calcular `$totales` | `BoxController.php` | Agregado `- $total_devolucion_tarjeta` en la fórmula |
| 5 | `store()` crasheaba si la venta relacionada a una devolución había sido eliminada | `BoxController.php` | `continue` si `$item->getSale` retorna null |
| 6 | `Livewire\Boxes\Box` importaba `Devolution` (modelo inexistente) | `Livewire/Boxes/Box.php` | Eliminado del use-block |
| 7 | Iframe del ticket cargaba desde el inicio de la página (antes de mostrar el modal) — usuario veía demora sin retroalimentación | `_modal_ticket.blade.php` | Iframe con `src="about:blank"`, carga solo al abrir modal; spinner overlay hasta que dispara `load` |
| 8 | URL del iframe usaba `Auth::User()->id/true` (división PHP = solo el ID) — nunca pasaba el parámetro `auto` | `_modal_ticket.blade.php` | Corregido a `route('ticket.box', [id, 'true'])` |
| 9 | `turnOff()` — tras un cierre exitoso `$box` queda null, pero `$box->start_date ?? null` lanza `TypeError` en PHP 8 (`??` no protege acceso a propiedad en null) | `BoxController.php` | Separado en dos ramas: si `$box` es null y `session('ticket')` existe, retorna la vista vacía directamente sin acceder a propiedades del objeto |
| 10 | `rules()` — cuando hay descuadre en efectivo Y tarjeta, `$arr[0]` de efectivo era sobreescrito por `$arr[0]` de tarjeta — la validación de efectivo se perdía silenciosamente | `BoxController.php` | Reemplazado a `$arr[0]['monto_efectivo']` y `$arr[0]['monto_tarjeta']` para que ambas coexistan en el mismo array |
| 11 | `statusBox()` — rama `$status == 0` accedía a `$box->status` sin null check — crash si el usuario no tiene ningún turno registrado | `BoxController.php` | Null check con redirect a `sale.index` |
| 12 | `statusBox()` — `Auth::logout(Auth::User())` pasa el usuario como argumento; `logout()` no acepta parámetros en Laravel 10 | `BoxController.php` | Corregido a `Auth::logout()` |
| 13 | `Box::getTotalDevolutions()` consultaba `SaleDetail` de TODOS los usuarios en el rango de fechas sin filtrar por `user_id` — inflaba devoluciones de cada cierre; además usaba `SaleDetail::sum('total')` inconsistente con `Devolucion::sum('total_devolucion')` del controlador | `Models/Box.php` | Reemplazado por `Devolucion::whereIn('sale_id', Sale::where('user_id', $this->user_id)->pluck('id'))->sum('total_devolucion')` |
| 14 | `box.blade.php` — `$user->getBranch->name` crash en PHP 8 si el usuario no tiene sucursal asignada (`null->name` lanza `TypeError`) | `livewire/boxes/box.blade.php` | Cambiado a `$user->getBranch?->name ?? ''` (operador null-safe) |
| 15 | `start_amount.blade.php` — usaba `class="bi bi-info-circle-fill"` (Bootstrap Icons) que no está cargado en la página — el ícono no renderizaba | `Admin/box/start_amount.blade.php` | Cambiado a `class="fa fa-info-circle"` (Font Awesome, sí disponible) |
| 16 | Listado de cierres — "Efectivo diferencia de" mostraba $172.71 en lugar de $0.13 porque comparaba ventas brutas contra lo reportado sin restar devoluciones; también el badge de descuadre de efectivo en "Completada Irregular" se activaba siempre que hubiera devoluciones | `livewire/boxes/box.blade.php` | `$cash_diff = (start + ventas_efectivo − devoluciones) − lo_reportado`; badge solo aparece cuando `abs($cash_diff) > 1`; `$devs` calculado una vez por fila via `@php` en lugar de 3 llamadas separadas |

---

*Última actualización: 2026-06-17*

---

## 11. Módulo de Roles y Permisos

### 11.1 Descripción general

Sistema de control de acceso propio (no usa Spatie ni Gate de Laravel). Cada usuario tiene uno o más **roles** (`user_roles`), cada rol tiene uno o más **permisos** (`permission_role`), y cada permiso representa una combinación `module + submodule + action`.

El middleware `CheckPermission` intercepta todas las rutas protegidas y verifica si el usuario autenticado tiene el permiso requerido antes de dejar pasar la request.

### 11.2 Arquitectura del sistema de permisos

```
Usuario
  └─ UserRole (user_roles)
       └─ Role (roles)
            └─ PermissionRole (permission_role)
                 └─ Permission (permissions)
                      ├─ module     → identificador del módulo (ej. "ventas")
                      ├─ submodule  → contexto (ej. "punto_venta")
                      └─ action     → operación (show|create|update|destroy|auth)
```

**Método clave en `User`:** `hasPermissionThroughModule($module, $submodule = null, $action = null)`
Recorre los roles del usuario, luego sus permisos, y retorna `true` si encuentra uno que coincida con el módulo/submódulo/acción solicitados.

### 11.3 Middleware `CheckPermission`

Registrado como `permission` en el kernel. Se usa en rutas así:

```php
->middleware('permission:ventas')                        // solo módulo
->middleware('permission:ventas,punto_venta,create')    // módulo + acción
->middleware('permission:ventas,punto_venta,[create|update]')  // múltiples acciones
```

El middleware descompone la acción con `explode('|', ...)` y acepta el request si el usuario tiene **cualquiera** de las acciones listadas.

> **Nota:** el bypass `root` está comentado en el middleware — un `root` debe tener permisos explícitos asignados como cualquier otro rol.

### 11.4 Rutas

**Roles:**

| Método | URI | Nombre | Permiso |
|--------|-----|---------|---------|
| GET | `/roles/{status}` | `roles.index` | `roles` |
| POST | `/roles-store` | `roles.store` | `roles,punto_venta,create` |
| POST | `/roles-update` | `roles.update` | `roles,punto_venta,update` |
| GET | `/roles-destroy/{id}` | `roles.destroy` | `roles,punto_venta,destroy` |
| GET | `/roles-enable/{id}` | `roles.enable` | `roles,punto_venta,destroy` |
| GET | `/roles-permissions/{role}` | `roles.permissions` | `roles,punto_venta,[show\|update]` |
| POST | `/roles-sync-permissions/{role}` | `roles.permissions.sync` | `roles,punto_venta,auth` |

**Permisos:**

| Método | URI | Nombre | Permiso |
|--------|-----|---------|---------|
| GET | `/permissions` | `permission.index` | `roles` |
| POST | `/permissions-store` | `permission.store` | `roles,punto_venta,create` |
| POST | `/permissions-update` | `permission.update` | `roles,punto_venta,update` |
| GET | `/permissions-desctroy/{id}` | `permission.destroy` | `roles,punto_venta,destroy` |

### 11.5 Flujo de gestión de roles

```
Listado (index)
│  status=1 → activos    status=0 → inhabilitados
│  root ve todos; otros no ven el rol "root"
│
├─ Crear → store()
│    Valida: name (required, unique:roles,name, max:50)
│
├─ Editar → update()
│    Valida: name (required, unique ignorando ID actual, max:50)
│    id en hidden input del modal
│
├─ Inhabilitar/Habilitar → destroy() / enable()
│    Cambia status = 0 / 1
│
└─ Asignar permisos → permissions() + syncPermissions()
     GET /roles-permissions/{role} → carga _permissions.blade.php vía AJAX
     Vista agrupa permisos por módulo, muestra checkboxes con tooltips descriptivos
     "Permisos no implementados" se muestran deshabilitados (opacidad reducida)
     POST /roles-sync-permissions/{role} → sync() en tabla permission_role
     Respuesta JSON: {success, message, data.permissions_count}
```

### 11.6 Vista de permisos (`_permissions.blade.php`)

Cargada vía AJAX dentro del modal. Agrupa permisos por módulo y muestra un grid de checkboxes:

- **"Todo"** (select-all por módulo): marca/desmarca todos los permisos habilitados del módulo.
- **Tooltip `?`**: cada permiso tiene un tooltip CSS-only con descripción específica por módulo+acción (definida en `$specificTooltips`).
- **Permisos no implementados**: si el tooltip empieza con "No implementado", el checkbox se desactiva visualmente (`perm-item-disabled`) y se envía `disabled` para no incluirlo en el sync.
- Los módulos sin entrada en `$moduleLabels` se muestran con nombre capitalizado automáticamente.

### 11.7 Modelos

**`Role`** — tabla `roles`

| Campo | Descripción |
|-------|-------------|
| `name` | Nombre único del rol |
| `description` | Descripción opcional |
| `status` | `1`=activo, `0`=inhabilitado |

Relaciones: `permissions()` → `belongsToMany(Permission)` vía `permission_role` · `users()` → `belongsToMany(App\Models\User)`

**`Permission`** — tabla `permissions`

| Campo | Descripción |
|-------|-------------|
| `module` | Identificador del módulo (ej. `ventas`, `inventarios`) |
| `submodule` | Contexto (ej. `punto_venta`) |
| `action` | Operación: `show`, `create`, `update`, `destroy`, `auth` |
| `description` | Descripción legible del permiso |

**`PermissionRole`** — tabla `permission_role` (pivote `Role ↔ Permission`)

**`UserRole`** — tabla `user_roles` (pivote `User ↔ Role`)

### 11.8 Acciones por módulo (`auth`)

El permiso `auth` tiene significado distinto según el módulo:

| Módulo | Qué habilita `auth` |
|--------|---------------------|
| `ventas` | Vender con stock en cero + búsqueda ampliada en POS |
| `compras` | Cambiar estatus de OC a "Autorizada" |
| `usuarios` | Asignar roles y turnos a usuarios |
| `roles` | Abrir y guardar el modal de permisos |
| `empresa` | Guardar datos de empresa + acceder a importación desde DB externa |

### 11.9 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `index()` — `$status` del parámetro URL nunca se usaba en la query; ambas ramas siempre hacían `where('status', 1)` — la vista de inhabilitados nunca mostraba nada | `RoleController.php` | Refactorizado a `Role::where('status', $status)` con filtro de `root` condicional |
| 2 | `update()` — regla `unique:roles,name` sin ignorar el ID actual — guardar un rol sin cambiar el nombre fallaba con "El nombre ya existe" | `RoleController.php` | Cambiado a `unique:roles,name,{$request->id}` |
| 3 | `store()` y `update()` — `$validated` declarado pero nunca usado | `RoleController.php` | Eliminada la asignación |
| 4 | `Role::users()` — namespace `App\User` incorrecto en Laravel 8+; lanzaría `Class 'App\User' not found` si se llamara la relación; además `withTimesTamps()` era typo | `Models/Role.php` | Corregido a `App\Models\User` y `withTimestamps()` |
| 5 | `PermissionController::store()` — sin validación; `module` y `action` (campos clave del sistema) podían guardarse vacíos | `PermissionController.php` | Agregado `$request->validate(['module' => 'required', 'action' => 'required'])` |
| 6 | `_modal.blade.php` — `Descripción*` implicaba campo requerido pero no tenía `required` ni se validaba en el controlador | `Admin/roles/_modal.blade.php` | Eliminado el `*` |

---

## 10. Módulo de Turnos

### 10.1 Descripción general

Gestiona los turnos laborales (horario de entrada y salida) que se asignan a los usuarios. Es un catálogo de configuración — no controla el flujo de caja (eso lo hace el módulo de Cierre de Turno/Box). Los turnos se habilitan/inhabilitan en lugar de eliminarse.

**Permisos requeridos:** `turnos / punto_venta / create|update|destroy`

### 10.2 Rutas

| Método | URI | Nombre | Permiso |
|--------|-----|---------|---------|
| GET | `/turnos/{status}` | `turnos.index` | `turnos` |
| POST | `/turnos-store` | `turnos.store` | `turnos,punto_venta,create` |
| POST | `/turnos-update` | `turnos.update` | `turnos,punto_venta,update` |
| GET | `/turnos-destroy/{id}` | `turnos.destroy` | `turnos,punto_venta,destroy` |
| GET | `/turnos-enable/{id}` | `turnos.enable` | `turnos,punto_venta,destroy` |

`{status}` es requerido: `1` = turnos activos, `0` = inhabilitados.

### 10.3 Flujo CRUD

El módulo usa un único modal (`_modal.blade.php`) tanto para crear como para editar, cambiando el `action` del formulario vía JS:

```
Listado (index)
│  GET /turnos/1 → activos    GET /turnos/0 → inhabilitados
│
├─ Crear
│    btnShow() → muestra modal con action = turnos.store
│    POST /turnos-store → store()
│    Valida: turno (required), entrada (required), salida (required)
│    Crea Turno con status=1 por defecto
│
├─ Editar
│    btnEdit(turno) → inyecta datos del modelo en el modal, cambia action a turnos.update
│    POST /turnos-update → update()
│    Valida: id (required|integer), turno, entrada, salida
│    Busca Turno::find($request->id), actualiza campos
│
├─ Inhabilitar
│    GET /turnos-destroy/{id} → destroy() → status = 0
│
└─ Habilitar
     GET /turnos-enable/{id} → enable() → status = 1
```

### 10.4 Modelo `Turno`

Tabla: `turnos`

| Campo | Descripción |
|-------|-------------|
| `turno` | Nombre del turno (ej. "Matutino") |
| `description` | Descripción opcional |
| `entrada` | Hora de entrada (formato `HH:MM`) |
| `salida` | Hora de salida (formato `HH:MM`) |
| `status` | `1` = activo, `0` = inhabilitado |

Relación en `User`: `getTurno()` → `hasOne(Turno, 'id', 'turno_id')`.

### 10.5 Lógica del modal (JS)

```js
btnShow()        // limpia el modal, action = store
btnEdit(turno)   // inyecta {id, turno, description, entrada, salida}, action = update
btnCancel()      // oculta modal, restablece action = store, limpia inputs
```

El objeto `turno` viene del modelo serializado a JSON via `{{ $item }}` en el `onClick` de cada fila.

### 10.6 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `store()` — `$validated` declarado pero no usado; la variable capturaba el resultado de `validate()` pero el código accedía a `$request->xxx` directamente | `TurnoController.php` | Eliminada la asignación — se llama `$request->validate()` sin asignar |
| 2 | `update()` — mismo patrón `$validated` no usado; además `id` no estaba en las reglas de validación — si el hidden llegaba vacío, el usuario recibía "Ocurrio un error" sin contexto | `TurnoController.php` | Eliminada asignación; agregado `'id' => 'required\|integer'` a las reglas |
| 3 | `_modal.blade.php` — etiqueta `Descripción*` implicaba campo requerido, pero el `<textarea>` no tenía `required` ni el controlador lo validaba — `*` engañoso | `_modal.blade.php` | Eliminado el `*` de la etiqueta |

---

## 9. Módulo de Sucursales

### 9.1 Descripción general

Gestiona las sucursales del negocio. Cada usuario pertenece a una o más sucursales (`branch_user`) y tiene una sucursal activa (`users.branch_id`). El módulo permite crear, editar, inhabilitar y seleccionar sucursal activa. Solo usuarios `root` o `admin` ven todas las sucursales; los demás solo ven las que tienen asignadas.

**Permisos requeridos:** `sucursales / punto_venta / create|show|update|destroy`

### 9.2 Rutas

| Método | URI | Nombre | Permiso |
|--------|-----|---------|---------|
| GET | `/branchs/{status?}` | `branchs.index` | `sucursales` |
| GET | `/branchs-create` | `branchs.create` | `sucursales,punto_venta,create` |
| POST | `/branchs-store/{branch_id?}` | `branchs.store` | `sucursales,punto_venta,[create\|update]` |
| GET | `/branchs-show/{branch_id}` | `branchs.show` | `sucursales,punto_venta,[show\|update]` |
| GET | `/branchs-destroy/{id}/{status?}` | `branchs.destroy` | `sucursales,punto_venta,destroy` |
| GET | `/branchs-set-branch/{branch_id}` | `branchs.setSucursalUser` | — (solo auth) |
| GET | `/quickbase-import/{table_name}` | `branchs.import` | — (solo auth) |

### 9.3 Flujo CRUD

```
Listado (index)
│  status=1 → sucursales activas   status=0 → inhabilitadas
│  root/admin ven todas; otros solo las que tienen asignadas
│
├─ Crear (create → store POST)
│    Valida: name (required), address (required)
│    Guarda: name, address, razon_social, rfc, phone
│    user_id = Auth::User()->id (solo al crear)
│    Elimina BranchUser previos y re-inserta los seleccionados
│
├─ Ver/Editar (show)
│    Vista en modo readonly (status=1)
│    Botón "Habilitar edición" → JS quita readonly y muestra botón Guardar
│    Al guardar → store POST con branch_id → actualiza sin cambiar user_id original
│    Elimina BranchUser y re-inserta seleccionados (incluso si quedan vacíos)
│
├─ Inhabilitar/Habilitar (destroy)
│    destroy(id, 0) → status=0 (inhabilita)
│    destroy(id, 1) → status=1 (habilita)
│
└─ Seleccionar sucursal activa (setSucursalUser)
     Verifica que la sucursal exista
     Si el usuario ya tiene branch_id distinto y no es root/admin → error
     Guarda users.branch_id = branch_id → redirect a sale.index
```

### 9.4 Modelos

**`Branch`** — tabla `branchs`

| Campo | Descripción |
|-------|-------------|
| `user_id` | Usuario que creó la sucursal |
| `name` | Nombre de la sucursal |
| `razon_social` | Razón social |
| `address` | Dirección |
| `rfc` | RFC |
| `phone` | Teléfono |
| `status` | `1`=activa, `0`=inhabilitada |

Método: `getUsers($branch_id)` → colección de `BranchUser` con `user_id`, o la cadena `'false'` si no hay usuarios asignados.

**`BranchUser`** — tabla `branch_user` (pivote)

| Campo | Descripción |
|-------|-------------|
| `branch_id` | FK → `branchs.id` |
| `user_id` | FK → `users.id` |

Método: `getBranch()` → `hasOne(Branch, 'id', 'branch_id')`

**Relaciones en `User`**

| Método | Descripción |
|--------|-------------|
| `getBranch()` | `hasOne(Branch, 'id', 'branch_id')` — sucursal activa |
| `getBranchs()` | `hasMany(BranchUser, 'user_id', 'id')` — todas las sucursales asignadas |
| `hasBranch($branch_id)` | Verifica si el usuario tiene asignada una sucursal específica |

### 9.5 Importación desde QuickBase

`importarQuickbase($table_name)` descarga datos vía `getQuickBase($table_name)` (método del controlador base) y crea las sucursales que no existen (deduplica por `razon_social`). Sin middleware de permiso — solo requiere estar autenticado.

`getProducts2($branch_id)` importa marcas y luego productos desde QuickBase para una sucursal. Los botones de importación en la vista `show.blade.php` están comentados (`{{-- --}}`).

### 9.6 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `store()` — sin null check tras `Branch::find($branch_id)` — crash si el ID no existe | `BranchController.php` | Redirect con error si `!is_object($branch)` |
| 2 | `store()` — `razon_social`, `rfc` y `phone` nunca se guardaban; el método solo asignaba `name` y `address` | `BranchController.php` | Agregados los tres campos al guardado |
| 3 | `store()` — al quitar todos los usuarios del multiselect, los `BranchUser` existentes no se eliminaban porque el `delete()` estaba dentro del `if(isset($request->user_id))` | `BranchController.php` | `BranchUser::delete()` movido fuera del condicional — siempre limpia antes de re-insertar |
| 4 | `store()` — al actualizar sobreescribía `user_id` (creador) con el usuario que editaba | `BranchController.php` | `user_id` solo se setea en la rama de creación (`else`) |
| 5 | `setSucursalUser()` — no verificaba que el `branch_id` existiera; cualquier ID numérico se guardaba en `users.branch_id` | `BranchController.php` | `Branch::find()` + null check antes de asignar |
| 6 | `index()` — `User::get()` hacía una query innecesaria; `$users` no se usaba en `index.blade.php` | `BranchController.php` | Eliminada la query |
| 7 | `index.blade.php` — `@empty` vacío; no se mostraba ningún mensaje cuando no había sucursales | `Admin/branchs/index.blade.php` | Agregado mensaje "No hay sucursales registradas/inhabilitadas" |

---

## 12. Módulo de Usuarios

**Controlador:** `app/Http/Controllers/Admin/UserController.php`  
**Vista principal:** `resources/views/Admin/users/index.blade.php`  
**Modales:** `resources/views/Admin/users/_modal.blade.php`, `_modalStoreEdit.blade.php`  
**Rutas:** `routes/admin.php` bajo prefijo `/usuarios`  
**Permiso:** `hasPermissionThroughModule('usuarios', 'punto_venta', 'create|update|destroy|show|auth')`

### 12.1 Rutas

| Método | URI | Método controlador | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/usuarios/{status?}` | `index($status=1)` | Listado de usuarios activos/inhabilitados |
| POST | `/usuarios/store` | `store(Request)` | Crear usuario |
| POST | `/usuarios/update` | `update(Request)` | Actualizar usuario |
| GET | `/usuarios/destroy/{id}/{status}` | `destroy($id, $status)` | Inhabilitar/habilitar usuario |
| POST | `/usuarios/roles-turnos` | `rolesTurnos(Request)` | Asignar roles, turno y sucursales |
| PUT | `/usuarios/roles-turnos` | `updateRolesTurnos(Request)` | Alias de rolesTurnos (para formulario PUT) |
| GET | `/logout` | `logout()` | Cerrar sesión |

### 12.2 Flujo principal

```
GET /usuarios/{status}
    │
    ├─ Si root → todos los usuarios con ese status
    │  Si no root → excluye usuario 'TCI_DEV' y rol 'root'
    │
    ├─ Construye $user_branch[user_id] = colección de BranchUser
    │
    └─ Retorna vista con: users, roles, turnos, branchs, status, user_branch

POST /usuarios/store
    │
    ├─ Valida: name (required), phone (required), email (required|email|unique:users,email),
    │          password (required), confirmedPass (required|same:password)
    └─ Crea User con contraseña bcrypt

POST /usuarios/update
    │
    ├─ Valida: name, phone, email (required|email, SIN unique para permitir guardar sin cambiar email)
    ├─ Null check: User::find($request->user_id) → error si no existe
    ├─ Si $request->password no vacío → valida confirmedPass y actualiza bcrypt
    └─ Guarda name, email, phone

POST /usuarios/roles-turnos
    │
    ├─ Null check: User::find($request->id)
    ├─ Si turno_id → user->turno_id = turno_id
    ├─ BranchUser::where('user_id')->delete() siempre (limpia antes de reinsertar)
    ├─ Si branch_id[] → inserta nuevos BranchUser
    ├─ UserRole::where('user_id')->delete() siempre
    └─ Si role_id[] → inserta nuevos UserRole
```

### 12.3 Login de usuario

El login está en el mismo controlador (`loginUser`). Ver [Sección 1](#1-login-y-autenticación) para el flujo completo.

### 12.4 Modelo `User`

**Tabla:** `users`

| Campo | Descripción |
|-------|-------------|
| `name` | Nombre del usuario |
| `email` | Correo electrónico |
| `phone` | Teléfono (usado como identificador de login) |
| `password` | Hash bcrypt |
| `status` | `1`=activo, `0`=inhabilitado |
| `turno_id` | FK → `turnos.id` |
| `branch_id` | FK → sucursal activa actual |

**Relaciones relevantes**

| Método | Descripción |
|--------|-------------|
| `getRoles()` | `hasMany(UserRole, 'user_id', 'id')` |
| `getTurno()` | `hasOne(Turno, 'id', 'turno_id')` |
| `getBranch()` | `hasOne(Branch, 'id', 'branch_id')` — sucursal activa |
| `getBranchs()` | `hasMany(BranchUser, 'user_id', 'id')` — todas las sucursales asignadas |
| `hasRole($name)` | Verifica si el usuario tiene el rol indicado |
| `hasAnyRole([...])` | Verifica si tiene alguno de los roles indicados |
| `hasPermissionThroughModule($module, $submodule, $action)` | Verifica permiso completo |

### 12.5 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `store()` — sin validación de formato ni unicidad del email; podían crearse usuarios con emails duplicados o malformados | `UserController.php` | Agregadas reglas `email\|unique:users,email` |
| 2 | `store()` — sin confirmación de contraseña; cualquier valor se guardaba sin verificación | `UserController.php` | Agregada regla `confirmedPass: required\|same:password` |
| 3 | `update()` — sin null check tras `User::find()`; crash si el ID no existe | `UserController.php` | Null check + redirect con error |
| 4 | `update()` — `$validatedData` asignado pero nunca usado; validación aplicada con `validate()` pero los datos se leían de `$request` directamente (inconsistencia) | `UserController.php` | Eliminado `$validatedData`, uso directo de `$request` |
| 5 | `update()` — si se enviaba una contraseña nueva, se guardaba sin confirmar | `UserController.php` | Validación condicional: si `$request->password` no está vacío → valida `confirmedPass` antes de guardar |
| 6 | `destroy()` — el mensaje decía siempre "Se inhabilitó el usuario" sin importar si se habilitaba | `UserController.php` | Mensaje dinámico: `$status == 0 ? 'inhabilitó' : 'habilitó'` |
| 7 | `logout()` — redirigía a `branchs.index` en lugar de `login`; el usuario quedaba en una ruta protegida sin sesión | `UserController.php` | `redirect()->route('login')` |
| 8 | `rolesTurnos()` — sin null check: si `User::find()` retornaba null, el código intentaba acceder a propiedades y caía en el `if(is_object($user))` anidado sin llegar al `return` final | `UserController.php` | Early return si `!is_object($user)` + eliminado `if(is_object($user))` interno redundante |
| 9 | `rolesTurnos()` — `UserRole::where('user_id', $request->id)` duplicado (una vez para delete, otra vez en un segundo `where` nunca ejecutado) | `UserController.php` | Eliminada la cláusula `where` duplicada |
| 10 | `updateRolesTurnos()` — la ruta PUT `/usuarios/roles-turnos` apuntaba a un método que no existía | `UserController.php` | Agregado `updateRolesTurnos()` como alias de `rolesTurnos()` |
| 11 | `index.blade.php` — CDN de Bootstrap 4 duplicado; Bootstrap ya carga vía Vite (`app.css`), el CDN extra causaba conflictos de estilos | `Admin/users/index.blade.php` | Eliminada la línea `<link href="https://cdn.jsdelivr.net/.../bootstrap@4.6.2/...">` |
| 12 | `index.blade.php` — `$branch->getBranch->name` sin null-safe; crash en PHP 8 si `getBranch` retorna null (usuario con branch_id huérfano) | `Admin/users/index.blade.php` | Cambiado a `$branch->getBranch?->name ?? ''` |

---

## 13. Módulo de Empresa

**Controlador:** `app/Http/Controllers/Admin/AdminController.php`  
**Vista:** `resources/views/Admin/empresa/show.blade.php`  
**Modelo:** `app/Models/EmpresaDetail.php`  
**Rutas:** `routes/admin.php`  
**Permiso:** `hasPermissionThroughModule('empresa', 'punto_venta', 'show|update|auth')`

### 13.1 Rutas

| Método | URI | Método controlador | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/empresa` | `empresa()` | Mostrar datos de la empresa |
| POST | `/empresa-update` | `empresaUpdate(Request)` | Actualizar datos de la empresa |

### 13.2 Flujo principal

```
GET /empresa
    │
    ├─ Obtiene EmpresaDetail::first() (puede ser null si no hay registro)
    ├─ Branch::where('status',1)->get() — solo sucursales activas
    ├─ Descifra vigencia (Crypt::decrypt) dentro de try/catch
    └─ Retorna vista con: empresa, branchs, vigencia

POST /empresa-update
    │
    ├─ Verifica rol: solo root o admin pueden actualizar
    ├─ Valida: name (required), rfc (required|max:13), address (required)
    ├─ EmpresaDetail::first() — si null crea nuevo registro
    ├─ Guarda: razon_social, name, rfc, address, branch_id (en MAYÚSCULAS los primeros 3)
    ├─ Si root && vigencia enviada → cifra con Crypt::encrypt y guarda
    └─ Maneja cualquier excepción con redirect()->back()->with('error', ...)
```

### 13.3 Modelo `EmpresaDetail`

**Tabla:** `empresa_details`

| Campo | Descripción |
|-------|-------------|
| `name` | Nombre de la empresa |
| `razon_social` | Razón social |
| `rfc` | RFC |
| `address` | Dirección |
| `vigencia` | Fecha límite de licencia (cifrada con Crypt) |
| `path_logo` | Ruta al logo |
| `branch_id` | FK → `branchs.id` — sucursal principal |

**Métodos**

| Método | Descripción |
|--------|-------------|
| `setEmpresa($detail)` | Crea o actualiza el registro desde colección externa (importación QuickBase) |
| `getBranch()` | `hasOne(Branch, 'id', 'branch_id')` |

> **Nota:** `razon_social` y `branch_id` no están en `$fillable`. No es un problema en producción porque se asignan directamente sobre el modelo (no vía `create()`/`fill()`), pero limita el uso de mass assignment.

### 13.4 Vigencia de licencia

- Solo visible y editable para usuarios con rol `root`.
- Se almacena cifrada con el facade `Crypt` de Laravel.
- En `loginUser()`, se descifra y compara con la fecha actual: si venció → "Licencia vencida, contacta al proveedor." (solo para usuarios que no son root).
- En `empresa()`, el descifrado se hace dentro de `try/catch` para no crashear si el valor está corrupto.

### 13.5 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `empresa()` — `Branch::get()` traía todas las sucursales incluyendo las inhabilitadas | `AdminController.php` | Cambiado a `Branch::where('status', 1)->get()` |
| 2 | `empresaUpdate()` — sin null check: si no existía ningún registro en `empresa_details`, `EmpresaDetail::first()` retornaba null y el acceso a propiedades crasheaba con TypeError | `AdminController.php` | Agregado `if(!is_object($empresa)){ $empresa = new EmpresaDetail(); }` |
| 3 | `empresaUpdate()` — sin validación: `name`, `rfc` y `address` se guardaban sin ninguna regla de validación | `AdminController.php` | Agregada validación `required` para los tres campos |
| 4 | `show.blade.php` — `<title>Sucursal</title>` título incorrecto en pestaña del navegador | `Admin/empresa/show.blade.php` | Cambiado a `<title>Empresa</title>` |
| 5 | `show.blade.php` — `Auth::User()->hasRole(['root'])` pasaba un array a `hasRole()`, que solo acepta string; generaba error SQL al intentar `WHERE name = ['root']` | `Admin/empresa/show.blade.php` | Cambiado a `hasAnyRole(['root'])` |
| 6 | `show.blade.php` — `$empresa->razon_social ?? ''` sin null-safe; en PHP 8 el acceso a propiedad en objeto null lanza TypeError antes de que `??` pueda actuar | `Admin/empresa/show.blade.php` | Cambiados a `$empresa?->campo ?? ''` todos los campos del formulario |

---

## 14. Módulo de Permisos

**Controlador:** `app/Http/Controllers/Admin/PermissionController.php`  
**Vista principal:** `resources/views/Admin/permissions/index.blade.php`  
**Modal:** `resources/views/Admin/permissions/_modal.blade.php`  
**Modelo:** `app/Models/Permission.php`  
**Rutas:** `routes/admin.php` bajo prefijo `/permissions`  
**Permiso de acceso:** `permission:roles` (mismo grupo que el módulo de Roles)

### 14.1 Rutas

| Método | URI | Método controlador | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/permissions` | `index()` | Listado de permisos |
| POST | `/permissions-store` | `store(Request)` | Crear permiso |
| POST | `/permissions-update` | `update(Request)` | Actualizar permiso |
| GET | `/permissions-desctroy/{id}` | `destroy($id)` | Eliminar permiso |

> **Nota:** La URI de destroy tiene un typo (`desctroy`) pero no afecta el funcionamiento porque las rutas se resuelven por nombre (`permission.destroy`).

### 14.2 Flujo principal

```
GET /permissions
    │
    ├─ Permission::orderBy('module')->orderBy('description')->get()
    ├─ Module::where('status', 1)->get()
    └─ Retorna vista con: permissions, modules

POST /permissions-store
    │
    ├─ Valida: module (required), action (required)
    ├─ Verifica duplicado: mismo module + action → error "El permiso ya existe"
    └─ Crea Permission con module, submodule, action, description

POST /permissions-update
    │
    ├─ Valida: module (required), action (required)
    ├─ Null check: Permission::find($request->id) → error si no existe
    └─ Actualiza module, submodule, action, description

GET /permissions-desctroy/{id}
    │
    ├─ Null check: Permission::find($id) → error si no existe
    └─ $permission->delete()
```

### 14.3 Modelo `Permission`

**Tabla:** `permissions`

| Campo | Descripción |
|-------|-------------|
| `module` | Nombre del módulo (ej. `ventas`, `inventarios`) |
| `submodule` | Submódulo (siempre `punto_venta` desde la UI) |
| `action` | Acción: `create`, `update`, `destroy`, `show`, `auth` |
| `description` | Texto descriptivo del permiso |

### 14.4 Cómo se usan los permisos

Los permisos se asignan a roles en `roles_permissions` (tabla pivote). El sistema los evalúa con:

```php
$user->hasPermissionThroughModule('module', 'submodule', 'action')
```

El middleware `CheckPermission` intercepta las rutas y verifica los permisos antes de dejar pasar la request. Ver [Sección 11](#11-módulo-de-roles-y-permisos) para el flujo completo.

### 14.5 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `update()` — sin null check: `Permission::find($request->id)` podía retornar null; el crash quedaba silenciado por un `try/catch` genérico que ocultaba el error real | `PermissionController.php` | Null check explícito + redirect con error "Permiso no encontrado" |
| 2 | `update()` — sin validación: `module` y `action` se guardaban sin ninguna regla, a diferencia de `store()` que sí las tenía | `PermissionController.php` | Agregada validación `required\|string` para ambos campos |
| 3 | `destroy()` — `Permission::find($id)->delete()` encadenado: si el ID no existía, `find()` devolvía null y `->delete()` crasheaba; el `try/catch` lo ocultaba | `PermissionController.php` | Separado en null check + `$permission->delete()` |
| 4 | `_modal.blade.php` — `</label>` huérfano después del campo Acción (cierre sin apertura); error HTML que rompía la estructura del formulario | `Admin/permissions/_modal.blade.php` | Eliminada la etiqueta de cierre duplicada |
| 5 | `index.blade.php` — botones crear, editar y eliminar visibles para todos los usuarios sin importar sus permisos; inconsistente con el resto de módulos | `Admin/permissions/index.blade.php` | Envueltos en `@if(hasPermissionThroughModule('roles','punto_venta','create\|update\|destroy'))` |

---

## 15. Módulo de Importación

**Controlador:** `app/Http/Controllers/Admin/RootController.php`  
**Vista principal:** `resources/views/Admin/root/index.blade.php`  
**Partial reutilizable:** `resources/views/Admin/root/importacion_DBExt_DBLocal.blade.php`  
**Modal:** `resources/views/Admin/root/_modal.blade.php`  
**Rutas:** `routes/root.php` (grupo `web` + `auth`, cargado desde `RouteServiceProvider`)  
**Permiso de acceso:** `permission:empresa,punto_venta,auth`

### 15.1 Rutas

| Método | URI | Método controlador | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/import-data` | `RootController@index` | Vista principal |
| GET | `/import-datas/{table}` | `setDataDB($table)` | Importa tabla de QuickBase → DB externa |
| GET | `/import-data-local/{model}/{table}` | `setDataDBLocal($model, $table)` | Importa de DB externa → SQLite local |
| POST | `/import-conf-local` | `setConfDBLocal(Request)` | Configuración inicial (solo TCI_DEV) |
| POST | `/reset-app` | `resetDatabase()` | `migrate:refresh --force` (borra todo) |
| GET | `/logs` | `viewLogs()` | Visualiza logs de Laravel |
| GET | `/clear-logs` | `clearLogs()` | Vacía el archivo de log |

### 15.2 Flujo de importación

```
QB → DB Externa (setDataDB)
    │
    ├─ existDataDb($table) → verifica si ya hay datos en DB externa
    ├─ getQuickBase($table) → descarga todos los registros de QuickBase
    ├─ Si ya hay datos → addNewDataDB() filtra solo los nuevos (deduplicación)
    ├─ inputsDb($table, $data) → mapea campos QB → columnas locales
    └─ saveDb($table, $data_db) → inserta en DB externa

DB Externa → SQLite local (setDataDBLocal)
    │
    ├─ $model::first() → verifica si ya hay datos locales
    ├─ consultDb($table, '') → descarga todos los registros de DB externa
    ├─ Si ya hay datos → addNewDataDBLocal() filtra solo los nuevos
    └─ $model::create((array)$item) → inserta cada registro en SQLite
```

### 15.3 Tablas soportadas

| Tabla | QB → DB Externa | DB Externa → Local |
|-------|----------------|--------------------|
| `users` | ✓ | ✓ |
| `brands` | ✓ | ✓ |
| `products` | ✓ | ✓ |
| `empresa_details` | ✓ (sin deduplicación*) | ✓ |
| `drivers` | ✓ | ✓ |
| `payment_methods` | ✓ | ✓ |
| `unidades_sat` | ✓ | ✓ |
| `proveedores` | ✓ | ✓ |
| `branchs` | ✓ | ✓ |
| `roles` | — | ✓ |

> *`empresa_details` no tiene entrada en `keysTable()`, por lo que en una segunda importación QB → DB externa `addNewDataDB()` falla con variable indefinida. Primera importación funciona correctamente (no pasa por `addNewDataDB`).

### 15.4 Configuración inicial (`setConfDBLocal`)

Solo ejecutable por el usuario `TCI_DEV` con contraseña correcta y conexión a internet. Realiza en orden:

1. Importa roles desde DB externa (`setDataDBLocal('Role', 'roles')`)
2. Crea cliente "Publico General" si no existe
3. Asigna rol `root` al usuario `TCI_DEV`
4. Ejecuta `DatabaseSeeder`
5. Descarga logo (`URL_LOGO`) e instalador de SumatraPDF (`URL_PDF`) vía HTTP

### 15.5 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | `import.data`, `import.dataLocal`, `import.setConfDBLocal` — sin middleware de permiso; cualquier usuario autenticado podía importar y sobreescribir datos en la DB | `routes/root.php` | Agregado `->middleware('permission:empresa,punto_venta,auth')` a las tres rutas |
| 2 | `/logs` y `/clear-logs` — sin middleware; cualquier usuario autenticado podía leer errores del servidor o borrar el historial de logs | `routes/root.php` | Agregado `->middleware('permission:empresa,punto_venta,auth')` a ambas rutas |
| 3 | `viewLogs()` — usaba `dd('No hay logs registrados aún.')` cuando el archivo no existía; `dd()` en producción rompe el layout y mata el request sin respuesta HTTP limpia | `RootController.php` | Cambiado a `return view('logs', ['lines' => []])` |
| 4 | `setConfDBLocal()` — `$rol = Role::where('name','root')->first()` sin null check; si la importación de roles falla o la tabla está vacía, `$rol->id` lanza TypeError | `RootController.php` | Null check + redirect con error "El rol root no existe. Importa los roles primero." |
| 5 | `_modal.blade.php` — título del modal decía "Asignar Turno y Roles" (era un copy-paste del modal de usuarios) | `Admin/root/_modal.blade.php` | Cambiado a "Configuración Inicial" |
| 6 | `importacion_DBExt_DBLocal.blade.php` — `env('URL_DOWNLOAD')` directo en `href`; si la variable no está definida en `.env`, el enlace genera una URL vacía o rota | `Admin/root/importacion_DBExt_DBLocal.blade.php` | Envuelto en `@if(env('URL_DOWNLOAD'))` |

---

## 16. Módulo de Logs

**Vista:** `resources/views/logs.blade.php`  
**Controlador:** `app/Http/Controllers/Admin/RootController.php` (`viewLogs`, `clearLogs`)  
**Rutas:** `routes/root.php`  
**Permiso de acceso:** `permission:empresa,punto_venta,auth`

### 16.1 Rutas

| Método | URI | Método controlador | Descripción |
|--------|-----|--------------------|-------------|
| GET | `/logs` | `viewLogs()` | Muestra logs del sistema |
| GET | `/clear-logs` | `clearLogs()` | Vacía `storage/logs/laravel.log` |

### 16.2 Cómo funciona `viewLogs()`

Lee `storage/logs/laravel.log` línea por línea en orden inverso (más recientes primero). Las líneas de continuación (stack traces) se concatenan a la entrada anterior. Si el archivo no existe retorna la vista con array vacío.

La vista parsea cada línea con el regex:
```
/^\[(.*?)\] ([a-zA-Z\.]+): (.*)$/
```
Extrayendo: `[fecha]`, `canal.NIVEL` y `mensaje`. El nivel se colorea con clases CSS (`level-INFO`, `level-ERROR`, `level-WARNING`, `level-UNKNOWN`).

### 16.3 Bugs corregidos

| # | Descripción | Archivo | Fix |
|---|-------------|---------|-----|
| 1 | Botón "Limpiar logs" sin confirmación; un click accidental borraba permanentemente todo el historial de errores | `logs.blade.php` | Agregado `onclick="return confirm('...')"` |
| 2 | Columna Mensaje con `width:10%` — la columna de mayor contenido tenía el ancho más pequeño, haciendo el texto ilegible | `logs.blade.php` | Redistribuidos anchos: Fecha 15%, Nivel 10%, Mensaje 75% con `word-break:break-word` |
| 3 | Cuando el regex no hacía match (línea malformada), `$nivel` quedaba vacío y se aplicaba la clase CSS `level-` (inexistente), sin estilo visual | `logs.blade.php` | Fallback a `level-UNKNOWN` con color gris + clase CSS `level-UNKNOWN` agregada |
