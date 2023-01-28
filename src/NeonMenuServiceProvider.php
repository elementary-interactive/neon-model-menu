<?php

namespace Neon;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Http\Kernel;
use Neon\View\Components\Menu;

class NeonMenuServiceProvider extends ServiceProvider
{

  /** Bootstrap any application services.
   *
   * @param \Illuminate\Contracts\Http\Kernel  $kernel
   *
   * @return void
   */
  public function boot(Kernel $kernel): void
  {

    if ($this->app->runningInConsole()) {
      if (!class_exists('CreateSitesTable')) {
        $this->publishes([
          __DIR__ . '/../database/migrations/create_links_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_links_table.php'),
          __DIR__ . '/../database/migrations/create_menus_pivot.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_menus_table.php'),
          // you can add any number of migrations here
        ], 'neon-site');
      }
    }

    $this->loadViewComponentsAs('neon', [
      Menu::class,
    ]);

    $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'neon');
  }
}
