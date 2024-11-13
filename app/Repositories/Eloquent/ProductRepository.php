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
    /**
     * The model instance that this repository will interact with.
     *
     * @var \App\Models\OrderItem
     */
    protected $model;

    /**
     * ProductRepository constructor.
     *
     * @param \App\Models\Product $model
     */
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all product items, paginated.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->paginate(12);
    }

    /**
     * Find a record item by its ID.
     *
     * @param int $id
     * @return \App\Models\Product|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get products for the shop with filters, sorting, and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getProductsForShop(Request $request)
    {
        //Get data from request
        $slides = Slide::where('status', 1)->take(3)->get();
        $size = $request->query('size', 12);
        $order = $request->query('order', -1);
        $filter_brands = $request->query('brands', '');
        $filter_categories = $request->query('categories', '');
        $min_price = $request->query('min', 1);
        $max_price = $request->query('max', 5000);

        //Define sorting options
        $order_options = [
            1 => ['sale_price', 'ASC'],
            2 => ['sale_price', 'DESC'],
            3 => ['created_at', 'DESC'],
            4 => ['created_at', 'ASC'],
            -1 => ['id', 'DESC'],
        ];
        [$order_column, $order_option] = $order_options[$order];

        //Retrieve all brands and categories for the filter options 
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();

        //Query products based on filters: brand, category, price range, and sort order
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
            // Apply the chosen sorting order
            ->orderBy($order_column, $order_option)
            // Paginate the results based on the size parameter
            ->paginate($size);

        return compact('products', 'size', 'order', 'brands', 'filter_brands', 'categories', 'filter_categories', 'min_price', 'max_price', 'slides');
    }

    /**
     * Get a product by its slug.
     *
     * @param string $product_slug
     * @return \App\Models\Product|null
     */
    public function getProductBySlug($product_slug)
    {
        $this->model->where('slug', $product_slug)->first();
    }

    /**
     * Get related products, excluding the current product.
     *
     * @param string $product_slug
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProducts($product_slug)
    {
        $this->model->where('slug', '<>', $product_slug)->get()->take(8);
    }

    /**
     * Retrieve a random selection of sale products.
     *
     * @return \Illuminate\Database\Eloquent\Collection 
     */
    public function getSaleProducts()
    {
        $this->model->whereNotNull('sale_price')->where('sale_price', '<>', '')->inRandomOrder()->get()->take(8);
    }

    /**
     * Retrieve a selection of featured products.
     *
     * @return \Illuminate\Database\Eloquent\Collection 
     */
    public function getFeaturedProducts()
    {
        $this->model->where('featured', true)->get()->take(8);
    }
}
