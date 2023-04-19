<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\Model;
use Neon\Attributable\Models\Traits\Attributable;
use Neon\Models\Traits\Uuid;

class Content extends Model
{
    use Attributable;
    use Uuid;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content' => 'json'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content'
    ];

    public function __construct()
    {
        $this->casts['content'] = class_exists(\Whitecube\NovaFlexibleContent\Value\FlexibleCast::class) ? \Whitecube\NovaFlexibleContent\Value\FlexibleCast::class : 'json';
    }

    /**
     *
     */
    public function link()
    {
        return $this->belongsTo(\Neon\Models\Link::class, 'link_id');
    }
}