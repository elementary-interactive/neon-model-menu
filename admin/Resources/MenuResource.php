<?php

namespace Neon\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Neon\Admin\Resources\Traits\NeonAdmin;
use Neon\Admin\Resources\MenuResource\Pages;
use Neon\Admin\Resources\MenuResource\RelationManagers;
use Neon\Attributable\Models\Attribute;
use Neon\Models\Menu;
use Neon\Models\Scopes\ActiveScope;
use Neon\Models\Statuses\BasicStatus;
use Neon\Site\Models\Scopes\SiteScope;
use Neon\Site\Models\Site;

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
      Fieldset::make(__('neon-admin::admin.resources.menu.form.fieldset.name'))
        ->schema([
          TextInput::make('title')
            ->label(__('neon-admin::admin.resources.menu.form.fields.title.label'))
            ->afterStateUpdated(function ($get, $set, ?string $state) {
              if (!$get('is_slug_changed_manually') && filled($state)) {
                $set('slug', Str::slug($state));
              }
            })
            ->reactive()
            ->required()
            ->maxLength(255),
          TextInput::make('slug')
            ->label(__('neon-admin::admin.resources.menu.form.fields.slug.label'))
            ->afterStateUpdated(function (Forms\Set $set) {
              $set('is_slug_changed_manually', true);
            })
            ->required(),
          Forms\Components\Hidden::make('is_slug_changed_manually')
            ->default(false)
            ->dehydrated(false),
        ])
        ->columns(2),
      Select::make('status')
        ->label(__('neon-admin::admin.resources.menu.form.fields.status.label'))
        ->required()
        ->reactive()
        ->native(false)
        ->default(BasicStatus::default())
        ->options(BasicStatus::class),
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
          ->link()
          ->url(fn (Menu $record): string => '/admin/menu-items?activeTab=' . $record->slug),
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
