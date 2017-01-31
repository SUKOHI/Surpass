<?php namespace Sukohi\Surpass;

use Illuminate\Support\ServiceProvider;

class SurpassServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/views', 'surpass');
		$this->publishes([
			__DIR__.'/migrations' => database_path('migrations')
		], 'migrations');
		$this->publishes([
			__DIR__.'/config/surpass.php' => config_path('surpass.php'),
		], 'config');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->singleton('surpass', function(){

            return new Surpass;

        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['surpass'];
	}

}
