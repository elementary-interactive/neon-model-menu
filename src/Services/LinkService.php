<?php

namespace Neon\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Neon\Models\Link;

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

  public static final function cleanup_slug(string $slug): string
  {
    $new_slug = '';

    if (class_exists(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::class)) 
    {
      try {
        $new_slug = implode('/', array_diff(explode('/', $slug), [\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale()]));
      }
      catch (\Exception $e)
      {
          //    ^ _ ^
      }
    }

    return $new_slug;
  }

  function getViews(string $host = null): array
  {
    $templates = [];

    $slug = str_replace('/', '.', $this->slug);

    if ($host) {
      $host = Str::slug($host);

      $templates[] = "{$host}.pages.static.{$slug}"; // 1a
      if ($this->page->template) {
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

  function find(string $slug): Link
  {
    $this->slug = $slug; // The slug for we what querying...

    /** Get the link model's class to query that.
     * @var string Link model's class.
     */
    $link = config('neon.link.model', \Neon\Models\Link::class);

    $this->page = $link::whereHas('menus', function($query) use ($slug) {
      $query->where('url', Str::start($slug, "/"))
        ->orWhere('url', Str::start(app('site')->current()->locale, '/').Str::start($slug, "/"));
    })
      ->first();

    /** If the URL (by slug) not found in the menu, we try to find it 
     * directly in links.
     */
    if (!$this->page)
    {
      $this->page = $link::whereUrl(Str::start($this->slug, "/"))
        ->firstOrFail();
    }

    return $this->page;
  }

  function index(): Link
  {
    $link = config('neon.link.model', \Neon\Models\Link::class);

    /** If the URL (by slug) not found in the menu, we try to find it 
     * directly in links.
     */
    $this->page = $link::whereIsIndex(true)
      ->firstOrFail();
    
      return $this->page;
  }


  function static(string $slug): Link
  {
    $this->slug = $slug;

    $model = config('neon.link.model', \Neon\Models\Link::class);

    $this->page = new $model();
    $this->page->slug           = $slug;
    $this->page->og_description = '';
    $this->page->locale         = app()->getLocale();

    return $this->page;
  }
}
