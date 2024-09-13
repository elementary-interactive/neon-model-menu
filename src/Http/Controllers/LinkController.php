<?php

namespace Neon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use Neon\Services\LinkService;

class LinkController extends Controller
{
    public function index(LinkService $service, Request $request)
    {
        return $this->show($service, $request, null);
    }

    public function show(LinkService $service, Request $request, string $slug = null)
    {
        if ($slug == app('site')->current()->locale)
        {
            $slug = null;
        }

        if (!$slug)
        {
            $page = $service->index();
        }
        else
        {
            $slug = $service::cleanup_slug($slug);
            
            /** Geting the current page.
             * 
             * @var  mixed $page
             */
            $page = $service->find($slug);
        }

        return View::first(
            $service->getViews(Arr::first(app('site')->current()->domains)),
            [ // Data to render
                'page' => $page
            ]
        );
    }

}