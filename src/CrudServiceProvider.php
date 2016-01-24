<?php namespace Timenz\Crud;

use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot(){
//		$this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'admin-manager');
		$this->loadTranslationsFrom($this->app->basePath().'/resources/lang/vendor/timenz/crud', 'crud');
		$this->publishes([
			__DIR__.'/views' => base_path('resources/views/vendor/timenz/crud'),
			__DIR__.'/../public' => base_path('public/vendor/timenz/crud'),
			__DIR__.'/../lang' => base_path('resources/lang/vendor/timenz/crud'),

		]);

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(){

		$this->app->bind('crud',function($app){
			return new Crud($app);
		});
	}

}
