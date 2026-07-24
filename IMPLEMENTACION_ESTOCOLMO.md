# Implementación sucursal Estocolmo — contexto y notas

Documento de referencia de todo lo revisado/corregido en el módulo de productos antes de
implementar POSTCI en la sucursal Estocolmo. Generado el 2026-07-21.

## 1. Qué se estaba resolviendo

Se va a instalar POSTCI en una PC Windows nueva de la sucursal Estocolmo. Te pasaron un Excel
con el catálogo de productos y existencias de esa sucursal
(`EXISTENCIAS ESTOCOLMO 20.07.26_8315.xls`) para cargarlo una vez instalada la app. Antes de
hacerlo se revisó el módulo de productos/import completo porque nunca se había probado con un
archivo de este tamaño en una máquina que no fuera Mac.

## 2. Cómo funciona el import de productos (lo importante para el día de la implementación)

- Pantalla: `product.showUploadExcel` → sube el Excel → `ProductController@uploadExcel`
  ([app/Http/Controllers/Admin/ProductController.php](app/Http/Controllers/Admin/ProductController.php)) → `App\Imports\ProductsImport`
  ([app/Imports/ProductsImport.php](app/Imports/ProductsImport.php)).
- **Columnas esperadas del Excel** (hoja de datos, fila 3 en adelante):
  - A = código de producto (`code_product`)
  - B/C/D = descripción/línea/marca (no se usan en el import)
  - E = existencia (stock nuevo)
  - F = "código relacionado" (agrupa presentaciones/equivalencias de un mismo producto)
  - G = código de barras (`code_bar`)
  - H = equivalencia (factor de despiece)
  - Columnas extra (I, J en el archivo de Estocolmo) se ignoran sin problema.

- **CRÍTICO — el import NO crea productos nuevos.** Solo actualiza stock/precios/presentaciones
  de productos que **ya existen** en la tabla `products` local, buscando por `code_product`
  (`if (!$product) continue;`). Si la PC de Estocolmo es una instalación nueva sin el catálogo de
  productos ya sincronizado, subir este Excel **no va a hacer nada** (todo sale como "sin
  coincidencia") y no habrá ningún error visible que lo delate a simple vista — por eso se agregó
  el contador de coincidencias (ver punto 3).
- La creación inicial del catálogo de productos para una sucursal nueva se hace por otra vía,
  separada del Excel: sincronización con el sistema externo "Quick"
  (`BranchController::getProducts2()` → `Product::setProducs()`), no con este archivo.

**Antes de subir el Excel en Estocolmo: confirmar que el catálogo de productos ya está
sincronizado en esa máquina (vía el flujo de Quick), si no la importación no actualizará nada.**

## 3. Fixes aplicados a `ProductsImport.php` / `ProductController.php`

Todos ya probados contra datos reales (ver sección 5). Commit `1c87dbb` en `main`
("Corrige rendimiento y confiabilidad del import de productos por Excel") — **excepto el punto
6, que sigue sin commitear** (ver sección 6).

1. **Reporte de coincidencias/fallos.** La clase ahora expone `$matched`, `$skipped`,
   `$skippedCodes`. El controller arma el mensaje flash con ambos números y, si hubo saltados,
   los loguea (hasta 200 códigos) con `Log::warning`. Antes siempre decía "Archivo procesado
   correctamente" así se hubiera aplicado el 100% o el 0%.
2. **Bug de parseo de stock.** Se limpiaba una variable `$stockStr` (separador de miles/coma
   decimal) pero el guardado final usaba el `$stock` crudo sin limpiar. Ahora se guarda
   `$stockValue` (la versión ya parseada) de forma consistente.
3. **`trim()` en el código de producto**, tanto al leer la columna A como al comparar contra la
   columna F en el agrupamiento de presentaciones. Evita no-matches silenciosos por espacios
   extra (confirmado en los archivos reales: el código `VAS16MUL` aparece con espacio en ambos
   Excels de prueba).
4. **Código muerto eliminado** (un `Product::find()` redundante y un bloque de debug vacío).
5. **Índice O(n) en vez de O(n²)** para agrupar presentaciones por "código relacionado" (columna
   F). Antes se re-escaneaban todas las filas por cada producto — con ~7,700 filas eso son
   decenas de millones de comparaciones. Benchmark sintético: la versión vieja tardaba **2.24s**
   solo en esa parte; la nueva, **0.001s** (~2,180x). Esto era lo más probable que causara que la
   importación se sintiera "colgada" en una PC más lenta que un Mac.
6. **Transacción única (`DB::transaction`)** envolviendo todo el proceso, en vez de un `save()`
   individual por producto (miles de commits sueltos). Reduce drásticamente el I/O a disco,
   relevante en Windows con antivirus escaneando el archivo SQLite en tiempo real.
7. **Restricción a la hoja de datos real** (`WithMultipleSheets` → `sheets(): [0 => $this]`). Sin
   esto, Maatwebsite Excel procesa las 3 hojas del archivo (incluyendo hojas vacías de miles de
   filas), lo cual desperdiciaba trabajo y además inflaba el contador de "sin coincidencia" con
   basura. Se agregó también un `continue` explícito si el código viene vacío, como resguardo.
8. **Detección real del formato de archivo** (`IOFactory::identify()` en vez de confiar en la
   extensión). Ver sección 4 — esto es lo que hacía fallar el archivo de Estocolmo.

## 4. Bug encontrado con el archivo real de Estocolmo (`.xls` que en realidad es `.xlsx`)

El archivo `EXISTENCIAS ESTOCOLMO 20.07.26_8315.xls` tiene extensión `.xls` pero su contenido
real es un `.xlsx` (formato ZIP moderno — se confirmó por los primeros bytes del archivo, firma
`PK..`, y con `PhpOffice\PhpSpreadsheet\IOFactory::identify()`, que lo detectó como `Xlsx`). El
sistema/ERP de origen etiqueta mal la extensión al exportar. Maatwebsite Excel elegía el lector
según la extensión (`.xls` → lector binario legacy "OLE"), lo cual fallaba con:

```
The filename ... is not recognised as an OLE file
```

**Fix:** en `ProductController::uploadExcel()` ahora se detecta el formato real del archivo con
`IOFactory::identify()` antes de llamar a `Excel::import()`, pasándolo explícitamente como cuarto
parámetro. Esto deja la importación a prueba de que otras sucursales manden archivos con la
extensión mal puesta (parece ser un problema sistemático del export del cliente, no exclusivo de
Estocolmo — el archivo de prueba anterior, `products.xlsx`, sí era un `.xlsx` real).

**⚠️ Este fix (punto 8 de la sección 3) todavía no está commiteado** — ver sección 6.

## 5. Pruebas realizadas (contra copia aislada de la BD, nunca la real)

Se armó un script que bootea Laravel apuntando a una **copia temporal** de
`database/nativephp.sqlite` (nunca la base real), fuerza la conexión sqlite (el `.env` de este
checkout tiene `DB_CONNECTION` duplicado, ver sección 7), corre `Excel::import()` con el import
real, y compara conteos antes/después.

**Con `products.xlsx`** (7,680 filas, mismo catálogo base):
- Antes: 4,177 productos, suma existencias 263,812.40, 5,329 presentaciones.
- Después: 4,138 coincidencias, 3,542 sin match, suma existencias 11,628.85, 5,410 presentaciones.
- Tiempo: 1.73s. Memoria pico: 114 MB. Sin errores.

**Con el archivo real de Estocolmo** (8,421 filas, tras el fix del punto 4):
- Antes: 4,177 productos, suma existencias 263,812.40, 5,329 presentaciones.
- Después: **4,177 de 4,177 coincidencias** (100% contra este catálogo de prueba), 4,244 sin
  match, suma existencias 9,456.61, 5,636 presentaciones (+307 nuevas).
- Tiempo: 1.72s. Memoria pico: 112 MB. Sin errores.

**Importante:** ese 100%/4,177 de match es contra la base de datos de desarrollo de este Mac, no
contra la máquina real de Estocolmo. No es garantía de que el catálogo de Estocolmo esté
sincronizado — solo prueba que el *mecanismo* del import funciona bien con este archivo.

### Calidad de datos observada en ambos Excels (mismo patrón en los dos)

- 1 fila de prueba/basura: "Producto prueba", código corrupto en notación científica
  (`1.0101010101e+027`). Se salta sola (no hace match), no requiere limpieza manual.
- 1 código duplicado: `VAS16MUL` (una vez con espacio inicial). Con el fix de `trim()` esto ya no
  causa problema.
- Stock negativo en algunas filas (15 en el primer archivo, 70 en el de Estocolmo). El import los
  clampa a 0 silenciosamente — probablemente correcto, pero si algo se ve raro en el stock post-
  import, esta es una causa posible.
- ~88% de las filas en ambos archivos tienen existencia 0.00 — parece ser normal para este tipo
  de export, no es señal de error.

## 6. Estado de git — IMPORTANTE

- Commit `1c87dbb` ("Corrige rendimiento y confiabilidad del import de productos por Excel") en
  `main`, ya pusheado — incluye los puntos 1-7 de la sección 3.
- **Pendiente de commit:** el fix de detección real de formato de archivo (punto 8 / sección 4,
  en `ProductController.php`, uso de `IOFactory::identify()`). Este cambio **sí quedó incluido en
  el `.exe` ya generado** (el build empaqueta el árbol de trabajo actual, no solo lo commiteado),
  pero si alguien vuelve a compilar desde un checkout limpio de git sin este cambio, el archivo
  de Estocolmo (y cualquier otro `.xls`-que-en-realidad-es-`.xlsx`) volverá a fallar. **Conviene
  commitear esto pronto.**
- Hubo un commit externo `082d205` ("migracion forzada") hecho fuera de esta conversación que
  incluyó cambios en `NativeAppServiceProvider.php` y `RELEASE.md` que no se revisaron aquí.

## 7. Nota sobre el `.env` de este checkout (no afecta la app real)

El `.env` de este repo tiene `DB_CONNECTION` definido dos veces (`sqlite`, y más abajo `mysql`
bajo el comentario "Original PROD Hostinger"), y gana la segunda definición. Esto **no afecta la
app empacada/instalada**: `NativeServiceProvider::rewriteDatabase()` (paquete NativePHP) siempre
fuerza `database.default` a una conexión propia (`nativephp`) con su propio archivo SQLite
gestionado internamente, sin importar lo que diga el `.env`. Solo afecta scripts sueltos
(`php artisan tinker`, scripts de prueba) corridos fuera del contexto de la app nativa — hay que
forzar `DB_CONNECTION=sqlite` manualmente en esos casos.

## 8. Ejecutable de Windows generado

- **Ruta:** `dist/POSTCI-1.0.0-setup.exe` (~119 MB), generado 2026-07-21 con
  `php artisan native:build win x64`.
- Incluye todos los fixes de la sección 3 (incluyendo el pendiente de commit, punto 8).
- **"INSECURE BUILD"**: el código PHP va expuesto sin compilar dentro del paquete (no se usó el
  servicio de build seguro de pago de NativePHP). Aceptable para una app interna de POS, pero
  vale la pena saberlo.
- El log mostró un error de "notarize"/codesign (`postci.app: No such file or directory`) — es un
  hook de firma para macOS que se dispara sin querer en un build de Windows; no afectó el
  resultado, el instalador se generó bien después de eso.
- El instalador probablemente no tiene un certificado de firma de código comprado/válido —
  Windows puede mostrar advertencia de "Editor desconocido" al ejecutarlo en la PC de la
  sucursal. Es esperable, solo hay que darle "Ejecutar de todas formas" / "Más información" →
  "Ejecutar de todas formas".

## 9. Checklist antes/durante la implementación en Estocolmo

- [ ] Confirmar que el catálogo de productos de Estocolmo ya está sincronizado localmente (vía
      Quick) antes de subir el Excel — si no, el import no actualizará nada (sección 2).
- [ ] Instalar `POSTCI-1.0.0-setup.exe` en la PC de la sucursal (aceptar advertencia de "Editor
      desconocido" si aparece).
- [ ] Subir `EXISTENCIAS ESTOCOLMO 20.07.26_8315.xls` desde la pantalla de import de productos.
- [ ] Revisar el mensaje de resultado ("X productos actualizados, Y sin coincidencia") — si Y es
      alto o igual al total de filas, es señal de que el catálogo no estaba sincronizado.
- [ ] Si algo falla, revisar el log de Laravel (`storage/logs/laravel.log`) — ahí quedan
      registrados hasta 200 códigos sin coincidencia con `Log::warning`.
- [ ] Pendiente aparte: commitear el fix de detección de formato de archivo (sección 6).

## 10. Archivos relevantes

- [app/Imports/ProductsImport.php](app/Imports/ProductsImport.php)
- [app/Http/Controllers/Admin/ProductController.php](app/Http/Controllers/Admin/ProductController.php)
- [app/Models/Product.php](app/Models/Product.php)
- [app/Http/Controllers/Admin/BranchController.php](app/Http/Controllers/Admin/BranchController.php) — sincronización de productos vía Quick (`getProducts2`)
- [app/Providers/NativeAppServiceProvider.php](app/Providers/NativeAppServiceProvider.php)
