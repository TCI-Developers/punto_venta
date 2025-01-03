<?php

Route::get('/', 'Admin\AdminController@index')->name('admin.index'); //vista principal

//products
Route::get('/products', 'Admin\ProductController@index')->name('product.index'); //vista principal productos
Route::get('/products-add-presentation/{product_id}', 'Admin\ProductController@create')->name('product.create'); //vista para presentaciones/devoluciones/promociones
Route::post('/products-store-presentation/{product_id}', 'Admin\ProductController@store')->name('product.store'); //funcion para guardar las presentaciones/devoluciones/promociones asignadas
Route::post('/products-update-presentation', 'Admin\ProductController@update')->name('product.update'); //funcion para guardar las presentaciones/devoluciones/promociones asignadas

Route::get('/products-show-upload-excel', 'Admin\ProductController@showUploadExcel')->name('product.showUploadExcel'); 
Route::post('/products-upload-excel', 'Admin\ProductController@uploadExcel')->name('product.uploadExcel'); 

Route::get('/presentation-product', 'Admin\ProductController@indexPartProduct')->name('product.indexPartProduct'); //vista principal productos
Route::get('/presentation-product-disabled', 'Admin\ProductController@indexPartProductDisabled')->name('product.indexPartProductDisabled'); //vista principal productos
Route::post('/presentation-store', 'Admin\ProductController@storePresentationProduct')->name('product.storePresentationProduct');
Route::post('/presentation-update', 'Admin\ProductController@updatePresentationProduct')->name('product.updatePresentationProduct');
Route::get('/presentation-destroy/{id}/{status}', 'Admin\ProductController@destroyPresentationProduct')->name('product.destroyPresentationProduct');

//customers
Route::get('/customers', 'Admin\CustomerController@index')->name('customer.index'); //vista principal clcientes
Route::post('/customers-store', 'Admin\CustomerController@store')->name('customer.store'); //vista principal clcientes
Route::get('/customers-destroy/{id}/{status}', 'Admin\CustomerController@destroy')->name('customer.destroy'); //vista principal clcientes

//sales
Route::get('/sales', 'Admin\SaleController@index')->name('sale.index'); //vista principal ventas
Route::get('/sales-create', 'Admin\SaleController@create')->name('sale.create'); //vista principal ventas
Route::post('/sales-store', 'Admin\SaleController@store')->name('sale.store');
Route::get('/sales-show/{id}', 'Admin\SaleController@show')->name('sale.show');
Route::post('/sales-update/{id}', 'Admin\SaleController@update')->name('sale.update');

//sales detail
Route::post('/sales-detail-store', 'Admin\SaleController@storeDetail')->name('sale.storeDetail');
Route::post('/sales-detail-update', 'Admin\SaleController@updateDetail')->name('sale.updateDetail');

//categorias
Route::get('/categorys', 'Admin\CategoryController@index')->name('category.index');
Route::get('/categorys-delete', 'Admin\CategoryController@indexDead')->name('category.indexDead');
Route::post('/category-store', 'Admin\CategoryController@store')->name('category.store');
Route::post('/category-update', 'Admin\CategoryController@update')->name('category.update');
Route::get('/category-destroy/{id}', 'Admin\CategoryController@destroy')->name('category.destroy');
Route::get('/category-enable/{id}', 'Admin\CategoryController@enable')->name('category.enable');

//corte de caja
Route::get('/turn-off', 'Admin\BoxController@index')->name('box.index');
Route::get('/turn-off-view', 'Admin\BoxController@turnOff')->name('box.turnOff');
Route::post('/turn-off-store', 'Admin\BoxController@store')->name('box.store');

//usuarios
Route::get('/users/{status?}', 'Admin\UserController@index')->name('users.index');
Route::post('/users-store', 'Admin\UserController@store')->name('users.store');
Route::post('/users-update', 'Admin\UserController@update')->name('users.update');
Route::get('/users-destroy/{id}/{status}', 'Admin\UserController@destroy')->name('users.destroy');
Route::post('/users-turnos-roles', 'Admin\UserController@rolesTurnos')->name('users.setRolesTurnos');
Route::post('/users-update-turnos-roles', 'Admin\UserController@updateRolesTurnos')->name('users.updateRolesTurnos');
Route::get('/users-logout', 'Admin\UserController@logout')->name('logout_');

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

//sucursal
Route::get('/quickbase-import/{table_name}', 'Admin\BranchController@importarQuickbase')->name('branchs.import'); //importar sucursales de quickbase
Route::get('/branchs/{status?}', 'Admin\BranchController@index')->name('branchs.index');
Route::get('/branchs-create', 'Admin\BranchController@create')->name('branchs.create');
Route::post('/branchs-store/{branch_id?}', 'Admin\BranchController@store')->name('branchs.store');
Route::get('/branchs-show/{branch_id}', 'Admin\BranchController@show')->name('branchs.show');
Route::get('/branchs-destroy/{id}/{status?}', 'Admin\BranchController@destroy')->name('branchs.destroy');
Route::get('/branchs-set-branch/{branch_id}', 'Admin\BranchController@setSucursalUser')->name('branchs.setSucursalUser');

Route::get('/branch', 'Admin\BranchController@index')->name('branch.index');
Route::post('/branch-store', 'Admin\BranchController@store')->name('branch.store');
Route::get('/products-import/{branch_id}', 'Admin\BranchController@getProducts')->name('import.products'); //ruta para importar productos de quickbase y almacenar en la DB local
// Route::get('/brands-import', 'Admin\UserController@getBrands'); //ruta para importar productos de quickbase y almacenar en la DB local

//promociones
Route::get('/promos/{status?}', 'Admin\PromotionController@index')->name('promos.index');
Route::get('/promos-create', 'Admin\PromotionController@create')->name('promos.create');
Route::post('/promos-store', 'Admin\PromotionController@store')->name('promos.store');
Route::get('/promos-show/{promo_id}/{status?}', 'Admin\PromotionController@create')->name('promos.show');
Route::post('/promos-update/{promo_id}', 'Admin\PromotionController@update')->name('promos.update');
Route::get('/promos-destroy/{promo_id}/{status}', 'Admin\PromotionController@destroy')->name('promos.destroy');

//devoluciones
Route::get('/devoluciones/{status?}', 'Admin\DevolucionController@index')->name('devoluciones.index');
Route::get('/devoluciones-create/{type_or_devolucion_id?}', 'Admin\DevolucionController@show')->name('devoluciones.create');
Route::get('/devoluciones-create-sales/{devolucion_id?}/{sale_id}', 'Admin\DevolucionController@createDevolucionSale')->name('devoluciones.createSale');
Route::post('/devoluciones-store', 'Admin\DevolucionController@store')->name('devoluciones.store');
Route::get('/devoluciones-show/{devolucion_id}', 'Admin\DevolucionController@show')->name('devoluciones.show');
Route::get('/devoluciones-show-sale/{devolucion_id}/{sale_id}', 'Admin\DevolucionController@createDevolucionSale')->name('devoluciones.showDevSale');
Route::post('/devoluciones-update/{devolucion_id}', 'Admin\DevolucionController@update')->name('devoluciones.update');
Route::get('/devoluciones-destroy/{devolucion_id}/{status}', 'Admin\DevolucionController@destroy')->name('devoluciones.destroy');