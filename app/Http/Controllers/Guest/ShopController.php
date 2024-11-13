<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ShopController extends Controller
{
    /**
     * Repository for handling product data operations.
     *
     * @var \App\Repositories\Contracts\ProductRepositoryInterface
     */
    protected $productRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Contracts\ProductRepositoryInterface $productRepo
     */
    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    /**
     * Display the shop page with filtered products.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $data = $this->productRepo->getProductsForShop($request);
        return view('shop', $data);
    }

    /**
     * Display the product details page.
     *
     * @param string $product_slug
     * @return \Illuminate\View\View
     */
    public function productDetails($product_slug)
    {
        $product = $this->productRepo->getProductBySlug($product_slug);
        $relatedProducts = $this->productRepo->getRelatedProducts($product_slug);
        return view('product-details', compact('product', 'relatedProducts'));
    }
}
