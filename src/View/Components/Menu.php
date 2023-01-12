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
     * The alert type.
     *
     * @var string
     */
    public $type;
 
    /**
     * The alert message.
     *
     * @var string
     */
    public $message;
 
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
        // $this->slug = $slug;
    }
 
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.alert');
    }
}