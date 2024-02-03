<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Neon\Models\Traits\Uuid;
use Neon\Models\Traits\Statusable;
use Neon\Models\Basic as BasicModel;
use Neon\Site\Models\Traits\SiteDependencies;

class Menu extends BasicModel
{
  use SoftDeletes;
  use Uuid;
  use SiteDependencies;
  use Statusable;

  protected $fillable = [
    'title', 'slug', 'status'
  ];

  /** The attributes that should be handled as date or datetime.
   *
   * @var array
   */
  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at'
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

  public function items()
  {
    return $this->hasMany(\Neon\Models\MenuItem::class);
  }
}
