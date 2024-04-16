<?php

Route::get('/view-test', function(){
    return view('view_test');
})->name('test');