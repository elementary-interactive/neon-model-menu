<?php

namespace Neon\Services;

use Neon\Models\Link;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Neon\Models\Menu;

class LinkService
{
    /** The called slug.
     * 
     * @var string
     */
    protected $slug = null;

    /** The selected page, represents by a link object.
     * 
     * @var \Neon\Models\Link
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

    function find(string $slug): ?Link
    {
        if (count(app('site')->current()->prefixes))
        {
            foreach (app('site')->current()->prefixes as $prefix)
            {
                if (Str::startsWith($slug, $prefix))
                {
                    $this->slug = Str::replace($prefix, '', $slug);
                }
            }
        }

        if (is_null($this->slug))
        {
            $this->slug = $slug;
        }

        $this->page = Link::whereUrl(Str::start($this->slug, "/"))
            ->whereHas('menu', function($q) {
                $q->whereHas('site', function($q) {
                    $q->where('sites.id', app('site')->current()->id);
                });
            })
            ->firstOrFail();

        return $this->page;
    }

    
    function static(string $slug): Link
    {
        $this->slug = $slug;

        $this->page = new Link();
        $this->page->slug = $slug;
        $this->page->og_description = '';
        $this->page->locale = app()->getLocale();

        return $this->page;
    }
}
