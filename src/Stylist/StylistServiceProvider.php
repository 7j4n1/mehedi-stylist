<?php

namespace Mehedi\Stylist;

use Illuminate\Support\AggregateServiceProvider;
use Mehedi\Stylist\Html\ThemeHtmlBuilder;
use Mehedi\Stylist\Theme\Stylist;
use Mehedi\Stylist\Theme\Loader;
use Illuminate\Support\Facades\Config;

class StylistServiceProvider extends AggregateServiceProvider
{
    /**
     * Stylist provides the HtmlServiceProvider for ease-of-use.
     *
     * @var array
     */
    protected $providers = [
        'Collective\Html\HtmlServiceProvider'
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerConfiguration();
        $this->registerStylist();
        $this->registerAliases();
        $this->registerThemeBuilder();
        $this->registerCommands();

    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootThemes();
    }

    protected function bootThemes()
    {
        $stylist = $this->app->make('stylist');
        $paths = $this->app['config']->get('stylist.themes.paths', []);

        // loop through the paths, discover the path and register the themes paths
        foreach ($paths as $path) {
            $themePath = $stylist->discover($path);
            $stylist->registerPaths($themePath);
        }

        // register the active theme
        $theme = $this->app['config']->get('stylist.themes.activate', null);

        if(!is_null($theme)) {
            $stylist->activate($theme);
        }

    }

    /**
     * Sets up the object that will be used for theme registration calls.
     */
    protected function registerStylist()
    {
        $this->app->singleton('stylist', function ($app) {
            return new Stylist(new Loader, $app);
        });
    }

    /**
     * Create the binding necessary for the theme html builder.
     */
    protected function registerThemeBuilder()
    {
        $this->app->singleton('stylist.theme', function ($app) {
            return new ThemeHtmlBuilder($app['html'], $app['url']);
        });
    }

    /**
     * Stylist class should be accessible from global scope for ease of use.
     */
    private function registerAliases()
    {
        $this->app->singleton(\Mehedi\Stylist\Facades\StylistFacade::class);
        $this->app->singleton(\Mehedi\Stylist\Facades\ThemeFacade::class);

        $this->app->alias(\Mehedi\Stylist\Facades\StylistFacade::class, 'Stylist');
        $this->app->alias(\Mehedi\Stylist\Facades\ThemeFacade::class, 'Theme');

        // $this->app->alias(\Mehedi\Stylist\Theme\Stylist::class, 'stylist');
    }

    /**
     * Register the commands available to the package.
     */
    private function registerCommands()
    {
        $this->commands(
            'Mehedi\Stylist\Console\PublishAssetsCommand'
        );
    }

    /**
     * Setup the configuration that can be used by stylist.
     */
    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => $this->app->configPath('stylist.php')
        ]);
    }

    /**
     * An array of classes that Stylist provides.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(parent::provides(), [
            'Stylist',
            'Theme'
        ]);
    }
}
