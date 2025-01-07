<?php

namespace Neon\Admin\Resources;

use Camya\Filament\Forms\Components\TitleWithSlugInput;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Neon\Admin\Resources\Traits\NeonAdmin;
use Neon\Admin\Resources\MenuResource\Pages;
use Neon\Admin\Resources\MenuResource\RelationManagers;
use Neon\Models\Menu;
use Neon\Models\Scopes\ActiveScope;
use Neon\Models\Statuses\BasicStatus;
use Neon\Site\Models\Scopes\SiteScope;

class MenuResource extends Resource
{
  use NeonAdmin;

  protected static ?int $navigationSort = 2;

  protected static ?string $model = Menu::class;

  protected static ?string $navigationIcon = 'heroicon-o-bars-3';

  protected static ?string $activeNavigationIcon = 'heroicon-s-bars-3';

  protected static ?string $recordTitleAttribute = 'title';

  // public static function getNavigationParentItem(): string
  // {
  //   return __('neon-admin::admin.resources.sites.title');
  // }

  public static function getNavigationLabel(): string
  {
    return __('neon-admin::admin.navigation.menu');
  }

  public static function getNavigationGroup(): string
  {
    return __('neon-admin::admin.navigation.web');
  }

  public static function getModelLabel(): string
  {
    return __('neon-admin::admin.models.menu');
  }

  public static function getPluralModelLabel(): string
  {
    return __('neon-admin::admin.models.menu');
  }

  public static function items(): array
  {
    $t = [
      Select::make('site')
        ->label(__('neon-admin::admin.resources.menu.form.fields.site.label'))
        ->multiple()
        ->relationship(titleAttribute: 'title'),
      TitleWithSlugInput::make(
        fieldTitle: 'title',
        titleLabel: __('neon-admin::admin.resources.menu.form.fields.title.label'),
        fieldSlug: 'slug',
        slugLabel: __('neon-admin::admin.resources.menu.form.fields.slug.label'),
        urlHostVisible: false
      ),
      Forms\Components\Section::make()
        ->columns([
          'sm' => 3,
          'xl' => 6,
          '2xl' => 9,
        ])
        ->schema([
          Forms\Components\Select::make('status')
            ->label(__('neon-admin::admin.resources.menu.form.fields.status.label'))
            ->required()
            ->reactive()
            ->native(false)
            ->default(BasicStatus::default())
            ->options(BasicStatus::class)
            ->columnSpan([
              'sm' => 1,
              'xl' => 2,
              '2xl' => 3,
            ])
        ])
    ];
    
    return $t;
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->label(__('neon-admin::admin.resources.menu.form.fields.title.label'))
          ->description(fn (Menu $record): string => "
          <x-neon-menu id=\"{$record->slug}\"> <x-slot:tools> ... </x-slot> </x-neon-menu>")
          ->copyable()
          ->copyableState(fn (Menu $record): string => "<x-neon-menu id=\"{$record->slug}\"></x-neon-menu>")
          ->searchable(),
        Tables\Columns\TextColumn::make('items.title')
          ->label(__('neon-admin::admin.resources.menu.form.fields.items.label'))
          ->listWithLineBreaks()
          ->bulleted()
          ->searchable(),
        Tables\Columns\TextColumn::make('site.title')
          ->label(__('neon-admin::admin.resources.menu.form.fields.site.label'))
          ->listWithLineBreaks()
          ->bulleted()
          ->searchable(),
        Tables\Columns\IconColumn::make('status')
          ->label(__('neon-admin::admin.resources.menu.form.fields.status.label'))
          ->icon(fn (BasicStatus $state): string => match ($state) {
            BasicStatus::New      => 'heroicon-o-sparkles',
            BasicStatus::Active   => 'heroicon-o-check-circle',
            BasicStatus::Inactive => 'heroicon-o-x-circle',
          })
          ->color(fn (BasicStatus $state): string => match ($state) {
            BasicStatus::New      => 'gray',
            BasicStatus::Active   => 'success',
            BasicStatus::Inactive => 'danger',
          })
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('deleted_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('site')
          ->label(__('neon-admin::admin.resources.menu.form.fields.site.label'))
          ->relationship('site', 'title'),
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\Action::make('redirect_to_menu_items')
          ->label(__('neon-admin::admin.resources.menu.actions.items'))
          ->url(fn (Menu $record): string => '/admin/menu-items?activeTab=' . $record->id),
        Tables\Actions\EditAction::make()
          ->slideOver(),
        Tables\Actions\DeleteAction::make(),
        Tables\Actions\ForceDeleteAction::make(),
        Tables\Actions\RestoreAction::make(),
      ])
      ->bulkActions(self::bulkActions());
  }

  public static function getRelations(): array
  {
    return [
      RelationManagers\ItemsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageMenus::route('/'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        ActiveScope::class,
        SiteScope::class,
        SoftDeletingScope::class
      ]);
  }
}
