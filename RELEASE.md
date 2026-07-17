# Guía de Actualización — POSTCI

## Requisitos previos
- Node.js instalado
- Proyecto configurado y funcionando localmente
- Acceso al repositorio [TCI-Developers/punto_venta](https://github.com/TCI-Developers/punto_venta)

---

## Pasos para publicar una nueva versión

### 1. Realizar los cambios en el código
Haz los cambios necesarios (bugs, nuevas funciones, etc.) y asegúrate de que todo funcione correctamente en local.

### 2. Actualizar el número de versión
Edita el archivo `.env` y actualiza `NATIVEPHP_APP_VERSION`:

```env
NATIVEPHP_APP_VERSION=1.0.1
```

> Usa versionado semántico: `MAYOR.MENOR.PARCHE`
> - **PARCHE** (1.0.0 → 1.0.1): corrección de bugs
> - **MENOR** (1.0.0 → 1.1.0): nueva funcionalidad sin romper compatibilidad
> - **MAYOR** (1.0.0 → 2.0.0): cambios que rompen compatibilidad

### 3. Hacer commit y push de los cambios

```bash
git add .
git commit -m "v1.0.1 - descripción del cambio"
git push origin main
```

### 4. Generar el instalador de Windows

```bash
php artisan native:build win
```

Este proceso tarda varios minutos. Al terminar, se generan 3 archivos en la carpeta `dist/`:

| Archivo | Descripción |
|---|---|
| `POSTCI-1.0.1-setup.exe` | Instalador principal (≈119 MB) |
| `latest.yml` | Manifiesto para el auto-updater |
| `POSTCI-1.0.1-setup.exe.blockmap` | Mapa de bloques para actualizaciones delta |

> **Importante:** Los 3 archivos son necesarios. Sin `latest.yml` el auto-updater no detecta la nueva versión.

### 5. Crear el Release en GitHub

1. Ir a [github.com/TCI-Developers/punto_venta/releases/new](https://github.com/TCI-Developers/punto_venta/releases/new)
2. En **Tag**, escribir `v1.0.1` y seleccionar **"Create new tag: v1.0.1 on publish"**
3. En **Release title**, escribir `POSTCI`
4. Agregar notas del release en la descripción
5. Arrastrar los 3 archivos de `dist/` al área de assets
6. Verificar que **Release label** esté en **None** (no marcar Pre-release)
7. Clic en **Publish release**

### 6. Verificar el release

Confirmar que en la página del release aparezcan los 3 assets:
- `latest.yml`
- `POSTCI-1.0.1-setup.exe`
- `POSTCI-1.0.1-setup.exe.blockmap`

---

## Comportamiento del auto-updater

- Los usuarios con la app instalada recibirán una notificación de actualización automáticamente al abrir el POS.
- La actualización se descarga e instala en segundo plano.
- Las migraciones de base de datos se ejecutan automáticamente al iniciar la nueva versión (no se pierde información).

---

## Historial de versiones

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0.0 | 2026-07-15 | Versión inicial |
