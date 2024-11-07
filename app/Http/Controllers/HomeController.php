<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::where('status',1)->get()->take(3);
        $categories = Category::orderBy('name')->get();
        $saleProducts = Product::whereNotNull('sale_price')->where('sale_price','<>','')->inRandomOrder()->get()->take(8);
        $featuredProducts = Product::where('featured',1)->get()->take(8);
        return view('index',compact('slides','categories','saleProducts','featuredProducts'));
    }
}
