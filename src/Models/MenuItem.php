<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Neon\Models\Traits\Uuid;
use Neon\Models\Basic as BasicModel;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MenuItem extends BasicModel implements Sortable
{
  use SoftDeletes;
  use SortableTrait;
  use Uuid;

  const TARGET_SELF   = "_self";
  const TARGET_BLANK  = "_blank";

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'title', 'order',
  ];

  /** The attributes that should be handled as date or datetime.
   *
   * @var array
   */
  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
  ];
  
  /** The model's default values for attributes.
   *
   * @var array
   */
  protected $attributes = [
    'target'    => self::TARGET_SELF,
  ];

  /** Set up sorting.
   *
   * @var array
   */
  public $sortable = [
      'order_column_name'     => 'order',
      'sort_when_creating'    => true,
      'sort_on_has_many'      => true,
  ];

  /** Extending the boot, to be able to set Observer this model, as because
   * the Observer will not run on the inherited classes.
   *
   * @see https://github.com/laravel/framework/issues/25546
   * @see https://laravel.com/docs/6.x/eloquent#global-scopes
   */
  protected static function boot()
  {
    /** We MUST call the parent boot method  in this case the:
     *      \Illuminate\Database\Eloquent\Model
     */
    parent::boot();
  }

  public function menu(): BelongsTo
  {
    return $this->belongsTo(\Neon\Models\Menu::class);
  }

  public function link(): BelongsTo
  {
    return $this->belongsTo(\Neon\Models\Link::class);
  }
  
  public function buildSortQuery()
  {
      return static::query()
          ->where('menu_id', $this->menu_id)
          ->where('parent_id', $this->parent_id);
  }

  public function getHrefAttribute(): string
  {
    return $this->url ?: $this->link->url;
  }

  public function setUrlAttribute(string $value)
  {
    $this->attributes['url'] = Str::start($value, "/");
  }
}
