<?php

namespace Neon\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Neon\Admin\Resources\Traits\NeonAdmin;
use Neon\Admin\Resources\MenuItemResource\Pages;
use Neon\Admin\Resources\SiteResource\RelationManagers;
use Neon\Attributable\Models\Attribute;
use Neon\Models\Link;
use Neon\Models\Menu;
use Neon\Models\MenuItem;
use Neon\Models\Scopes\ActiveScope;
use Neon\Models\Statuses\BasicStatus;
use Neon\Site\Models\Scopes\SiteScope;
use Neon\Site\Models\Site;

class MenuItemResource extends Resource
{
  use NeonAdmin;

  protected static ?int $navigationSort = 3;

  protected static ?string $model = MenuItem::class;

  // protected static ?string $navigationIcon = 'heroicon-o-bars-3';

  // protected static ?string $activeNavigationIcon = 'heroicon-s-bars-3';

  protected static ?string $recordTitleAttribute = 'title';

  public static function getNavigationParentItem(): string
  {
    return __('neon-admin::admin.navigation.menu');
  }

  public static function getNavigationLabel(): string
  {
    return __('neon-admin::admin.navigation.menu_item');
  }

  public static function getNavigationGroup(): string
  {
    return __('neon-admin::admin.navigation.web');
  }

  public static function getModelLabel(): string
  {
    return __('neon-admin::admin.models.menu_item');
  }

  public static function getPluralModelLabel(): string
  {
    return __('neon-admin::admin.models.menu_item');
  }

  public static function tabs(): array
  {
    return [];
  }

  public static function items(): array
  {
    $t = [
      Select::make('menu_id')
        ->relationship(
          name: 'menu',
          titleAttribute: 'title',
          modifyQueryUsing: fn (Builder $query) => $query->withoutGlobalScopes()->withoutTrashed(),
        )
        ->label(trans('neon-admin::admin.resources.menu-item.form.fields.menu.label'))
        ->default(fn (Request $request) => Menu::where('slug', $request->get('activeTab'))->withoutGlobalScopes()->first()?->id)
        ->searchable()
        ->reactive()
        ->required(),
      Select::make('link_id')
        ->label(trans('neon-admin::admin.resources.menu-item.form.fields.link.label'))
        ->relationship(
          name: 'link',
          titleAttribute: 'title',
          modifyQueryUsing: function (Get $get, Builder $query) {
            $query->withoutGlobalScopes()->withoutTrashed();

            if ($get('menu_id')) {
              $sites = Menu::withoutGlobalScopes()->find($get('menu_id'))->site;

              foreach ($sites->pluck('id') as $index => $site_id) {
                if ($index == 0) {
                  $query->whereRelation('site', 'id', '=', $site_id);
                } else {
                  $query->orWhereRelation('site', 'id', '=', $site_id);
                }
              }
            }
          },
        )
        ->preload()
        ->getSearchResultsUsing(function (Get $get) {

          $link = Link::withoutGlobalScopes()->withoutTrashed();

          if ($get('menu_id')) {
            $sites = Menu::withoutGlobalScopes()->find($get('menu_id'))->site;

            foreach ($sites->pluck('id') as $index => $site_id) {
              if ($index == 0) {
                $link->whereRelation('site', 'id', '=', $site_id);
              } else {
                $link->orWhereRelation('site', 'id', '=', $site_id);
              }
            }
          }
          /** Go.
           * 
           */
          return $link?->pluck('title', 'id')?->toArray();
        })
        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
          if (!$get('is_slug_changed_manually') && filled($state)) {
            $link = Link::withoutGlobalScopes()->find($state);

            $set('title', $link->title);
            $set('slug', $link->slug);
          }
        })
        // ->options(fn (Get $get): array => Link::whereRelation('site', 'id', Menu::find($get('menu'))?->withoutGlobalScopes()?->first()?->site?->id)?->withoutGlobalScopes()?->withoutTrashed()?->pluck('title', 'id')?->unique()?->toArray())
        ->requiredIf('is_outside', 0)
        ->searchable(),
      Forms\Components\Toggle::make('is_outside')
        ->onIcon('heroicon-o-arrow-top-right-on-square')
        ->label(trans('neon-admin::admin.resources.menu-item.form.fields.is_outside.label'))
        ->helperText(trans('neon-admin::admin.resources.menu-item.form.fields.is_outside.help')),
      Select::make('status')
        ->label(__('neon-admin::admin.resources.menu-item.form.fields.status.label'))
        ->required()
        ->reactive()
        ->native(false)
        ->default(BasicStatus::default())
        ->options(BasicStatus::class),

      Forms\Components\Section::make(trans('neon-admin::admin.resources.menu-item.form.fieldset.name'))
        ->schema([
          TextInput::make('title')
            ->label(trans('neon-admin::admin.resources.menu-item.form.fields.title.label'))
            ->afterStateUpdated(function ($get, $set, ?string $state) {
              if (!$get('is_slug_changed_manually') && filled($state) && !$get('is_outside')) {
                $set('slug', Str::slug($state));
              }
            })
            ->reactive()
            ->required()
            ->maxLength(255),
          Select::make('target')
            ->label(__('neon-admin::admin.resources.menu-item.form.fields.target.label'))
            ->options([
              MenuItem::TARGET_SELF   => __('neon-admin::admin.resources.menu-item.form.fields.target.options.self'),
              MenuItem::TARGET_BLANK  => __('neon-admin::admin.resources.menu-item.form.fields.target.options.blank'),
            ])
            ->default(MenuItem::TARGET_SELF)
            ->native(false),
          TextInput::make('url')
            ->label(__('neon-admin::admin.resources.menu-item.form.fields.slug.label'))
            ->helperText(__('neon-admin::admin.resources.menu-item.form.fields.slug.help'))
            ->afterStateUpdated(function (Set $set) {
              $set('is_slug_changed_manually', true);
            })
            ->required(),
        ])
        ->columns(2),
      Repeater::make('children')
        ->relationship()
        ->label(__('neon-admin::admin.resources.menu-item.form.fields.children.label'))
        ->addActionLabel(__('neon-admin::admin.resources.menu-item.form.fields.children.add'))
        ->schema([
          Select::make('link_id')
            ->label(trans('neon-admin::admin.resources.menu-item.form.fields.link.label'))
            ->relationship(
              name: 'link',
              titleAttribute: 'title',
              modifyQueryUsing: function (Get $get, Builder $query) {
                $query->withoutGlobalScopes()->withoutTrashed();

                if ($get('menu_id')) {
                  $sites = Menu::withoutGlobalScopes()->find($get('menu_id'))->site;

                  foreach ($sites->pluck('id') as $index => $site_id) {
                    if ($index == 0) {
                      $query->whereRelation('site', 'id', '=', $site_id);
                    } else {
                      $query->orWhereRelation('site', 'id', '=', $site_id);
                    }
                  }
                }
              },
            )
            ->preload()
            ->getSearchResultsUsing(function (Get $get) {

              $link = Link::withoutGlobalScopes()->withoutTrashed();

              if ($get('menu_id')) {
                $sites = Menu::withoutGlobalScopes()->find($get('menu_id'))->site;

                foreach ($sites->pluck('id') as $index => $site_id) {
                  if ($index == 0) {
                    $link->whereRelation('site', 'id', '=', $site_id);
                  } else {
                    $link->orWhereRelation('site', 'id', '=', $site_id);
                  }
                }
              }
              /** Go.
               * 
               */
              return $link?->pluck('title', 'id')?->toArray();
            })
            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
              if (!$get('is_slug_changed_manually') && filled($state)) {
                $link = Link::withoutGlobalScopes()->find($state);

                $set('title', $link->title);
                $set('slug', $link->slug);
              }
            })
            // ->options(fn (Get $get): array => Link::whereRelation('site', 'id', Menu::find($get('menu'))?->withoutGlobalScopes()?->first()?->site?->id)?->withoutGlobalScopes()?->withoutTrashed()?->pluck('title', 'id')?->unique()?->toArray())
            ->requiredIf('is_outside', 0)
            ->searchable(),
          Forms\Components\Toggle::make('is_outside')
            ->onIcon('heroicon-o-arrow-top-right-on-square')
            ->label(trans('neon-admin::admin.resources.menu-item.form.fields.is_outside.label'))
            ->helperText(trans('neon-admin::admin.resources.menu-item.form.fields.is_outside.help')),
          Select::make('status')
            ->label(__('neon-admin::admin.resources.menu-item.form.fields.status.label'))
            ->required()
            ->reactive()
            ->native(false)
            ->default(BasicStatus::default())
            ->options(BasicStatus::class),
          Forms\Components\Section::make(trans('neon-admin::admin.resources.menu-item.form.fieldset.name'))
            ->schema([
              TextInput::make('title')
                ->label(trans('neon-admin::admin.resources.menu-item.form.fields.title.label'))
                ->required()
                ->maxLength(255),
              Select::make('target')
                ->label(__('neon-admin::admin.resources.menu-item.form.fields.target.label'))
                ->options([
                  MenuItem::TARGET_SELF   => __('neon-admin::admin.resources.menu-item.form.fields.target.options.self'),
                  MenuItem::TARGET_BLANK  => __('neon-admin::admin.resources.menu-item.form.fields.target.options.blank'),
                ])
                ->default(MenuItem::TARGET_SELF)
                ->native(false),
              TextInput::make('url')
                ->label(trans('neon-admin::admin.resources.menu-item.form.fields.slug.label'))
                ->helperText(trans('neon-admin::admin.resources.menu-item.form.fields.slug.help'))
                ->afterStateUpdated(function (Set $set) {
                  $set('is_slug_changed_manually', true);
                })
                ->required(),
            ])
            ->columns(2)
        ])
        ->orderColumn('order')
        ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Get $get): array {
          $data['menu_id'] = $get('../../menu_id');

          return $data;
        })
        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
        ->cloneable()
        ->collapsible()
        ->collapsed(),
    ];
    return $t;
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('title')
          ->label(__('neon-admin::admin.resources.menu-item.form.fields.title.label'))
          ->description(fn (MenuItem $record): string => $record->href)
          ->searchable(),
        // Tables\Columns\TextColumn::make('site.title')
        //   ->label(__('neon-admin::admin.resources.menu-item.form.fields.site.label'))
        //   ->listWithLineBreaks()
        //   ->bulleted()
        //   ->searchable(),
        // Tables\Columns\IconColumn::make('status')
        //   ->label(__('neon-admin::admin.resources.menu-item.form.fields.status.label'))
        //   ->icon(fn (BasicStatus $state): string => match ($state) {
        //       BasicStatus::New      => 'heroicon-o-sparkles',
        //       BasicStatus::Active   => 'heroicon-o-check-circle',
        //       BasicStatus::Inactive => 'heroicon-o-x-circle',
        //   })
        //   ->color(fn (BasicStatus $state): string => match ($state) {
        //       BasicStatus::New      => 'gray',
        //       BasicStatus::Active   => 'success',
        //       BasicStatus::Inactive => 'danger',
        //   })
        //   ->searchable()
        //   ->sortable(),
        Tables\Columns\TextColumn::make('children.title'),
        Tables\Columns\TextColumn::make('link.title'),
        // Tables\Columns\TextColumn::make('order', '#')
        //   ->toggleable()
        //   ->sortable(),
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
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\EditAction::make()
          ->slideOver(),
        Tables\Actions\DeleteAction::make(),
        Tables\Actions\ForceDeleteAction::make(),
        Tables\Actions\RestoreAction::make(),
      ])
      ->reorderable('order')
      ->defaultSort('order')
      ->bulkActions(self::bulkActions());
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageMenuItems::route('/'),
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
