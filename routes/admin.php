<?php
use Illuminate\Support\Facades\Route;

Route::get('/import-data', 'Admin\RootController@index')->name('root.index'); //vista principal importacion de data a nube

Route::get('/', 'Admin\BranchController@index')->name('admin.index'); //vista principal
Route::get('/start-amount-box', 'Admin\AdminController@startAmountBox')->name('admin.startAmountBox'); //vista principal
Route::get('/empresa', 'Admin\AdminController@empresa')->name('admin.empresa'); //vista muetsra datos de la empresa
Route::post('/empresa-update', 'Admin\AdminController@empresaUpdate')->name('admin.empresaUpdate'); //vista muetsra datos de la empresa

//corte de caja
Route::get('/turn-off', 'Admin\BoxController@index')->name('box.index');
Route::post('/store-start-amount', 'Admin\BoxController@storeStarAmountBox')->name('box.storeStarAmountBox');
Route::get('/turn-off-view', 'Admin\BoxController@turnOff')->name('box.turnOff');
Route::post('/turn-off-store', 'Admin\BoxController@store')->name('box.store');

//sucursal
Route::get('/quickbase-import/{table_name}', 'Admin\BranchController@importarQuickbase')->name('branchs.import'); //importar sucursales de quickbase
Route::get('/branchs/{status?}', 'Admin\BranchController@index')->name('branchs.index');
Route::get('/branchs-create', 'Admin\BranchController@create')->name('branchs.create');
Route::post('/branchs-store/{branch_id?}', 'Admin\BranchController@store')->name('branchs.store');
Route::get('/branchs-show/{branch_id}', 'Admin\BranchController@show')->name('branchs.show');
Route::get('/branchs-destroy/{id}/{status?}', 'Admin\BranchController@destroy')->name('branchs.destroy');
Route::get('/branchs-set-branch/{branch_id}', 'Admin\BranchController@setSucursalUser')->name('branchs.setSucursalUser');

//products
Route::get('/products', 'Admin\ProductController@index')->name('product.index'); //vista principal productos
Route::get('/products-show-upload-excel', 'Admin\ProductController@showUploadExcel')->name('product.showUploadExcel'); 
Route::get('/products-add-presentation/{product_id}/{despiece?}', 'Admin\ProductController@create')->name('product.create'); //vista para presentaciones/devoluciones/promociones
Route::post('/products-store-presentation/{product_id}', 'Admin\ProductController@store')->name('product.store'); //funcion para guardar las presentaciones/devoluciones/promociones asignadas
Route::post('/presentation-store', 'Admin\ProductController@storePresentationProduct')->name('product.storePresentationProduct');
Route::post('/presentation-update', 'Admin\ProductController@updatePresentationProduct')->name('product.updatePresentationProduct');

Route::get('/products-show-upload-excel', 'Admin\ProductController@showUploadExcel')->name('product.showUploadExcel'); 
Route::post('/products-upload-excel', 'Admin\ProductController@uploadExcel')->name('product.uploadExcel'); 

//customers
Route::get('/customers', 'Admin\CustomerController@index')->name('customer.index'); //vista principal clcientes
Route::post('/customers-store', 'Admin\CustomerController@store')->name('customer.store'); //vista principal clcientes
Route::get('/customers-destroy/{id}/{status}', 'Admin\CustomerController@destroy')->name('customer.destroy'); //vista principal clcientes

//proveedores
Route::get('/poveedores/{status?}', 'Admin\ProveedorController@index')->name('proveedor.index');
Route::get('/poveedores-create', 'Admin\ProveedorController@create')->name('proveedor.create');
Route::post('/poveedores-store/{proveedor_id?}', 'Admin\ProveedorController@store')->name('proveedor.store');
Route::get('/poveedores-show/{proveedor_id?}', 'Admin\ProveedorController@create')->name('proveedor.show');
Route::get('/poveedores-enable/{proveedor_id}/{status}', 'Admin\ProveedorController@enable')->name('proveedor.enable');

//sales
Route::get('/sales', 'Admin\SaleController@index')->name('sale.index'); //vista principal ventas
Route::get('/sales-create', 'Admin\SaleController@create')->name('sale.create'); //vista principal ventas
Route::post('/sales-store', 'Admin\SaleController@store')->name('sale.store');
Route::get('/sales-show/{id}', 'Admin\SaleController@show')->name('sale.show');
Route::post('/sales-update/{id}', 'Admin\SaleController@update')->name('sale.update');
Route::get('/sales-destroy/{id}', 'Admin\SaleController@destroy')->name('sale.destroy');

//compras
Route::get('/compras/{status?}', 'Admin\CompraController@index')->name('compra.index');
Route::get('/compras-create', 'Admin\CompraController@create')->name('compra.create');
Route::post('/compras-store/{compra_id?}', 'Admin\CompraController@store')->name('compra.store');
Route::post('/compras-store-close/{compra_id}', 'Admin\CompraController@storeRecibido')->name('compra.storeRecibido');
Route::get('/compras-show/{compra_id?}', 'Admin\CompraController@create')->name('compra.show');
Route::get('/compras-status/{compra_id?}/{status}', 'Admin\CompraController@status')->name('compra.status'); //cambiar el status de la compra
Route::get('/detalle-compra-destroy/{detalle_id}', 'Admin\CompraController@destroy')->name('compra.destroy'); //cambiar el status de la compra
Route::get('/compra-pdf/{compra_id}', 'Admin\CompraController@pdf')->name('compra.pdf');

//cuentas por pagar
Route::get('/cxp/{status?}', 'Admin\CuentaPagarController@index')->name('cxp.index');
Route::get('/cxp-show/{id}', 'Admin\CuentaPagarController@show')->name('cxp.show');
Route::post('/cxp-store/{id}', 'Admin\CuentaPagarController@store')->name('cxp.store');
Route::get('/cxp-destroy/{id}', 'Admin\CuentaPagarController@destroy')->name('cxp.destroy');

//devoluciones ventas
Route::get('/devoluciones/{status?}', 'Admin\DevolucionController@index')->name('devoluciones.index');
Route::get('/devoluciones-corte/{starDate}/{endDate}', 'Admin\DevolucionController@indexDevCorte')->name('devoluciones.indexDevCorte'); //devoluciones por fechas de algun corte
Route::get('/devoluciones-create-sale', 'Admin\DevolucionController@showListadoVentas')->name('devoluciones.showListadoVentas'); //crear una devolucion de venta
Route::get('/devoluciones-show-dev-sale/{devolucion_id}', 'Admin\DevolucionController@showDevSale')->name('devoluciones.showDevSale');
Route::get('/devoluciones-delete-detail-dev/{devolution_id}/{detail_dev_id}', 'Admin\DevolucionController@deleteDetailDev')->name('devoluciones.deleteDetailDev'); //elimina un detalle de la venta en status 0
Route::post('/devoluciones-store/{devolucion_id?}', 'Admin\DevolucionController@store')->name('devoluciones.store'); //funcion para guardar la devolucion de venta
Route::get('/devoluciones-sale-create/{sale_id}', 'Admin\DevolucionController@createSaleToDevolucion')->name('devoluciones.createSaleToDevolucion'); // muestra vista de la devolucion de venta

//devoluciones matriz
Route::get('/devoluciones-create-matriz', 'Admin\DevolucionController@createMatriz')->name('devoluciones.createMatriz'); //crear una devolucion de matriz
Route::post('/devoluciones-store-matriz', 'Admin\DevolucionController@storeMatriz')->name('devoluciones.storeMatriz'); //crear una devolucion de matriz

//turnos
Route::get('/turnos/{status}', 'Admin\TurnoController@index')->name('turnos.index');
Route::post('/turnos-store', 'Admin\TurnoController@store')->name('turnos.store');
Route::post('/turnos-update', 'Admin\TurnoController@update')->name('turnos.update');
Route::get('/turnos-destroy/{id}', 'Admin\TurnoController@destroy')->name('turnos.destroy');
Route::get('/turnos-enable/{id}', 'Admin\TurnoController@enable')->name('turnos.enable');

//roles
Route::get('/roles/{status}', 'Admin\RoleController@index')->name('roles.index');
Route::post('/roles-store', 'Admin\RoleController@store')->name('roles.store');
Route::post('/roles-update', 'Admin\RoleController@update')->name('roles.update');
Route::get('/roles-destroy/{id}', 'Admin\RoleController@destroy')->name('roles.destroy');
Route::get('/roles-enable/{id}', 'Admin\RoleController@enable')->name('roles.enable');

//usuarios
Route::get('/users/{status?}', 'Admin\UserController@index')->name('users.index');
Route::post('/users-store', 'Admin\UserController@store')->name('users.store');
Route::post('/users-update', 'Admin\UserController@update')->name('users.update');
Route::get('/users-destroy/{id}/{status}', 'Admin\UserController@destroy')->name('users.destroy');
Route::post('/users-turnos-roles', 'Admin\UserController@rolesTurnos')->name('users.setRolesTurnos');
Route::post('/users-update-turnos-roles', 'Admin\UserController@updateRolesTurnos')->name('users.updateRolesTurnos');
Route::get('/users-logout', 'Admin\UserController@logout')->name('logout_');