<?php
use Illuminate\Support\Facades\Route;

Route::get('/import-data', 'Admin\RootController@index')->name('root.index'); //vista principal importacion de data a nube

Route::get('/', 'Admin\BranchController@index')->name('admin.index'); //vista principal
Route::get('/start-amount-box', 'Admin\AdminController@startAmountBox')->name('admin.startAmountBox'); //vista principal

Route::get('/empresa', 'Admin\AdminController@empresa')->name('admin.empresa')->middleware('permission:empresa,punto_venta,[show|update]'); //vista muetsra datos de la empresa
Route::post('/empresa-update', 'Admin\AdminController@empresaUpdate')->name('admin.empresaUpdate')->middleware('permission:empresa,punto_venta,[update]'); //vista muetsra datos de la empresa

//corte de caja
Route::get('/turn-off', 'Admin\BoxController@index')->name('box.index')->middleware('permission:listado_cierre_caja,punto_venta,show');
Route::post('/store-start-amount', 'Admin\BoxController@storeStarAmountBox')->name('box.storeStarAmountBox');
Route::get('/turn-off-view', 'Admin\BoxController@turnOff')->name('box.turnOff');
Route::post('/turn-off-store', 'Admin\BoxController@store')->name('box.store');
Route::get('/turn-off-store-ticket/{status?}', 'Admin\BoxController@statusBox')->name('box.statusBox');
Route::get('/test', 'Admin\BoxController@getComprasDbExt')->name('box.getDevolutionDBExt');

//sucursal
Route::get('/quickbase-import/{table_name}', 'Admin\BranchController@importarQuickbase')->name('branchs.import'); //importar sucursales de quickbase
Route::get('/branchs/{status?}', 'Admin\BranchController@index')->name('branchs.index')->middleware('permission:sucursales');
Route::get('/branchs-create', 'Admin\BranchController@create')->name('branchs.create')->middleware('permission:sucursales,punto_venta,create');
Route::post('/branchs-store/{branch_id?}', 'Admin\BranchController@store')->name('branchs.store')->middleware('permission:sucursales,punto_venta,[create|update]');
Route::get('/branchs-show/{branch_id}', 'Admin\BranchController@show')->name('branchs.show')->middleware('permission:sucursales,punto_venta,[show|update]');
Route::get('/branchs-destroy/{id}/{status?}', 'Admin\BranchController@destroy')->name('branchs.destroy')->middleware('permission:sucursales,punto_venta,destroy');
Route::get('/branchs-set-branch/{branch_id}', 'Admin\BranchController@setSucursalUser')->name('branchs.setSucursalUser');

//products
Route::get('/products', 'Admin\ProductController@index')->name('product.index')->middleware('permission:inventarios'); //vista principal productos
Route::get('/products-show-upload-excel', 'Admin\ProductController@showUploadExcel')->name('product.showUploadExcel'); 
Route::get('/products-add-presentation/{product_id}/{despiece?}', 'Admin\ProductController@create')->name('product.create')->middleware('permission:inventarios,punto_venta,create'); //vista para presentaciones/devoluciones/promociones
Route::post('/products-store-presentation/{product_id}', 'Admin\ProductController@store')->name('product.store')->middleware('permission:inventarios,punto_venta,create'); //funcion para guardar las presentaciones/devoluciones/promociones asignadas
Route::post('/presentation-store', 'Admin\ProductController@storePresentationProduct')->name('product.storePresentationProduct')->middleware('permission:inventarios,punto_venta,create');
Route::post('/presentation-update', 'Admin\ProductController@updatePresentationProduct')->name('product.updatePresentationProduct')->middleware('permission:inventarios,punto_venta,update');

Route::get('/products-show-upload-excel', 'Admin\ProductController@showUploadExcel')->name('product.showUploadExcel'); 
Route::post('/products-upload-excel', 'Admin\ProductController@uploadExcel')->name('product.uploadExcel')->middleware('permission:inventarios,punto_venta,create'); 

//customers
Route::get('/customers', 'Admin\CustomerController@index')->name('customer.index')->middleware('permission:clientes'); //vista principal clcientes
Route::post('/customers-store', 'Admin\CustomerController@store')->name('customer.store')->middleware('permission:clientes,punto_venta,create'); //vista principal clcientes
Route::post('/customers-update', 'Admin\CustomerController@store')->name('customer.store')->middleware('permission:clientes,punto_venta,update'); //vista principal clcientes
Route::get('/customers-destroy/{id}/{status}', 'Admin\CustomerController@destroy')->name('customer.destroy')->middleware('permission:clientes,punto_venta,destroy'); //vista principal clcientes

//proveedores
Route::get('/poveedores/{status?}', 'Admin\ProveedorController@index')->name('proveedor.index')->middleware('permission:proveedores');
Route::get('/poveedores-create', 'Admin\ProveedorController@create')->name('proveedor.create')->middleware('permission:proveedores,punto_venta,create');
Route::post('/poveedores-store/{proveedor_id?}', 'Admin\ProveedorController@store')->name('proveedor.store')->middleware('permission:proveedores,punto_venta,create');
Route::get('/poveedores-show/{proveedor_id?}', 'Admin\ProveedorController@create')->name('proveedor.show')->middleware('permission:proveedores,punto_venta,[show|update]');
Route::post('/poveedores-update/{proveedor_id?}', 'Admin\ProveedorController@store')->name('proveedor.store')->middleware('permission:proveedores,punto_venta,update');
Route::get('/poveedores-enable/{proveedor_id}/{status}', 'Admin\ProveedorController@enable')->name('proveedor.enable')->middleware('permission:proveedores,punto_venta,create');

//sales
Route::get('/sales', 'Admin\SaleController@index')->name('sale.index')->middleware('permission:ventas'); //vista principal ventas
Route::get('/sales-create', 'Admin\SaleController@create')->name('sale.create')->middleware('permission:ventas,punto_venta,create'); //vista principal ventas
Route::post('/sales-store', 'Admin\SaleController@store')->name('sale.store')->middleware('permission:ventas,punto_venta,create');
Route::get('/sales-show/{id}', 'Admin\SaleController@show')->name('sale.show')->middleware('permission:ventas,punto_venta,show');
Route::post('/sales-update/{id}', 'Admin\SaleController@update')->name('sale.update')->middleware('permission:ventas,punto_venta,[create|update]');
Route::get('/sales-destroy/{id}', 'Admin\SaleController@destroy')->name('sale.destroy')->middleware('permission:ventas,punto_venta,destroy');

//compras
Route::get('/compras/{status?}', 'Admin\CompraController@index')->name('compra.index')->middleware('permission:compras');
Route::get('/compras-create', 'Admin\CompraController@create')->name('compra.create')->middleware('permission:compras,punto_venta,create');
Route::post('/compras-store/{compra_id?}', 'Admin\CompraController@store')->name('compra.store')->middleware('permission:compras,punto_venta,create');
Route::post('/compras-update/{compra_id?}', 'Admin\CompraController@store')->name('compra.store')->middleware('permission:compras,punto_venta,update');
Route::post('/compras-store-close/{compra_id}', 'Admin\CompraController@storeRecibido')->name('compra.storeRecibido');
Route::get('/compras-show/{compra_id?}', 'Admin\CompraController@create')->name('compra.show')->middleware('permission:compras,punto_venta,show');
Route::get('/compras-status/{compra_id?}/{status}', 'Admin\CompraController@status')->name('compra.status'); //cambiar el status de la compra
Route::get('/detalle-compra-destroy/{detalle_id}', 'Admin\CompraController@destroy')->name('compra.destroy')->middleware('permission:compras,punto_venta,destroy'); //cambiar el status de la compra
Route::get('/compra-pdf/{compra_id}', 'Admin\CompraController@pdf')->name('compra.pdf')->middleware('permission:compras,punto_venta,show');

//cuentas por pagar
Route::get('/cxp/{status?}', 'Admin\CuentaPagarController@index')->name('cxp.index')->middleware('permission:cuentas_por_pagar');
Route::get('/cxp-show/{id}', 'Admin\CuentaPagarController@show')->name('cxp.show')->middleware('permission:cuentas_por_pagar,punto_venta,show');
Route::post('/cxp-store/{id}', 'Admin\CuentaPagarController@store')->name('cxp.store')->middleware('permission:cuentas_por_pagar,punto_venta,create');
Route::get('/cxp-destroy/{id}', 'Admin\CuentaPagarController@destroy')->name('cxp.destroy')->middleware('permission:cuentas_por_pagar,punto_venta,destroy');

//devoluciones ventas
Route::get('/devoluciones/{status?}', 'Admin\DevolucionController@index')->name('devoluciones.index')->middleware('permission:devoluciones');
Route::get('/devoluciones-corte/{starDate}/{endDate}', 'Admin\DevolucionController@indexDevCorte')->name('devoluciones.indexDevCorte'); //devoluciones por fechas de algun corte
Route::get('/devoluciones-create-sale', 'Admin\DevolucionController@showListadoVentas')->name('devoluciones.showListadoVentas')->middleware('permission:devoluciones,punto_venta,create'); //crear una devolucion de venta
Route::get('/devoluciones-show-dev-sale/{devolucion_id}', 'Admin\DevolucionController@showDevSale')->name('devoluciones.showDevSale')->middleware('permission:devoluciones,punto_venta,[show|update]');
Route::get('/devoluciones-delete-detail-dev/{devolution_id}/{detail_dev_id}', 'Admin\DevolucionController@deleteDetailDev')->name('devoluciones.deleteDetailDev')->middleware('permission:devoluciones,punto_venta,destroy'); //elimina un detalle de la venta en status 0
Route::post('/devoluciones-store/{devolucion_id?}', 'Admin\DevolucionController@store')->name('devoluciones.store')->middleware('permission:devoluciones,punto_venta,create'); //funcion para guardar la devolucion de venta
Route::get('/devoluciones-sale-create/{sale_id}', 'Admin\DevolucionController@createSaleToDevolucion')->name('devoluciones.createSaleToDevolucion'); // muestra vista de la devolucion de venta

//devoluciones matriz
Route::get('/devoluciones-compras', 'Admin\DevolucionController@indexCompras')->name('devoluciones.indexCompras'); //crear una devolucion de matriz
Route::get('/devoluciones-create-matriz', 'Admin\DevolucionController@createMatriz')->name('devoluciones.createMatriz'); //crear una devolucion de matriz
Route::post('/devoluciones-store-matriz', 'Admin\DevolucionController@storeMatriz')->name('devoluciones.storeMatriz'); //crear una devolucion de matriz

Route::get('/devoluciones-show/{id}', 'Admin\DevolucionController@showMatriz')->name('devoluciones.showMatriz'); //crear una devolucion de matriz

//turnos
Route::get('/turnos/{status}', 'Admin\TurnoController@index')->name('turnos.index')->middleware('permission:turnos');
Route::post('/turnos-store', 'Admin\TurnoController@store')->name('turnos.store')->middleware('permission:turnos,punto_venta,create');
Route::post('/turnos-update', 'Admin\TurnoController@update')->name('turnos.update')->middleware('permission:turnos,punto_venta,update');
Route::get('/turnos-destroy/{id}', 'Admin\TurnoController@destroy')->name('turnos.destroy')->middleware('permission:turnos,punto_venta,destroy');
Route::get('/turnos-enable/{id}', 'Admin\TurnoController@enable')->name('turnos.enable')->middleware('permission:turnos,punto_venta,destroy');

//roles
Route::get('/roles/{status}', 'Admin\RoleController@index')->name('roles.index')->middleware('permission:roles');
Route::post('/roles-store', 'Admin\RoleController@store')->name('roles.store')->middleware('permission:roles,punto_venta,create');
Route::post('/roles-update', 'Admin\RoleController@update')->name('roles.update')->middleware('permission:roles,punto_venta,update');
Route::get('/roles-destroy/{id}', 'Admin\RoleController@destroy')->name('roles.destroy')->middleware('permission:roles,punto_venta,destroy');
Route::get('/roles-enable/{id}', 'Admin\RoleController@enable')->name('roles.enable')->middleware('permission:roles,punto_venta,destroy');
// Nuevas rutas para gestión de permisos
Route::get('/roles-permissions/{role}', 'Admin\RoleController@permissions')->name('roles.permissions');
Route::post('/roles-sync-permissions/{role}', 'Admin\RoleController@syncPermissions')->name('roles.permissions.sync')->middleware('permission:roles,punto_venta,auth');

//permisos
Route::get('/permissions', 'Admin\PermissionController@index')->name('permission.index');
Route::post('/permissions-store', 'Admin\PermissionController@store')->name('permission.store');
Route::post('/permissions-update', 'Admin\PermissionController@update')->name('permission.update');
Route::get('/permissions-desctroy/{id}', 'Admin\PermissionController@destroy')->name('permission.destroy');

//usuarios
Route::get('/users/{status?}', 'Admin\UserController@index')->name('users.index')->middleware('permission:usuarios');
Route::post('/users-store', 'Admin\UserController@store')->name('users.store')->middleware('permission:usuarios,punto_venta,create');
Route::post('/users-update', 'Admin\UserController@update')->name('users.update')->middleware('permission:usuarios,punto_venta,update');
Route::get('/users-destroy/{id}/{status}', 'Admin\UserController@destroy')->name('users.destroy')->middleware('permission:usuarios,punto_venta,destroy');
Route::post('/users-turnos-roles', 'Admin\UserController@rolesTurnos')->name('users.setRolesTurnos')->middleware('permission:usuarios,punto_venta,auth');
Route::post('/users-update-turnos-roles', 'Admin\UserController@updateRolesTurnos')->name('users.updateRolesTurnos');
Route::get('/users-logout', 'Admin\UserController@logout')->name('logout_');

Route::get('/ticket-sale/{sale_id}/{auto?}', 'Controller@ticket')->name('ticket.sale');
Route::get('/ticket-devolution/{devolution_id}/{auto?}', 'Controller@ticket')->name('ticket.devolution');
Route::get('/ticket-box/{user_id}/{auto?}', 'Controller@ticket')->name('ticket.box');