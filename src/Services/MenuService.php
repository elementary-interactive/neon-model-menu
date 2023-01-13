<?php

namespace Neon\Services;

use Neon\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class MenuService
{

  private $menus;

  /** The class which will handle the menu instances
   * 
   * @var string
   */
  private $class;

  /** Get all menus to don't query the database whenever you ask for a menu item. Later we can cache this.
   * 
   */
  public function __construct()
  {
    $this->class = config('neon.menu.class', \Neon\Models\Menu::class);
    $this->menus = $this->class::all();
  }

  /** Get a menu item by slug.
   * 
   * @param string $slug
   * 
   * @return \Brightly\Mango\Menu 
   */
  public function findMenu(string $slug): ?\Neon\Models\Menu
  {
    return $this->menus
            ->where('slug', $slug)
            ->first();
  }

  public function templates(string $slug, string $path = null): array
  {
    /** Replace to custom path if given.
     */
    if (!is_null($path)) {
      return [$path];
    } else {
      /** The given locale.
       * @var string
       */
      $locale = \App::getLocale();

      return [
        /** Domain related component not yet supported!
         */
        "web.layouts.components.navigation_{$slug}",
        "components.navigation_{$slug}",
        "web.layouts.components.navigation",
        "components.navigation",
      ];
    }
  }
}
