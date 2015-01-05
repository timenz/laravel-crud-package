<?php
Route::get('crud/{model}/{method}', array('uses' => 'CrudCtl@index'));
Route::get('crud_create/{model}/{method}', array('uses' => 'CrudCtl@create'));
Route::post('crud_save/{model}/{method}', array('uses' => 'CrudCtl@save', 'before' => 'csrf', function() {
    return 'You gave a valid CSRF token!';
}));
Route::get('crud_read/{model}/{method}/{id}', array('uses' => 'CrudCtl@read'));
Route::get('crud_edit/{model}/{method}/{id}', array('uses' => 'CrudCtl@edit'));
Route::put('crud_update/{model}/{method}/{id}', array('uses' => 'CrudCtl@update', 'before' => 'csrf', function() {
    return 'You gave a valid CSRF token!';
}));
Route::delete('crud_delete/{model}/{method}/{id}', array('uses' => 'CrudCtl@delete', 'before' => 'csrf', function() {
    return 'You gave a valid CSRF token!';
}));


Blade::extend(function($value){
    return preg_replace('/(\s*)@(break|continue)(\s*)/', '$1<?php $2; ?>$3', $value);
});