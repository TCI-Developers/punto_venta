# Contexto del POS para el proyecto Matriz

**Documento para:** Desarrollador del proyecto Laravel Matriz (Hostinger)  
**Proyecto origen:** POSTCI — Punto de Venta  
**Stack POS:** Laravel 10 + Livewire 3 + NativePHP/Electron + SQLite local  
**DB externa actual:** MySQL en Hostinger `193.203.166.213` / base `u142565811_pdv`

---

## 1. Qué es el POS y cómo funciona

El POS es una **aplicación de escritorio** (`.exe`) generada con NativePHP/Electron. Cada sucursal tiene una instalación independiente en su máquina local. No requiere navegador ni servidor — todo Laravel, PHP y SQLite van empaquetados dentro del instalador.

```
┌─────────────────────────────────────────────────────────────────┐
│                    Máquina del cajero (local)                    │
│                                                                  │
│  ┌─────────────────────────────────┐   ┌──────────────────────┐ │
│  │   POS (NativePHP .exe)          │   │  Docker (opcional)   │ │
│  │   Laravel 10 + Livewire 3       │   │  Servicio factura    │ │
│  │   SQLite → nativephp.sqlite     │   │  localhost:9000      │ │
│  └─────────────┬───────────────────┘   └──────────────────────┘ │
└────────────────┼────────────────────────────────────────────────┘
                 │  HTTP (cuando hay internet)
          ┌──────┴────────────────────────────────────────────┐
          │                                                   │
          ▼                                                   ▼
┌─────────────────────┐                     ┌──────────────────────────┐
│  Quickbase (QB)     │  ← se va a quitar   │  MySQL Hostinger         │
│  Catálogos maestros │                     │  193.203.166.213         │
│  (productos, etc.)  │                     │  u142565811_pdv          │
└─────────────────────┘                     │  (ventas, devoluciones,  │
                                            │   compras, cxp)          │
                                            └──────────────────────────┘
```

**Futuro (cuando exista la Matriz):**  
QB desaparece. La Matriz en Hostinger centraliza todo — catálogos maestros, administración, reportes globales — y el POS se sincroniza con ella en lugar de con QB.

---

## 2. Flujo de trabajo del cajero

```
Login
  └─► Ingresar monto inicial de caja
        └─► Crear venta → agregar productos → cobrar
              └─► (opcional) Devolución de venta
              └─► (opcional) Crear compra a proveedor
              └─► (opcional) Facturar venta cobrada
        └─► Cierre de turno
              ├─► Contar billetes/monedas
              ├─► Sincronizar ventas, devoluciones y compras a MySQL Hostinger
              └─► Logout
```

---

## 3. Base de datos local (SQLite)

Archivo: `database/nativephp.sqlite`

### Tablas principales

| Tabla | Descripción |
|-------|-------------|
| `users` | Cajeros y administradores |
| `roles` / `permissions` | Sistema de permisos granular propio |
| `branchs` | Sucursales |
| `empresa_details` | Datos fiscales del emisor (RFC, régimen, CP, etc.) |
| `customers` | Clientes con datos fiscales (RFC, régimen, CP) |
| `products` | Catálogo de productos con existencia local |
| `parts_to_product` | Presentaciones de cada producto (precio, unidad SAT, cantidad despiezado) |
| `payment_methods` | Métodos de pago SAT (PUE / PPD) |
| `unidades_sat` | Catálogo de unidades SAT |
| `sales` | Ventas encabezado |
| `sales_detail` | Renglones de cada venta |
| `sales_detail_cant` | Cantidades por renglón (con descuentos individuales) |
| `devoluciones` | Devoluciones de ventas |
| `compras` | Órdenes de compra a proveedor |
| `detalles_compra` | Renglones de cada compra |
| `detalle_compra_entradas` | Recepciones parciales de compra |
| `cuentas_pagar` | CXP generadas al recibir compra |
| `boxes` | Cierres de turno con montos declarados y del sistema |
| `facturas` | CFDIs emitidos |
| `factura_sales` | Pivot: qué ventas incluye cada factura |

### Campos clave de `empresa_details`
```
razon_social, name, rfc, regimen_fiscal, codigo_postal, address, vigencia, path_logo, branch_id
```
> `regimen_fiscal` y `codigo_postal` se agregaron para facturación CFDI 4.0.

### Campos clave de `customers`
```
name, razon_social, rfc, postal_code, regimen_fiscal, status
```
> Ya contiene todos los datos fiscales necesarios para el receptor del CFDI.

### Status de ventas (`sales.status`)
| Valor | Significado |
|-------|-------------|
| `0` | Cancelada |
| `1` | Abierta / en proceso |
| `2` | Cobrada / cerrada |

### Status de compras (`compras.status`)
| Valor | Significado |
|-------|-------------|
| `1` | Pendiente |
| `2` | Autorizada |
| `3` | Solicitada |
| `4` | Recibida (aumenta stock) |
| `5` | Cerrada |

### Status de caja (`boxes.status`)
| Valor | Significado |
|-------|-------------|
| `0` | Turno abierto |
| `1` | Cerrado — cuadre correcto |
| `2` | Cerrado — con varianza |

---

## 4. Integración actual con Quickbase (a reemplazar)

QB actualmente sirve como **catálogo maestro de referencia**. El POS consulta QB al hacer la "Importación inicial" y descarga los datos a su SQLite local.

### Qué se importa desde QB → SQLite local

| Dato | Tabla QB (dbid) | Tabla local |
|------|----------------|-------------|
| Sucursales | `bqa4qy37m` | `branchs` |
| Marcas/Líneas | `brer52xt3` | `brands` |
| Productos | `bqa4qy4jd` | `products` + `parts_to_product` |
| Métodos de pago | `bqgubmjca` | `payment_methods` |
| Unidades SAT | `bqgt9zstu` | `unidades_sat` |
| Proveedores | `bqa4qy387` | `proveedores` |
| Empresa | `bqa4qy3xm` | `empresa_details` |
| Choferes | `bqa4qy3yt` | `drivers` |

**Código relevante:** `app/Http/Controllers/Controller.php` — método `getQuickBase()` y familia `getBrands()`, `getProducts()`, `getProveedores()`, etc.

### Cuándo ocurre la importación
Manualmente desde el módulo **Importación** (`/import-data`). Solo se importa si la tabla local está vacía. El admin o root inicia el proceso al configurar una instalación nueva.

---

## 5. Sincronización de datos POS → MySQL Hostinger (actual)

El POS envía ventas, devoluciones y compras a la MySQL de Hostinger a través de un **middleware PHP** alojado en:

```
https://tciconsultoria.com/lapequenita/punto_venta_conection_db/
```

Scripts del middleware:
| Endpoint | Operación |
|----------|-----------|
| `save_data.php` | INSERT en MySQL Hostinger |
| `update_data.php` | UPDATE en MySQL Hostinger |
| `get_data.php` | SELECT (verifica si ya existe el registro) |
| `get_data_paginate.php` | SELECT paginado |
| `exist_data.php` | Verifica si hay registros en una tabla |

El payload se envía como JSON codificado en base64 en el body del request.

**Código relevante:** `Controller.php` — métodos `db_externa()`, `saveDb()`, `updateDb()`, `consultDb()`.

### Cuándo se sincroniza
Al **cierre de turno** (`BoxController@store`) se llaman automáticamente:
- `getSalesDbExt()` → envía ventas del día del cajero
- `getDevolutionDBExt()` → envía devoluciones de los últimos 8 días
- `getComprasDbExt()` → envía compras de los últimos 8 días

### Qué datos se envían a la MySQL Hostinger

**Ventas** (tabla `sales`):
```
sale_id, date, folio, user (nombre string), branch_id, uuid,
payment_method_id, type_payment, amount_received, change_, 
sat_document_type, total_sale, coin, status, customer (nombre string),
created_at, updated_at,
details_json      ← array JSON de SaleDetail
detail_cant_json  ← array JSON de SaleDetailCant
```

**Devoluciones** (tabla `devoluciones`):
```
devolucion_id, sale_id, branch_id, user (nombre string), cantidad,
description, fecha_devolucion, total_descuentos, total_devolucion,
status, created_at, updated_at,
details_json       ← renglones devueltos (SaleDetail status=0)
details_cant_json  ← cantidades devueltas (SaleDetailCant status=0)
```

**Compras** (tabla `compras`):
```
compra_id, folio, branch_id, proveedor_id, user (nombre string),
programacion_entrega, fecha_recibido, plazo, fecha_vencimiento,
moneda, tipo, importe, impuesto_productos, descuentos,
subtotal, total, observaciones, status, created_at, updated_at,
details_json      ← renglones de compra (DetalleCompra)
details_cant_json ← entradas parciales (DetalleCompraEntrada)
```

**Cuentas por Pagar** (tabla `cuentas_pagar`):
```
compra_id, branch_id, date, fecha_vencimiento, subtotal,
impuestos, total, status,
compra_json          ← objeto Compra
compra_details_json  ← array DetalleCompra
```

---

## 6. Lo que la Matriz necesita reemplazar/proveer

Cuando QB se elimine y la Matriz esté en Hostinger, el POS necesitará dos cosas de la Matriz:

### A) APIs de importación (Matriz → POS)

El POS necesita un endpoint por cada catálogo para descargar los datos maestros al SQLite local. Actualmente estos vienen de QB.

```
GET /api/pos/branchs
GET /api/pos/products
GET /api/pos/brands
GET /api/pos/payment-methods
GET /api/pos/unidades-sat
GET /api/pos/proveedores
GET /api/pos/empresa-details
GET /api/pos/drivers
GET /api/pos/customers         ← (si se centraliza)
```

El POS ya tiene la estructura de `setProducs()`, `setProveedores()`, etc. en los modelos — solo cambia de dónde viene la data (QB → Matriz API).

### B) APIs de sincronización (POS → Matriz)

Reemplazar el middleware de `tciconsultoria.com`. La Matriz expone:

```
POST /api/pos/sales          ← recibe ventas al cierre de turno
POST /api/pos/devoluciones   ← recibe devoluciones
POST /api/pos/compras        ← recibe compras
POST /api/pos/cxp            ← recibe cuentas por pagar
GET  /api/pos/sales/check/{sale_id}       ← verifica si ya existe
GET  /api/pos/devoluciones/check/{id}     ← verifica si ya existe
GET  /api/pos/compras/check/{compra_id}   ← verifica si ya existe
```

El campo más importante de la respuesta del check es `{ "status": "success" }` cuando el registro ya existe — el POS lo usa para no duplicar envíos.

### C) Servicio de facturación

Actualmente el servicio de facturación corre en Docker local (`localhost:9000`). Cuando la Matriz esté lista, el POS solo necesita que cambien una variable de entorno:

```env
# .env del POS — de localhost a Hostinger
FACTURACION_URL=https://matriz.tudominio.com
FACTURACION_DEMO=   # vacío = producción
```

La Matriz debe exponer los mismos 3 endpoints que usa el POS:

```
POST /api/timbrado        ← timbra un CFDI 4.0
POST /api/cancelaciones   ← cancela por UUID + motivo
POST /api/consulta        ← consulta estado en el SAT
```

Ver `docs/FACTURACION.md` para el contrato completo de request/response.

---

## 7. Módulo de Facturación del POS

El POS tiene un módulo de facturación completo para emitir CFDI 4.0 tipo Ingreso (I).

### Archivos relevantes
| Archivo | Descripción |
|---------|-------------|
| `app/Http/Controllers/Admin/FacturaController.php` | Controller principal — genera XML, llama servicio |
| `app/Services/FacturacionService.php` | Encapsula las llamadas HTTP al servicio de facturación |
| `app/Models/Factura.php` | Modelo con relaciones y helpers de status |
| `config/facturacion.php` | Config URL + modo demo |
| `database/migrations/2026_06_17_100001_create_facturas_table.php` | Tabla `facturas` |
| `database/migrations/2026_06_17_100002_create_factura_sales_table.php` | Pivot `factura_sales` |

### Flujo de timbrado
1. Cajero abre una venta cobrada (status=2) → botón **Facturar**
2. Selecciona ventas a incluir + datos del receptor (cliente o Público General)
3. `FacturaController::store()` genera el XML CFDI 4.0 completo
4. Lo envía en base64 a `FACTURACION_URL/api/timbrado`
5. Guarda UUID, xml_url, pdf_url en tabla `facturas`

### Tabla `facturas`
```
id, uuid, folio_fiscal, serie, folio, tipo_comprobante (I/E/P),
customer_id (null=público general), branch_id, user_id,
subtotal, descuento, iva, total,
forma_pago, metodo_pago (PUE/PPD), uso_cfdi, moneda,
status (0=pendiente, 1=timbrada, 2=cancelada, 3=error),
xml, pdf_url, error_message, response_json
```

### Receptor "Público en General"
- RFC: `XAXX010101000`
- Nombre: `PUBLICO EN GENERAL`
- Régimen fiscal: `616`
- Código postal: `99999`
- Uso CFDI: `S01`

### Cancelación — motivos SAT
| Motivo | Descripción | Requiere `foliosust` |
|--------|-------------|---------------------|
| `01` | Con relación (sustituye a otra factura) | ✅ Sí |
| `02` | Sin relación | ❌ No |
| `03` | No se realizó la operación | ❌ No |
| `04` | Operación nominativa en factura global | ❌ No |

---

## 8. Sistema de permisos del POS

El POS tiene un sistema de permisos propio (no usa Laravel Gates/Policies). Cada permiso tiene 3 dimensiones:

```php
hasPermissionThroughModule('module', 'submodule', 'action')
// Ejemplo:
hasPermissionThroughModule('ventas', 'punto_venta', 'create')
```

Roles base: `root`, `admin`, `empleado`  
`hasRole($string)` — solo acepta string  
`hasAnyRole($array)` — acepta array

---

## 9. Variables de entorno del POS relevantes para la Matriz

```env
# Conexión a MySQL Hostinger (sync de datos)
DB_CONNECTION_EXTERNAL=mysql
DB_HOST_EXTERNO=193.203.166.213
DB_DATABASE_EXTERNO=u142565811_pdv
DB_USERNAME_EXTERNO=...
DB_PASSWORD_EXTERNO=...

# Quickbase (a reemplazar por la URL de la Matriz)
DOMINIO=tu-dominio-quickbase
USER_TOKEN=QB-USER-TOKEN ...

# Facturación (apuntar a la Matriz cuando esté lista)
FACTURACION_URL=http://localhost:9000
FACTURACION_DEMO=true
```

---

## 10. Resumen de lo que la Matriz debe construir

| Prioridad | Qué | Para qué |
|-----------|-----|----------|
| Alta | APIs de importación de catálogos | Reemplazar QB — el POS descarga productos, proveedores, etc. |
| Alta | APIs de sincronización de ventas/compras/devoluciones | Reemplazar middleware `tciconsultoria.com` |
| Alta | Servicio de facturación (`/api/timbrado`, `/api/cancelaciones`, `/api/consulta`) | Reemplazar Docker local — el POS ya está integrado, solo cambia la URL |
| Media | Panel de administración | Gestión de productos, precios, sucursales, usuarios |
| Media | Reportes globales | Consolidado de todas las sucursales |
| Baja | Autenticación del POS | Si se quiere centralizar login (hoy es local) |

---

## 11. Notas de implementación

- El POS detecta si hay internet antes de sincronizar: `Controller::hasInternetConnection()` — si no hay conexión, omite el sync silenciosamente.
- La sincronización de ventas/compras/devoluciones envía los **detalles como JSON embebido** (`details_json`, `detail_cant_json`) — si la Matriz los necesita normalizados, debe parsear esos campos.
- Los productos en el POS se referencian a través de `PartToProduct` (presentaciones), no directamente desde `Product`. Una venta no toca `products` directamente.
- El stock de productos se mueve en 3 momentos: venta (↓), devolución (↑), recepción de compra (↑).
- El folio de una venta es `R-{id}` (generado en `Sale::addFolio()`).

---

*Generado el 2026-06-18 — para dudas sobre el POS contactar al equipo de desarrollo.*
