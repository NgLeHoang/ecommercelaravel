<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size') ? $request->query('size') : 12;
        $order = $request->query('order') ? $request->query('order') : -1;
        $order_column = "";
        $order_option = "";
        $filter_brands = $request->query('brands');
        $filter_categories = $request->query('categories');
        $min_price = $request->query('min') ? $request->query('min') : 1;
        $max_price = $request->query('max') ? $request->query('max') : 5000;
        switch ($order)
        {
            case 1:
                $order_column = 'sale_price';
                $order_option = 'ASC';
                break;
            case 2:
                $order_column = 'sale_price';
                $order_option = 'DESC';
                break;
            case 3: 
                $order_column = 'created_at';
                $order_option = 'DESC';
                break;
            case 4:
                $order_column = 'created_at';
                $order_option = 'ASC';
                break;
            default:
                $order_column = 'id';
                $order_option = 'DESC';
        }
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();
        $products = Product::where(function($query) use ($filter_brands){
            $query->whereIn('brand_id', explode(',',$filter_brands))->orWhereRaw("'".$filter_brands."'=''");
        })->where(function($query) use ($filter_categories) {
            $query->whereIn('category_id', explode(',',$filter_categories))->orWhereRaw("'".$filter_categories."'=''");
        })
        ->where(function($query) use ($min_price, $max_price) {
            $query->whereBetween('regular_price',[$min_price,$max_price])
            ->orWhereBetween('sale_price',[$min_price,$max_price]);
        })                 
        ->orderBy($order_column,$order_option)->paginate($size);
        return view('shop',compact('products','size','order','brands','filter_brands','categories','filter_categories','min_price','max_price'));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug',$product_slug)->first();
        $relatedProducts = Product::where('slug','<>',$product_slug)->get()->take(8);
        return view('product-details',compact('product','relatedProducts'));
    }
}
