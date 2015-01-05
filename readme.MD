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

to be continued ...