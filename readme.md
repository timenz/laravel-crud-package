## Crud Library for Laravel

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

	
code taruh sembarang asal kebaca composer

	<?php
	
	use \Timenz\Crud\Crud;
	
	class UserCrud extends Crud{
	
		function index(){
	
			$master = array(
				'title' => 'my'
			);
			
			$this->init('users');
			
			$this->setMasterBlade('admin.master');
			$this->setMasterData($master) ;
			
			return $this->execute();
		}
	}
	
panggil melalui url 

	/crud/user_crud/index
	
	crud : controller
	user_crud : nama class (UserCrud)
	index : nama method
	
