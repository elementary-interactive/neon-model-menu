<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
// use Spatie\EloquentSortable\{
//     Sortable,
//     SortableTrait
// };

class Link extends Model // implements Sortable
{
    use Uuid;
    use SoftDeletes;
    // use SortableTrait;

    const METHOD_GET    = "GET";
    const METHOD_POST   = "POST";
    const METHOD_PUSH   = "PUSH";
    const METHOD_PATCH  = "PATCH";
    const METHOD_DELETE = "DELETE";

    const TARGET_SELF   = "_self";
    const TARGET_BLANK  = "_blank";

    const OG_TYPE       = "website";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'slug', 'status', 'order'];

    /** The attributes that should be set to authenticated user by default.
     *
     * @var array
     **/
    public $users = [
        'created_by', 'updated_by', 'deleted_by', 'pulished_by'
    ];

    /** The attributes that should be handled as date or datetime.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 'published_at', 'expire_at',
    ];

    /** The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status'    => self::STATUS_DEFAULT,
        'method'    => self::METHOD_GET,
        'target'    => self::TARGET_SELF,
    ];

    /** Cast attribute to array...
     *
     */
    protected $casts = [
        'parameters'    => 'array'
    ];

    /** Set up sorting.
     *
     * @var array
     */
    public $sortable = [
        'order_column_name'     => 'order',
        'sort_when_creating'    => true,
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

        /** Add global scope to select by default only items where the status
         * is Active.
         */
        // static::addGlobalScope(new \Brightly\Mango\Scopes\ActiveScope);

        /** Add global scope to select by default only items where published
         * that set earlier date than current date and expired date is not set
         * or it is in the future.
         */
        // static::addGlobalScope(new \Brightly\Mango\Scopes\PublishedScope);

        static::saving(function(Link $model)
        {
            /** Handling URL field: slug is only for the given link, the URL will
             * contain all the generated slugs.
             *
             */
            if (!is_null($model->parent_id))
            {
                $model->url     = Str::start(DB::table($model->getTable())->where('id', $model->parent_id)->pluck('url')->first().'/'.$model->slug, '/');
                $model->menu_id = DB::table($model->getTable())->where('id', $model->parent_id)->pluck('menu_id')->first();
            }
            else
            {
                $model->url = Str::start($model->slug, '/');
            }
        });

        static::saved(function(Link $model)
        {
            /** The kids aren't alrgiht
             * ...so we check them.
             */
            self::refreshUrl($model);
        });

        static::created(function(Link $model)
        {
            /** Check wether content is needed or not.
             */
            self::checkContent($model);
        });
    }

    /** Fix the given link item's children by calling save method on them.
     *
     * @param \Brightly\Mango\Models\Link $model
     */
    public static function refreshUrl(Link $model): void
    {
        if ($model->children()->count())
        {
            foreach ($model->children()->get() as $item)
            {
                /** Being `url` field updated, as it is inherited by the ancestor,
                 * by the class' `saving` event handler. After saving, the `saved`
                 * event handler will the updater for this items' children.
                 */
                $item->save();
            }
        }
    }

    public static function checkContent(Link $model): void
    {
        // if (!$model->route && !$model->link)
        // {
        //     $model->content()->save(new \Brightly\Mango\Models\Content([
        //         'locale'        => ($model->menu()->first()) ? $model->menu()->first()->locale : config('app.locale'),
        //         'title'         => $model->title,
        //         'content'       => '',
        //         'status'        => $model->status,
        //         'published_at'  => $model->published_at,
        //         'published_by'  => $model->published_by,
        //         'expire_at'     => $model->expire_at,
        //         'site_id'       => (property_exists($model, 'site_id')) ? $model->site_id : null
        //     ]));
        // }
    }

    /** The parent menu identifier where this link belongs.
     *
     */
    public function menu()
    {
        return $this->belongsTo(\Neon\Models\Menu::class);
    }

    /** The content where to this link points.
     *
     */
    // public function content()
    // {
    //     return $this->morphOne(\Brightly\Mango\Models\Content::class, 'contentable');
    // }

    /** The parent of the given menu item, for multi level navigation.
     *
     */
    public function parent()
    {
        return $this->belongsTo(Link::class, 'parent_id');
    }

    /** Children in a multi level navigation.
     *
     */
    public function children()
    {
        return $this->hasMany(Link::class, 'parent_id', 'id');
    }


    public function getHrefAttribute(): string
    {
        /** Define "href" attribute, what is basically the url fieild's content.
         *
         * @var string
         */
        $href = $this->url ?: '';

        if (!is_null($this->route))
        {
            $href = route($this->route, $this->params);
        }

        return $href;
    }

    // public function getOgDataAttribute(): array
    // {
    //     $og = [
    //         'title'
    //             => $this->og_title ?? $this->title,
    //         'description'
    //             => $this->og_description ?? $this->content->description,
    //         'image'
    //             => \Storage::disk('public')->url($this->og_image),
    //         'locale'
    //             => $this->locale ?? $this->content->locale,
    //         'type'
    //             => self::OG_TYPE,
    //         'url'
    //             => ($this->slug == config('mango.routes.index')) ? url('/') : url($this->href),
    //     ];

    //     return $og;
    // }


    public function buildSortQuery()
    {
        return static::query()
            ->where('parent_id', $this->parent_id);
    }
}
