## Crud Library for Laravel

Adalah crud library untuk laravel, almost awesome .

### Install

Tambahkan variabel berikut pada composer

	{
		"require": {
		  	"timenz/crud": "dev-master"
		},
	    "repositories": {
		  	"vcs_crud": {
			  "type": "vcs",
			  "url": "https://bitbucket.org/timenz/laravel-crud-package.git"
			}
	    },
	}

trus run

    composer update

add to $app['providers']

	'Timenz\Crud\CrudServiceProvider',

publish view

	php artisan view:publish timenz/crud

publish view on-dev

	php artisan view:publish --path="workbench/timenz/crud/src/views" timenz/crud

### Mininal code example


buat route

	Route::resource('ok', 'TestCrud');
	
code taruh sembarang asal kebaca composer

	<?php
    
    use \Timenz\Crud\Crud;
    
    class TestCrud extends Crud{
    
        public function __construct(){
            $this->init('users');
    
            $master = array('title' => 'my');
            $this->setMasterData($master);
            $this->setMasterBlade('admin.master');
        }
    }
    
