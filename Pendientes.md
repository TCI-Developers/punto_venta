<!-- 15/may/25 -->
Revisar poruqe no se ejecutan los botones de bootstrap en productos (acciones)

<!-- 24/mar/25 -->
-Modulo de proveedores (Para Requisiciones)
* Se debe de crear el servicio para obtener los proveedores de quickbase (listo)
* Se debe desarrollar un CRUD de proveedores. (listo)
<!-- Terminado 25/mar/25 -->

-Tabla en DB de folios (no se ocupara)
* Se creara nueva tabla dedicada para folios (ventas, requisiciones) (pendiente para saber si se usara)

-Modulo de requisiciones
* Validar entrega de productos al guardar (listo)
* Se debe guardar las entradas en una tabla independiente para tener registro de los cambios (listo)
* Solo admin puede editar entradas, Rechachar y autorizar ///SE ESTAN EDITANDO LAS ENTRADAS, SE VE EL MODO PARA EDITARLAS SI SE ACTUALIZA TODO O SE ACTUALIZA SOLO EL REGISTRO
* Se hace orden de compra al entrar a status autorizar
* Antes de solicitar se debe llenar la fecha de entrega
* status solicitado no se puede modificar nada
* Vigencia de pago es el plazo mas la fecha de recibido
* Al cerrar la compra se actualizan los campos en dado caso de que existan cambios
* Se debe crear DRUD
* Dashboard para listar todas las requisiciones 
* PDF de la informacion despues de crear
* Se actualiza la existencia con lo recibido
<!-- Terminado 03/abr/25 -->
* Cuentas por cobrar (Despues de tener las requisiciones)
* Filtros para el listado (Fecha y sucursal)
* CRUD de las cuentas por cobrar en otro modulo
* Vista para crear con campos capturables Fecha e Importe seleccionar orden de compra en dado caso de que no este dentro del modulo
* Status 0 = eliminada, 1 = activa = 2 pagada
* Listado de cuentas por pagar con filtro de activas, pagadas y ordenar por fecha.
<!-- Terminado 21/abr/2025 -->
* En products se usa el stock de getparttoproduct, al momento de recibir en una orden de compra, agregar en esa tabla el stock 
* En dado caso de no tener presentacion se agrega solo en el producto
* En productos se debe de tomar en cuanta la existencia o la presentacion, para tomar el stock 
<!-- terminado 23/abr/2025 -->
* Tabla de empresa, datos de empresa para tomar en PDF y en registros qeu se requieran

* Revisar las conexiones de DB qeu se ocuparan, para escritorio se requiere una local y se ocupara otra para tenerla en la nuve.

<!-- quedan pendientes las devoluciones a matriz por falta de modulos que se requieren en el proyecto -->
-Revisar las devoluciones a matriz
* Vista para devoluciones por matriz
* Se debe de manejar la data tanto en la local, como en quickbase
* Una vez echa la devolucion se puede editar o cancelar?

Realizado
* 2 tablas nuevas para la devolucion a matriz (drivers y devoluciones_matriz)

