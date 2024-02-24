<?php

namespace Neon\Admin\Resources\MenuResource\Pages;

use Neon\Admin\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMenus extends ManageRecords
{
  protected static string $resource = MenuResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->slideOver(),
    ];
  }
}
