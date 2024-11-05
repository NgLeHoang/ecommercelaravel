<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id','DESC')->paginate(12);
        return view('shop',compact('products'));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug',$product_slug)->first();
        $relatedProducts = Product::where('slug','<>',$product_slug)->get()->take(8);
        return view('product-details',compact('product','relatedProducts'));
    }
}
