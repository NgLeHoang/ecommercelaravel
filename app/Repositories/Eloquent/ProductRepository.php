<?php

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getProductsForShop(Request $request)
    {
        $slides = Slide::where('status', 1)->take(3)->get();
        $size = $request->query('size', 12);
        $order = $request->query('order', -1);
        $filter_brands = $request->query('brands', '');
        $filter_categories = $request->query('categories', '');
        $min_price = $request->query('min', 1);
        $max_price = $request->query('max', 5000);

        $order_options = [
            1 => ['sale_price', 'ASC'],
            2 => ['sale_price', 'DESC'],
            3 => ['created_at', 'DESC'],
            4 => ['created_at', 'ASC'],
            -1 => ['id', 'DESC'],
        ];
        [$order_column, $order_option] = $order_options[$order];

        //Get data brand and category
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();

        //Query product
        $products = Product::where(function ($query) use ($filter_brands) {
            $query->whereIn('brand_id', explode(',', $filter_brands))
                ->orWhereRaw("'" . $filter_brands . "'=''");
        })
            ->where(function ($query) use ($filter_categories) {
                $query->whereIn('category_id', explode(',', $filter_categories))
                    ->orWhereRaw("'" . $filter_categories . "'=''");
            })
            ->where(function ($query) use ($min_price, $max_price) {
                $query->whereBetween('regular_price', [$min_price, $max_price])
                    ->orWhereBetween('sale_price', [$min_price, $max_price]);
            })
            ->orderBy($order_column, $order_option)
            ->paginate($size);

        return compact('products', 'size', 'order', 'brands', 'filter_brands', 'categories', 'filter_categories', 'min_price', 'max_price', 'slides');
    }

    public function getProductBySlug($product_slug)
    {
        $this->model->where('slug', $product_slug)->first();
    }

    public function getRelatedProducts($product_slug)
    {
        $this->model->where('slug', '<>', $product_slug)->get()->take(8);
    }
}
