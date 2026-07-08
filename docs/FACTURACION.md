# Sistema de Facturación CFDI 4.0 — Guía de Integración para Punto de Venta

**Stack del POS:** Laravel + NativePHP/Electron + Livewire  
**Ambiente facturador:** PHP 8.2 / Apache / Docker  
**PAC:** Dinvbox (SOAP/WSDL)  
**Vigente:** CFDI 4.0

---

## Tabla de contenidos

1. [Qué es este sistema](#1-qué-es-este-sistema)
2. [Arquitectura general](#2-arquitectura-general)
3. [Requisitos y dependencias](#3-requisitos-y-dependencias)
4. [Instalación y despliegue](#4-instalación-y-despliegue)
5. [Variables de entorno](#5-variables-de-entorno)
6. [APIs disponibles](#6-apis-disponibles)
7. [Flujo de timbrado](#7-flujo-de-timbrado)
8. [Flujo de cancelación](#8-flujo-de-cancelación)
9. [Consulta de estado SAT](#9-consulta-de-estado-sat)
10. [PAC: Dinvbox](#10-pac-dinvbox)
11. [Estructura del JSON CFDI (layout)](#11-estructura-del-json-cfdi-layout)
12. [Integración desde Laravel/Livewire](#12-integración-desde-laravellivewire)
13. [Motivos de cancelación](#13-motivos-de-cancelación)
14. [Manejo de errores](#14-manejo-de-errores)
15. [Almacenamiento S3](#15-almacenamiento-s3)
16. [Ambiente DEMO vs Producción](#16-ambiente-demo-vs-producción)
17. [Consideraciones NativePHP/Electron](#17-consideraciones-nativephpelectron)

---

## 1. Qué es este sistema

Este es un **servicio de facturación electrónica CFDI 4.0** desplegado como microservicio HTTP independiente. Su función es recibir los datos de una venta, generar y timbrar el comprobante fiscal (XML) ante el SAT a través del PAC Dinvbox, y devolver las URLs del XML y PDF firmados almacenados en S3.

El POS **no timbra directamente**: delega 100% del proceso de timbrado a este servicio via HTTP.

### Qué hace el servicio:
- Recibe un JSON con la estructura CFDI 4.0
- Se comunica con Dinvbox vía SOAP para timbrar
- Sube el XML y PDF resultantes a AWS S3
- Devuelve el UUID del timbre, URLs de descarga y datos del comprobante
- Permite cancelar CFDIs (motivos 01–04)
- Permite consultar el estado vigente de un CFDI en el SAT

### Qué **no** hace el servicio:
- No gestiona catálogos de productos, clientes ni ventas
- No genera el JSON CFDI por sí solo (eso lo hace el POS)
- No tiene base de datos propia (actualmente usa Quickbase, pero esa parte es opcional/reemplazable)

---

## 2. Arquitectura general

```
┌─────────────────────────────────────────┐
│          Punto de Venta (POS)           │
│    Laravel + NativePHP + Livewire       │
│                                         │
│  1. Genera JSON con estructura CFDI     │
│  2. Hace POST al servicio de factura    │
│  3. Recibe UUID + URLs XML/PDF          │
│  4. Guarda en su BD local               │
└──────────────────┬──────────────────────┘
                   │  HTTP (JSON)
                   ▼
┌─────────────────────────────────────────┐
│     Servicio de Facturación (este)      │
│         PHP 8.2 / Apache                │
│         http://localhost:9000           │
│                                         │
│  POST /api/timbrado                     │
│  POST /api/cancelaciones                │
│  POST /api/consulta                     │
└──────────┬──────────────────────────────┘
           │  SOAP/WSDL
           ▼
┌──────────────────────┐     ┌────────────┐
│   PAC: Dinvbox       │     │  AWS S3    │
│  (timbre + SAT)      │     │ XML + PDF  │
└──────────────────────┘     └────────────┘
```

El servicio corre en Docker en el mismo equipo donde está instalado el POS (localhost), o en un servidor dedicado.

---

## 3. Requisitos y dependencias

### Para correr el servicio:
- Docker Engine (Desktop o CLI)
- Docker Compose v2+
- Puerto `9000` disponible en la máquina

### El servicio incluye internamente:
- PHP 8.2 con extensiones: `soap`, `zip`, `mbstring`, `xml`, `openssl`
- Apache con `mod_rewrite`
- Sin base de datos propia

### Credenciales externas necesarias:
| Servicio | Para qué |
|----------|----------|
| **Dinvbox** | PAC que timbra ante el SAT (RFC, usuario, contraseña) |
| **AWS S3** | Almacenar XML y PDF generados |
| **CSD (Certificado SAT)** | El `.cer` y `.key` del emisor (manejado por Dinvbox, no por este código) |

> **Importante:** Los CSD del emisor se registran en el panel de Dinvbox directamente. Este código no los gestiona.

---

## 4. Instalación y despliegue

```bash
# 1. Clonar o copiar el proyecto
git clone <repo> facturacion
cd facturacion

# 2. Configurar variables de entorno
cp .env_example .env
# Editar .env con las credenciales reales (ver sección 5)

# 3. Construir la imagen Docker
docker compose build

# 4. Levantar el servicio
docker compose up -d

# 5. Verificar que está corriendo
curl http://localhost:9000/api/config.js
```

El servicio queda disponible en `http://localhost:9000`.

### docker-compose.yaml:
```yaml
services:
  php-apache:
    image: server-facturacion
    build: .
    container_name: php-soap-server
    ports:
      - "9000:80"
    volumes:
      - ./facturacion32ar:/var/www/html
    env_file:
      - .env
    restart: unless-stopped
```

El volumen `./facturacion32ar:/var/www/html` permite modificar el código sin reconstruir la imagen.

---

## 5. Variables de entorno

Todas se configuran en el archivo `.env` en la raíz del proyecto.

### Dinvbox — PAC

```env
# Ambiente DEMO (pruebas con RFC genérico del SAT)
RFC_DEMO=EKU9003173C9
USUARIO_DEMO=UsuarioPruebasWS
PASSWORD_DEMO=b9ec2afa3361a59af4b4d102d3f704eabdf097d4
NOMBRE_DEMO=ESCUELA KEMPER URGATE
DOMICILIO_FISCAL=26015
REGIMEN_FISCAL=601

# Ambiente PRODUCCIÓN (datos reales del emisor)
RFC_PRODUCCION=XAXX010101000      # RFC real del emisor
USUARIO_PRODUCCION=usuario_real   # Usuario Dinvbox
PASSWORD_PRODUCCION=hash_real     # Contraseña SHA1 del Dinvbox
```

### AWS S3

```env
AWS_ACCESS_KEY=TU_ACCESS_KEY
AWS_SECRET_KEY=TU_SECRET_KEY
AWS_REGION=us-east-2
AWS_BUCKET=tu-bucket
AWS_FOLDER=facturacion
```

### Quickbase (opcional — solo si se usa Quickbase como BD)

```env
QB_REALM=tu-dominio.quickbase.com
QB_TOKEN=tu_token
QB_TABLE_ID=id_tabla
QB_USER_TOKEN=QB-USER-TOKEN tu_token
```

> Si el POS usa su propia base de datos (MySQL/SQLite de Laravel), la integración con Quickbase puede ignorarse. Ver sección 12 para la integración directa.

---

## 6. APIs disponibles

Base URL: `http://localhost:9000` (o la URL del servidor en producción)

Todos los endpoints aceptan y devuelven `Content-Type: application/json`.

---

### `POST /api/timbrado`

Timbra un CFDI 4.0 ante el SAT.

**Request body:**
```json
{
  "txt": "base64_del_layout_CFDI",
  "demo": "demo",
  "idFactura": "FACTURA-001",
  "record": 12345,
  "tipo": "I",
  "concepto": "Venta en punto de venta",
  "host": "tu-dominio.quickbase.com",
  "idTablaQB": "tabla_id",
  "idTablaQBLog": "tabla_log_id",
  "clist": "3.6.7.8",
  "relacionUUID": ""
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `txt` | string (base64) | ✅ | Layout CFDI 4.0 en base64. Puede ser XML o JSON. |
| `demo` | string | ❌ | Enviar `"demo"` para usar Sandbox de Dinvbox. Omitir para producción. |
| `idFactura` | string | ✅ | Identificador interno de la factura (folio). |
| `record` | number | ✅ | ID de registro (puede ser el ID del POS). |
| `tipo` | string | ✅ | Tipo de comprobante: `"I"` (Ingreso), `"E"` (Egreso), `"T"` (Traslado). |
| `concepto` | string | ✅ | Descripción del concepto principal. |
| `relacionUUID` | string | ❌ | UUID de la factura original (para motivo 01, sustitución). |
| `host`, `idTablaQB`, `idTablaQBLog`, `clist` | string | ❌ | Solo necesarios si se usa Quickbase. Pueden enviarse vacíos si no. |

**Response exitoso (200):**
```json
{
  "success": true,
  "uuid": "BB1C1D05-BB5C-485B-84B3-851D57795312",
  "xml": "base64_del_xml_timbrado",
  "pdf": "base64_del_pdf",
  "xml_url": "https://bucket.s3.amazonaws.com/facturacion/BB1C1D05.xml",
  "pdf_url": "https://bucket.s3.amazonaws.com/facturacion/BB1C1D05.pdf",
  "record": 12345
}
```

**Response error (200 con success=false):**
```json
{
  "success": false,
  "error_code": "CFDI40124",
  "error_message": "El RFC del receptor no existe en el SAT"
}
```

---

### `POST /api/cancelaciones`

Cancela un CFDI vigente.

**Request body:**
```json
{
  "uuid": "BB1C1D05-BB5C-485B-84B3-851D57795312",
  "motivo": "02",
  "foliosust": "",
  "demo": "demo",
  "record": 12345,
  "campo": 42,
  "bd": "tabla_id"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `uuid` | string | ✅ | UUID del CFDI a cancelar. |
| `motivo` | string | ✅ | Motivo de cancelación: `"01"`, `"02"`, `"03"`, `"04"`. Ver sección 13. |
| `foliosust` | string | ❌ | UUID de la factura sustituta. Solo requerido si `motivo = "01"`. |
| `demo` | string | ❌ | `"demo"` para sandbox. |
| `record`, `campo`, `bd` | mixed | ❌ | Solo para Quickbase. Pueden omitirse. |

**Response exitoso:**
```json
{
  "success": true,
  "uuid": "BB1C1D05-BB5C-485B-84B3-851D57795312",
  "mensaje": "Cancelación exitosa"
}
```

**Response error:**
```json
{
  "success": false,
  "error_code": "CAF40001",
  "error_message": "El CFDI ya fue cancelado anteriormente"
}
```

---

### `POST /api/consulta`

Consulta el estado actual de un CFDI en el SAT vía Dinvbox.

**Request body:**
```json
{
  "uuid": "BB1C1D05-BB5C-485B-84B3-851D57795312",
  "rfcReceptor": "LOPS810406UH4",
  "total": "1500.00",
  "demo": "demo"
}
```

**Response:**
```json
{
  "success": true,
  "estado": "Vigente",
  "esCancelable": "Sí",
  "estatusCancelacion": "",
  "estatusDinvbox": "Vigente",
  "httpCode": "200"
}
```

| Campo respuesta | Valores posibles |
|-----------------|-----------------|
| `estado` | `"Vigente"`, `"Cancelado"`, `"No encontrado"` |
| `esCancelable` | `"Sí"`, `"No"` |
| `estatusCancelacion` | `""`, `"En proceso"`, `"Solicitada"`, `"Plazo vencido"` |

---

## 7. Flujo de timbrado

### Qué ocurre internamente cuando el POS llama a `POST /api/timbrado`:

```
POS envía JSON
    │
    ▼
api/index.php → timbrado.php
    │
    ├─ Decodifica base64 del campo "txt"
    ├─ Si "demo" = true: reemplaza RFC/Nombre por los del RFC demo del SAT
    │
    ▼
FacturacionModerna.php::timbrar()
    │
    ├─ Abre cliente SOAP con WSDL de Dinvbox
    ├─ Llama requestTimbrarCFDI() con el XML del CFDI
    ├─ Extrae UUID del Timbre Fiscal Digital (TFD)
    ├─ Obtiene XML timbrado y PDF en base64
    │
    ▼
filesaverapi.php
    │
    ├─ Sube XML a S3: s3://bucket/facturacion/{UUID}.xml
    ├─ Sube PDF a S3: s3://bucket/facturacion/{UUID}.pdf
    ├─ Retorna URLs públicas
    │
    ▼
(Opcional) updateqb.php
    │
    └─ Actualiza registro en Quickbase con UUID + URLs
    │
    ▼
Respuesta al POS: { success, uuid, xml_url, pdf_url, ... }
```

---

## 8. Flujo de cancelación

```
POS envía uuid + motivo
    │
    ▼
cancelaciones.php
    │
    ├─ Valida motivo (01-04)
    ├─ Si motivo=01: verifica que foliosust no esté vacío
    │
    ▼
FacturacionModerna.php::cancelar()
    │
    ├─ Abre cliente SOAP Dinvbox
    ├─ Llama requestCancelarCFDI()
    ├─ Dinvbox envía solicitud al SAT
    │
    ▼
(Opcional) updateqb.php
    │
    └─ Actualiza estado en Quickbase a "Cancelada"
    │
    ▼
Respuesta al POS: { success, uuid, mensaje }
```

> **Importante:** La cancelación ante el SAT puede no ser inmediata. Si el receptor ya aceptó el CFDI, el SAT da 72 horas para que el receptor acepte o rechace la cancelación. Usar `POST /api/consulta` para verificar el estado después.

---

## 9. Consulta de estado SAT

Usar esta API para:
- Verificar si un CFDI está vigente antes de cancelar
- Confirmar que una cancelación fue procesada
- Auditar el estado de facturas emitidas

```php
// Desde Laravel:
$response = Http::post('http://localhost:9000/api/consulta', [
    'uuid'        => $factura->uuid,
    'rfcReceptor' => $factura->rfc_receptor,
    'total'       => $factura->total,
    'demo'        => app()->environment('production') ? '' : 'demo',
]);

$data = $response->json();
// $data['estado'] => "Vigente" | "Cancelado"
```

---

## 10. PAC: Dinvbox

El PAC (Proveedor Autorizado de Certificación) es **Dinvbox**. Se comunica via SOAP con dos endpoints:

| Ambiente | WSDL |
|----------|------|
| Demo/Sandbox | `https://wsdemo.dinvbox.mx/timbrado/wsdl` |
| Producción | `https://t1.dinvbox.mx/timbrado/wsdl` |

### Métodos SOAP que usa el sistema:

| Método | Descripción |
|--------|-------------|
| `requestTimbrarCFDI(options)` | Timbra el CFDI y retorna el XML con TFD |
| `requestCancelarCFDI(options)` | Envía solicitud de cancelación al SAT |
| `consultarEstatusCFDI(options)` | Consulta estado vigente en el SAT |

### Credenciales necesarias en Dinvbox:
- RFC del emisor
- Usuario (generado por Dinvbox al dar de alta)
- Contraseña (hash SHA1)
- Los CSD (`.cer` y `.key` del emisor) se suben directamente en el panel de Dinvbox

---

## 11. Estructura del JSON CFDI (layout)

El campo `txt` del endpoint de timbrado es el **layout CFDI 4.0 codificado en base64**.

### Qué es el layout:

Es el XML del comprobante antes de ser timbrado. Debe seguir exactamente la estructura XSD del SAT para CFDI 4.0. Ejemplo de estructura mínima:

```xml
<?xml version="1.0" encoding="utf-8"?>
<cfdi:Comprobante
  xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd"
  Version="4.0"
  Serie="A"
  Folio="1001"
  Fecha="2026-06-18T10:00:00"
  FormaPago="01"
  NoCertificado=""
  SubTotal="1293.10"
  Descuento="0"
  Moneda="MXN"
  Total="1500.00"
  TipoDeComprobante="I"
  Exportacion="01"
  MetodoPago="PUE"
  LugarExpedicion="26015">

  <cfdi:Emisor
    Rfc="XAXX010101000"
    Nombre="NOMBRE DEL EMISOR SA DE CV"
    RegimenFiscal="601"/>

  <cfdi:Receptor
    Rfc="LOPS810406UH4"
    Nombre="NOMBRE DEL RECEPTOR"
    DomicilioFiscalReceptor="01000"
    RegimenFiscalReceptor="612"
    UsoCFDI="G01"/>

  <cfdi:Conceptos>
    <cfdi:Concepto
      ClaveProdServ="01010101"
      Cantidad="1"
      ClaveUnidad="H87"
      Unidad="Pieza"
      Descripcion="Producto de prueba"
      ValorUnitario="1293.10"
      Importe="1293.10"
      ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado
            Base="1293.10"
            Impuesto="002"
            TipoFactor="Tasa"
            TasaOCuota="0.160000"
            Importe="206.90"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>

  <cfdi:Impuestos TotalImpuestosTrasladados="206.90">
    <cfdi:Traslados>
      <cfdi:Traslado
        Base="1293.10"
        Impuesto="002"
        TipoFactor="Tasa"
        TasaOCuota="0.160000"
        Importe="206.90"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>
```

### Cómo generar el base64 desde Laravel:

```php
$xmlLayout = view('cfdi.template', ['venta' => $venta])->render();
$base64 = base64_encode($xmlLayout);
```

> **Recomendación:** Usar una librería PHP para generar y validar el XML CFDI 4.0 antes de enviarlo. El SAT publica los XSD en: `http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd`

---

## 12. Integración desde Laravel/Livewire

### Servicio de facturación en Laravel

Crear un servicio que encapsule las llamadas al microservicio:

```php
// app/Services/FacturacionService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacturacionService
{
    private string $baseUrl;
    private bool $demo;

    public function __construct()
    {
        $this->baseUrl = config('facturacion.url', 'http://localhost:9000');
        $this->demo    = !app()->environment('production');
    }

    public function timbrar(string $xmlLayout, array $metadata): array
    {
        $response = Http::timeout(60)->post("{$this->baseUrl}/api/timbrado", [
            'txt'       => base64_encode($xmlLayout),
            'demo'      => $this->demo ? 'demo' : '',
            'idFactura' => $metadata['folio'],
            'record'    => $metadata['id'],
            'tipo'      => $metadata['tipo'] ?? 'I',
            'concepto'  => $metadata['concepto'],
            'host'      => '',
            'idTablaQB' => '',
            'idTablaQBLog' => '',
            'clist'     => '',
        ]);

        return $response->json();
    }

    public function cancelar(string $uuid, string $motivo, string $folioSust = ''): array
    {
        $response = Http::timeout(30)->post("{$this->baseUrl}/api/cancelaciones", [
            'uuid'      => $uuid,
            'motivo'    => $motivo,
            'foliosust' => $folioSust,
            'demo'      => $this->demo ? 'demo' : '',
            'record'    => 0,
            'campo'     => 0,
            'bd'        => '',
        ]);

        return $response->json();
    }

    public function consultarEstado(string $uuid, string $rfcReceptor, string $total): array
    {
        $response = Http::timeout(20)->post("{$this->baseUrl}/api/consulta", [
            'uuid'        => $uuid,
            'rfcReceptor' => $rfcReceptor,
            'total'       => $total,
            'demo'        => $this->demo ? 'demo' : '',
        ]);

        return $response->json();
    }
}
```

### Configuración en `config/facturacion.php`:

```php
return [
    'url' => env('FACTURACION_URL', 'http://localhost:9000'),
];
```

### `.env` del POS:

```env
FACTURACION_URL=http://localhost:9000
```

### Componente Livewire para timbrar:

```php
// app/Livewire/FacturarVenta.php

namespace App\Livewire;

use App\Services\FacturacionService;
use Livewire\Component;

class FacturarVenta extends Component
{
    public $ventaId;
    public $uuid = '';
    public $xmlUrl = '';
    public $pdfUrl = '';
    public $error = '';
    public bool $procesando = false;
    public bool $timbrada = false;

    public function timbrar(): void
    {
        $this->procesando = true;
        $this->error = '';

        $venta = Venta::with('cliente', 'productos')->findOrFail($this->ventaId);

        // 1. Generar el XML CFDI desde plantilla Blade
        $xml = view('cfdi.ingreso', ['venta' => $venta])->render();

        // 2. Llamar al servicio de facturación
        $servicio = app(FacturacionService::class);
        $resultado = $servicio->timbrar($xml, [
            'folio'    => $venta->folio,
            'id'       => $venta->id,
            'tipo'     => 'I',
            'concepto' => $venta->concepto_principal,
        ]);

        // 3. Procesar respuesta
        if ($resultado['success']) {
            $venta->update([
                'uuid'    => $resultado['uuid'],
                'xml_url' => $resultado['xml_url'],
                'pdf_url' => $resultado['pdf_url'],
                'estado'  => 'timbrada',
            ]);
            $this->uuid   = $resultado['uuid'];
            $this->xmlUrl = $resultado['xml_url'];
            $this->pdfUrl = $resultado['pdf_url'];
            $this->timbrada = true;
        } else {
            $this->error = "[{$resultado['error_code']}] {$resultado['error_message']}";
        }

        $this->procesando = false;
    }

    public function render()
    {
        return view('livewire.facturar-venta');
    }
}
```

### Tabla de ventas sugerida (migración):

```php
Schema::table('ventas', function (Blueprint $table) {
    $table->string('uuid', 40)->nullable()->after('folio');
    $table->string('xml_url')->nullable();
    $table->string('pdf_url')->nullable();
    $table->enum('estado_cfdi', ['pendiente', 'timbrada', 'cancelada'])->default('pendiente');
    $table->string('error_cfdi')->nullable();
    $table->timestamp('timbrada_at')->nullable();
    $table->timestamp('cancelada_at')->nullable();
});
```

---

## 13. Motivos de cancelación

El SAT define 4 motivos válidos para cancelar un CFDI:

| Motivo | Clave | Descripción | Requiere `foliosust` |
|--------|-------|-------------|---------------------|
| Errores con relación | `01` | Se emitió con error y se reemplaza por otra factura | ✅ Sí (UUID de la factura sustituta) |
| Errores sin relación | `02` | Se emitió con error pero no se sustituye | ❌ No |
| No se realizó la operación | `03` | La venta/operación no se concretó | ❌ No |
| Operación nominativa | `04` | Relacionada con una factura global (ticket) | ❌ No |

### Flujo recomendado para el POS:

```
Devolución de venta → Motivo 03 (no se realizó)
Error en RFC/datos  → Motivo 02 (sin relación) + Re-timbrar nueva
Sustitución por     → Motivo 01 (con foliosust = UUID nueva factura)
  corrección
Factura global POS  → Motivo 04
```

---

## 14. Manejo de errores

El servicio siempre responde HTTP 200, pero con `success: false` cuando hay error. Revisar `error_code` para manejarlos.

### Errores comunes de Dinvbox/SAT:

| Código | Descripción | Solución |
|--------|-------------|----------|
| `CFDI40124` | RFC receptor no existe en el SAT | Verificar RFC con el cliente |
| `CFDI40125` | RFC receptor inactivo | El cliente debe regularizarse ante el SAT |
| `CFDI40126` | Nombre receptor no coincide con SAT | Usar razón social exacta del SAT |
| `CFDI40127` | Régimen fiscal receptor incorrecto | Verificar régimen con el cliente |
| `CFDI40128` | Código postal receptor incorrecto | Verificar CP con el cliente |
| `CAF40001` | CFDI ya cancelado | No requiere acción |
| `CAF40003` | CFDI no cancelable | El receptor ya rechazó la cancelación |

### Recomendación para el POS:

```php
if (!$resultado['success']) {
    // Guardar el error para mostrar al cajero
    $venta->update([
        'error_cfdi'  => $resultado['error_code'],
        'estado_cfdi' => 'error',
    ]);

    // Notificar al usuario con mensaje amigable
    $mensajesAmigables = [
        'CFDI40124' => 'El RFC del cliente no está registrado en el SAT. Verificar datos.',
        'CFDI40126' => 'El nombre del cliente no coincide con el registro del SAT.',
        // ...
    ];
}
```

---

## 15. Almacenamiento S3

El servicio sube automáticamente los archivos generados a AWS S3.

### URLs resultantes:
```
https://{bucket}.s3.{region}.amazonaws.com/{folder}/{UUID}.xml
https://{bucket}.s3.{region}.amazonaws.com/{folder}/{UUID}.pdf
```

Con la configuración actual de ejemplo:
```
https://cyllid.s3.us-east-2.amazonaws.com/facturacion/BB1C1D05-BB5C-485B-84B3-851D57795312.xml
https://cyllid.s3.us-east-2.amazonaws.com/facturacion/BB1C1D05-BB5C-485B-84B3-851D57795312.pdf
```

Las URLs son **públicas** (sin autenticación). El POS puede:
- Guardar las URLs en su base de datos
- Mostrarlas directamente al cliente para descarga
- Generar un QR con la URL del PDF para el ticket de caja

---

## 16. Ambiente DEMO vs Producción

### DEMO (Sandbox)
- Se usa el RFC genérico del SAT: `EKU9003173C9` (Escuela Kemper Urgate)
- Los timbres son válidos técnicamente pero **no tienen valor fiscal**
- No se cobra por timbres en demo
- WSDL: `https://wsdemo.dinvbox.mx/timbrado/wsdl`

### Cómo seleccionar el ambiente:

**Desde el POS:** enviar `"demo": "demo"` en el JSON del request para sandbox, o `"demo": ""` para producción.

**Desde Laravel:**
```php
'demo' => app()->environment('production') ? '' : 'demo',
```

> El código del servicio detecta si viene el string `"demo"` y automáticamente redirige al WSDL de sandbox y reemplaza los datos del emisor por los del RFC de prueba del SAT.

---

## 17. Consideraciones NativePHP/Electron

Al correr el POS como aplicación de escritorio con NativePHP/Electron, tomar en cuenta:

### El servicio de facturación puede correr de dos formas:

**Opción A — Servicio local (recomendada para POS offline):**
- El Docker corre en la misma máquina del POS
- El POS llama a `http://localhost:9000`
- Requiere Docker instalado en la máquina del cajero
- Funciona sin internet (excepto para timbrar, que sí requiere conexión al PAC)

**Opción B — Servicio remoto (recomendada para múltiples terminales):**
- El Docker corre en un servidor
- El POS llama a `https://servidor-facturacion.tuempresa.com`
- No requiere Docker local
- Requiere internet siempre

### Configuración en NativePHP:

```php
// config/facturacion.php
return [
    'url' => env('FACTURACION_URL', 'http://localhost:9000'),
];
```

```env
# .env del POS en producción local
FACTURACION_URL=http://localhost:9000

# .env del POS con servidor remoto
FACTURACION_URL=https://facturacion.miempresa.com
```

### Arranque automático del Docker con NativePHP:

Si se usa la opción A, se puede arrancar el contenedor al iniciar la app:

```php
// En AppServiceProvider o en el bootstrap de NativePHP
use Native\Laravel\Facades\Shell;

Shell::exec('docker compose -f /ruta/al/docker-compose.yaml up -d');
```

### CORS:

El servicio de facturación tiene headers CORS configurados para aceptar peticiones de cualquier origen. No requiere configuración adicional para Electron.

### Timeout:

El timbrado puede tardar hasta 30 segundos en condiciones lentas. Configurar el timeout de `Http::` de Laravel en mínimo 60 segundos:

```php
Http::timeout(60)->post(...)
```

---

## Resumen rápido — Checklist de integración

- [ ] Docker instalado en la máquina del POS (si se usa opción local)
- [ ] Archivo `.env` configurado con credenciales Dinvbox y AWS reales
- [ ] `docker compose up -d` corriendo en puerto 9000
- [ ] Plantilla Blade para generar XML CFDI 4.0 según el tipo de comprobante del POS
- [ ] `FacturacionService.php` creado en Laravel
- [ ] `FACTURACION_URL` en `.env` del POS
- [ ] Campos `uuid`, `xml_url`, `pdf_url`, `estado_cfdi` en la tabla de ventas
- [ ] Componente Livewire para timbrar desde la pantalla de venta
- [ ] Manejo de errores con mensajes amigables al cajero
- [ ] Flujo de cancelación con selección de motivo
- [ ] Prueba en DEMO con RFC `EKU9003173C9` antes de producción
- [ ] Subir CSD del emisor en el panel de Dinvbox antes de producción
