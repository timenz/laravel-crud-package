# Crud Library for Laravel

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

to be continued ...