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
    $this->publishes([
      __DIR__.'/../config/config.php'   => config_path('neon.php'),
    ], 'neon-menu');

    if ($this->app->runningInConsole()) {
      $migrations = [];

      if (!class_exists('CreateMenusTable')) {
        $migrations[__DIR__ . '/../database/migrations/create_menus_pivot.php.stub'] = database_path('migrations/000001_create_menus_table.php');
      } 
      if (!class_exists('CreateLinksTable')) {
        $migrations[__DIR__ . '/../database/migrations/create_links_table.php.stub'] = database_path('migrations/000002_create_links_table.php');
      }
      if (!class_exists('CreateMenuItemTable')) {
        $migrations[__DIR__ . '/../database/migrations/create_menu_item_table.php.stub'] = database_path('migrations/000003_create_menu_item_table.php');
      }
      // if (!class_exists('CreateContentsTable')) {
      //   $migrations[__DIR__ . '/../database/migrations/create_contents_table.php.stub'] = database_path('migrations/000003_create_contents_table.php');
      // }

      $this->publishes($migrations, 'neon-menu');
    }

    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    $this->loadViewComponentsAs('neon', [
      Menu::class,
    ]);

    $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'neon');
  }
}
