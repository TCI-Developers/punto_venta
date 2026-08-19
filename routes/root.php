<?php
use Illuminate\Support\Facades\Route;

Route::get('/import-datas/{table}', 'Admin\RootController@setDataDB')->name('import.data')->middleware('permission:empresa,punto_venta,auth');
Route::get('/import-data-local/{model}/{table}', 'Admin\RootController@setDataDBLocal')->name('import.dataLocal')->middleware('permission:empresa,punto_venta,auth');
Route::get('/import-catalogo-matriz', 'Admin\RootController@importCatalogoFromMatriz')->name('import.catalogoMatriz')->middleware('permission:empresa,punto_venta,auth');
// sin restriccion de permiso: cualquier usuario logueado (cualquier rol) puede ver el aviso
// de catalogo desactualizado y sincronizarlo el mismo, no solo root/admin.
Route::get('/catalogo-matriz-status', 'Admin\RootController@catalogStatus')->name('catalogoMatriz.status');
Route::post('/catalogo-matriz-sync-ajax', 'Admin\RootController@catalogSyncAjax')->name('catalogoMatriz.syncAjax');
Route::post('/import-conf-local', 'Admin\RootController@setConfDBLocal')->name('import.setConfDBLocal')->middleware('permission:empresa,punto_venta,auth');
Route::post('/reset-app', 'Admin\RootController@resetDatabase')->name('resetDatabase')->middleware('permission:empresa,punto_venta,auth');

Route::get('/logs', 'Admin\RootController@viewLogs')->name('logs')->middleware('permission:empresa,punto_venta,auth');
Route::get('/clear-logs', 'Admin\RootController@clearLogs')->name('clearLogs')->middleware('permission:empresa,punto_venta,auth');