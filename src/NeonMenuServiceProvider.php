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
  const VERSION = '3.0.0-alpha-18';

  public function configurePackage(Package $package): void
  {
    AboutCommand::add('N30N', 'Menu', self::VERSION);

    $package
      ->name('neon-menu')
      ->hasConfigFile()
      ->hasViews()
      ->hasRoutes('web')
      ->hasViewComponent('neon', Menu::class)
      ->hasMigrations([
        'create_links_table',
        'create_menus_table',
        'create_menu_items_table',
        'add_index_to_links_table',
        'add_status_to_menu_items_table'
      ]);
  }
}
