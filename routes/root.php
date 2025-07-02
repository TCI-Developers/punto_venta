<?php
use Illuminate\Support\Facades\Route;

Route::get('/import-datas/{table}', 'Admin\RootController@setDataDB')->name('import.data');
Route::get('/import-data-local/{model}/{table}', 'Admin\RootController@setDataDBLocal')->name('import.dataLocal'); //->middleware('permission:empresa,punto_venta,auth');
Route::post('/import-conf-local', 'Admin\RootController@setConfDBLocal')->name('import.setConfDBLocal');
Route::post('/reset-app', 'Admin\RootController@resetDatabase')->name('resetDatabase')->middleware('permission:empresa,punto_venta,auth');

Route::get('/logs', 'Admin\RootController@viewLogs')->name('logs');
Route::get('/clear-logs', 'Admin\RootController@clearLogs')->name('clearLogs');