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
    $this->menus = $this->class::with('links')->get();
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

  public function getViews(string $slug = ''): array
  {
    return [
      \Site::current()->slug . '.components.navigation_' . $slug,
      \Site::current()->slug . '.components.navigation',
      'components.navigation_' . $slug,
      'components.navigation',
      'neon::menu'
    ];
  }
}
