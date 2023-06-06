<?php

namespace Neon\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LinkService
{
    /** The called slug.
     * 
     * @var string
     */
    protected $slug = null;

    /** The selected page, represents by a link object.
     * 
     * @var  
     */
    protected $page;

    function getViews(string $host = null): array
    {
        $templates = [];

        $slug = str_replace('/', '.', $this->slug);

        if ($host)
        {
            $host = Str::slug($host);

            $templates[] = "{$host}.pages.static.{$slug}"; // 1a
            if ($this->page->template)
            {
                $templates[] = "{$host}.pages.static.{$this->page->template}"; // 1b
            }
            $templates[] = "{$host}.pages.{$slug}"; // 2a
            $templates[] = "{$host}.pages.default"; // 2b
        }
        $templates[] = "web.pages.static.{$slug}"; // 3a
        if ($this->page->template) {
            $templates[] = "web.pages.static.{$this->page->template}"; // 3b
        }
        $templates[] = "web.pages.{$slug}"; // 4a
        if ($this->page->template) {
            $templates[] = "web.pages.{$this->page->template}"; // 4b
        }
        $templates[] = "web.pages.default"; // 5

        return $templates;
    }

    function find(string $slug)
    {
        $this->slug = $slug;

        $model = config('neon.link.model', \Neon\Models\Link::class);

        $this->page = $model::whereUrl(Str::start($this->slug, "/"))
            ->whereHas('site', function($q) {
                $q->where('sites.id', app('site')->current()->id);
            })
            ->firstOrFail();

        return $this->page;
    }

    
    function static(string $slug)
    {
        $this->slug = $slug;

        $model = config('neon.link.model', \Neon\Models\Link::class);

        $this->page = new $model();
        $this->page->slug = $slug;
        $this->page->og_description = '';
        $this->page->locale = app()->getLocale();

        return $this->page;
    }
}
