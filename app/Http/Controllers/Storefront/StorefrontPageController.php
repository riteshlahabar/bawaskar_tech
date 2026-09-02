<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Storefront\StorefrontPageRendererContract;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class StorefrontPageController extends Controller
{
    public function __construct(
        private readonly StorefrontPageRendererContract $pages
    ) {}

    public function home(Request $request): View
    {
        return $this->pages->render($request, 'index-5');
    }

    public function show(Request $request, string $page): View
    {
        abort_unless(in_array($page, config('storefront.pages', []), true), 404);

        return $this->pages->render($request, $page);
    }
}
