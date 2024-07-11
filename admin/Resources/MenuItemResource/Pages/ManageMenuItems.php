<?php

namespace Neon\Admin\Resources\MenuItemResource\Pages;

use Neon\Admin\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Neon\Models\Statuses\BasicStatus;
use Neon\Models\Link;
use Neon\Models\Menu;

class ManageMenuItems extends ManageRecords
{
  protected static string $resource = MenuItemResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->slideOver(),
    ];
  }

  public function getTabs(): array
  {
    /** @var Collection Getting all the menus, to prefilter menu items.
     */
    $menus = Menu::withoutGlobalScopes()
      ->withoutTrashed()
      ->get();

    $result = [];

    foreach ($menus as $menu)
    {
      $result[$menu->slug] = ListRecords\Tab::make($menu->title)
        ->badge(fn() => implode(', ', $menu->site->pluck('title')->toArray()))
        ->query(fn ($query) => $query->where('menu_id', $menu->id));
    }

    return $result;
  }

  public function reorderTable(array $order): void
  {
      static::getResource()::getModel()::setNewOrder($order);
  }

  public function getDefaultActiveTab(): string|int|null
  {
    return Menu::withoutGlobalScopes()
    ->withoutTrashed()
    ->first()
    ?->slug;
  }

}
