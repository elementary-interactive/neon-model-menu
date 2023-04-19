<?php

namespace Neon\Models;

use Illuminate\Database\Eloquent\Model;
use Neon\Attributable\Models\Traits\Attributable;
use Neon\Models\Traits\Uuid;

use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;


class Content extends Model
{
    use Attributable;
    use Uuid;

    use HasFlexible;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content' => \Whitecube\NovaFlexibleContent\Value\FlexibleCast::class
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content'
    ];

    public function getFlexibleContentAttribute()
    {
        return $this->flexible('content');
    }
    /**
     *
     */
    public function link()
    {
        return $this->belongsTo(\Neon\Models\Link::class, 'link_id');
    }
}