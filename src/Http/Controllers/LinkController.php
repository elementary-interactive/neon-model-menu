<?php

namespace Neon\Http\Controllers;

use Illuminate\Http\Request;
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
        /** Geting the current page.
         * 
         * @var  \Neon\Models\Link $page
         */
        $page = $service->find($slug);

        return View::first(
            $service->getViews($request->getHost()),
            [ // Data to render
                'page' => $page
            ]
        );
    }

}