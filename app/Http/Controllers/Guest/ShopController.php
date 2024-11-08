<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ShopController extends Controller
{
    protected $productRepo;

    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function index(Request $request)
    {
        $data = $this->productRepo->getProductsForShop($request);
        return view('shop', $data);
    }

    public function productDetails($product_slug)
    {
        $product = $this->productRepo->getProductBySlug($product_slug);
        $relatedProducts = $this->productRepo->getRelatedProducts($product_slug);
        return view('product-details', compact('product', 'relatedProducts'));
    }
}
