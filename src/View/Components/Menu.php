<?php

namespace Neon\View\Components;

use Illuminate\View\Component;
use Neon\Services\MenuService;

class Menu extends Component
{

  /**
   * The Menu Service to handle Menu placeholders
   * 
   * @var MenuService
   */
  private $service;

  /**
   * The id - human readable slug - of the menu.
   *
   * @var string
   */
  public $id;

  /**
   * Create the component instance.
   *
   * @param  \Neon\Services\MenuService $service
   * @param  string  $id
   * 
   * @return void
   */
  public function __construct(MenuService $service, $id)
  {
    $this->service = $service;

    $this->id = $id;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|\Closure|string
   */
  public function render()
  {
    $x = $this->service->findMenu($this->id);
    $y = \Site::current();
    dd($x, $y);
    return view('components.alert');
  }
}
