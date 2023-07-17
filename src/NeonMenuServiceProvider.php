<?php

namespace Neon\Site;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Http\Kernel;
use Neon\Site\Http\Middleware\SiteMiddleware;
use Neon\Site\Console\SiteClearCommand;
use Neon\Site\Console\SiteGenerateSiteIdCommand;
use Neon\View\Components\Favicon;

class NeonSiteServiceProvider extends ServiceProvider
{

  /** Bootstrap any application services.
   *
   * @param \Illuminate\Contracts\Http\Kernel  $kernel
   *
   * @return void
   */
  public function boot(Kernel $kernel): void
  {
    // $kernel->pushMiddleware(SiteMiddleware::class);
    
    if ($this->app->runningInConsole())
    {
      // file_put_contents(__DIR__.'/../config/config.php', Str::of(file_get_contents(__DIR__.'/../config/config.php'))->replace('##uuid##', Str::uuid()));
      // Storage::put(__DIR__.'/../config/config.php', Str::of(Storage::get(__DIR__.'/../config/config.php'))->replace('##uuid##', Str::uuid()));

      $this->publishes([
        __DIR__.'/../config/config_standalone.php'   => config_path('site.php'),
      ], 'neon-configs');
      // $this->publishes([
      //   __DIR__.'/../config/config_database.php'   => config_path('site.php'),
      // ], 'neon-site-database');

      if (!class_exists('CreateSitesTable')) {
        $this->publishes([
          __DIR__ . '/../database/migrations/create_sites_table.php.stub'           => database_path('migrations/' . date('Y_m_d_', time()) . '000001_create_sites_table.php'),
        ], 'neon-migrations');
      }

      if (!class_exists('CreateSitesPivot')) {
        $this->publishes([
          __DIR__ . '/../database/migrations/create_sites_pivot.php.stub'           => database_path('migrations/' . date('Y_m_d_', time()) . '000002_create_sites_pivot.php'),
        ], 'neon-migrations');
      }

      $this->loadViewComponentsAs('neon', [
        Favicon::class,
      ]);
  
      $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'neon');

      $this->commands([
          SiteGenerateSiteIdCommand::class,
          SiteClearCommand::class
      ]);
    }
  }

  public function register()
  {
    $this->app->singleton('site', function($app) {
      return new Site();
    });
  }
}
