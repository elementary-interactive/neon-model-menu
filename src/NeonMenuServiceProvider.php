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
    $this->loadViewComponentsAs('neon', [
      Menu::class,
    ]);

    // dd($this);

    $this->loadViewsFrom(__DIR__ . '/../resources/views/components', 'neon');
  }
}
