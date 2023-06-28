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
        return $this->show($service, $request, config('site.routes.index'));
    }

    public function show(LinkService $service, Request $request, string $slug)
    {
        // dd($service, $slug);
        /** Geting the current page.
         * 
         * @var  mixed $page
         */
        $page = $service->find($slug);

        // dd($page, $page->content->first());

        return View::first(
            $service->getViews(Arr::first(app('site')->current()->domains)),
            [ // Data to render
                'page' => $page
            ]
        );
    }

}