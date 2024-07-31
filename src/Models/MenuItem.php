<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Neon\Models\Basic as BasicModel;
use Neon\Models\Statuses\BasicStatus;
use Neon\Models\Traits\Statusable;
use Neon\Models\Traits\Uuid;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MenuItem extends BasicModel implements Sortable
{
	use SoftDeletes;
	use SortableTrait;
	use Statusable;
	use Uuid;

	const TARGET_SELF = "_self";
	const TARGET_BLANK = "_blank";
	/** Set up sorting.
	 *
	 * @var array
	 */
	public $sortable = [
		'order_column_name'  => 'order',
		'sort_when_creating' => true,
		'sort_on_has_many'   => true,
	];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title',
		'order',
		'url',
		'target',
		'menu_id',
		'link_id',
		'is_outside',
		'status',
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
		'target'     => self::TARGET_SELF,
		'is_outside' => false,
	];
	protected $casts = [
		'is_outside' => 'boolean',
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

	public function parent(): BelongsTo
	{
		return $this->belongsTo(\Neon\Models\MenuItem::class);
	}

	public function children(): HasMany
	{
		return $this->hasMany(\Neon\Models\MenuItem::class, 'parent_id');
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
		if (!$this->is_outside && !Str::startsWith($value, ["http", "https"])) {
			$this->attributes['url'] = Str::start($value, "/");
		} else {
			$this->attributes['url'] = $value;
		}
	}
}
