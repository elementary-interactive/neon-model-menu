<?php

namespace Neon;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Http\Kernel;
use Neon\View\Components\Menu;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;


class NeonMenuServiceProvider extends PackageServiceProvider
{
  const VERSION = '3.0.0-alpha-10';

  public function configurePackage(Package $package): void
  {
    AboutCommand::add('N30N', 'Menu', self::VERSION);

    $package
      ->name('neon-menu')
      ->hasConfigFile()
      ->hasViews()
      ->hasRoutes('web')
      ->hasViewComponent('neon-menu', Menu::class)
      ->hasMigrations([
        'create_menus_table',
        'create_menu_items_table',
        'create_links_table',
        'add_index_to_links_table',
        'add_status_to_menu_items_table'
      ]);
  }


  // /** Bootstrap any application services.
  //  *
  //  * @param \Illuminate\Contracts\Http\Kernel  $kernel
  //  *
  //  * @return void
  //  */
  // public function boot(Kernel $kernel): void
  // {
  //   $this->publishes([
  //     __DIR__.'/../config/config.php'   => config_path('neon.php'),
  //   ], 'neon-config');

  //   if ($this->app->runningInConsole()) {
  //     $migrations = [];

  //     if (!class_exists('CreateMenusTable')) {
  //       $migrations[__DIR__ . '/../database/migrations/create_menus_table.php.stub'] = database_path('migrations/'.date('Y_m_d').'_000001_create_menus_table.php');
  //     } 
  //     if (!class_exists('CreateLinksTable')) {
  //       $migrations[__DIR__ . '/../database/migrations/create_links_table.php.stub'] = database_path('migrations/'.date('Y_m_d').'_000002_create_links_table.php');
  //     }
  //     if (!class_exists('CreateMenuItemTable')) {
  //       $migrations[__DIR__ . '/../database/migrations/create_menu_item_table.php.stub'] = database_path('migrations/'.date('Y_m_d').'_000003_create_menu_item_table.php');
  //     }

  //     $this->publishes($migrations, 'neon-migrations');
  //   }

  //   $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

  //   $this->loadViewComponentsAs('neon', [
  //     Menu::class,
  //   ]);

  //   $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'neon');
  // }
}
