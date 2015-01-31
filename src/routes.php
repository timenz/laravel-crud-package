<?php

Route::get('crud/{action?}', array('uses' => 'CrudController@index'));
