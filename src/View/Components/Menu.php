<?php
 
namespace Neon\View\Components;
 
use Illuminate\View\Component;
use Neon\Services\MenuService;
 
class Menu extends Component
{
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
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct(MenuService $menuService, $type, $message)
    {
        $this->type = $type;
        $this->message = $message;
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