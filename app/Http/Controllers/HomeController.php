<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService
    ) {}

    public function index(): View
    {
        $heroBanners = $this->catalogService->getActiveBanners('hero_main');
        $sideBanners = $this->catalogService->getActiveBanners('hero_side');
        $categories = $this->catalogService->getParentCategoriesWithChildren();
        $featuredProducts = $this->catalogService->getFeaturedProducts(8);
        $bestsellers = $this->catalogService->getBestsellers(8);
        $newArrivals = $this->catalogService->getNewArrivals(8);

        return view('storefront.home', compact(
            'heroBanners',
            'sideBanners',
            'categories',
            'featuredProducts',
            'bestsellers',
            'newArrivals'
        ));
    }
}

