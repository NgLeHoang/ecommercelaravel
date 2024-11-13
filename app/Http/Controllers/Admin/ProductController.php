<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Repository for handling product data operations.
     *
     * @var \App\Repositories\Eloquent\ProductRepositoryInterface
     */
    protected $productRepo;

    /**
     * Repository for handling brand data operations.
     *
     * @var \App\Repositories\Eloquent\BrandRepositoryInterface
     */
    protected $brandRepo;

    /**
     * Repository for handling category data operations.
     *
     * @var \App\Repositories\Eloquent\CategoryRepositoryInterface
     */
    protected $categoryRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\ProductRepositoryInterface $productRepo
     */
    public function __construct(ProductRepositoryInterface $productRepo,
    BrandRepositoryInterface $brandRepo,
    CategoryRepositoryInterface $categoryRepo)
    {
        $this->productRepo = $productRepo;
        $this->brandRepo = $brandRepo;
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Display a listing of all products in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function products()
    {
        $products = $this->productRepo->getAll();
        return view('admin.products', compact('products'));
    }

    /**
     * Display a page add product view.
     *
     * @return \Illuminate\View\View
     */
    public function addProduct()
    {
        $categories = $this->categoryRepo->getCategoryForProduct();
        $brands = $this->brandRepo->getBrandsForProduct();
        return view('admin.product-add', compact('categories', 'brands'));
    }

    /**
     * Store a new product.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'description' => 'required',
            'short_description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'mimes:jpg,jpeg,png|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        // Prepare data to save
        $productData = $request->only([
            'name', 'slug', 'description', 'short_description', 'regular_price', 'sale_price', 
            'SKU', 'status', 'featured', 'quantity', 'category_id', 'brand_id'
        ]);
        $productData['slug'] = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            // Handle image upload and get image filename
            $fileName = $this->productRepo->saveProductImage($request->file('image'));
            $productData['image'] = $fileName;
        }

        if ($request->hasFile('images')) 
        {
            // Handle images upload and save image
            $productData['images'] = $this->productRepo->saveGalleryImages($request->file('images'));
        }

        $this->productRepo->storeProduct($productData);

        return redirect()->route('admin.products.index')->with('status', 'Product has added successfully!');
    }

    /**
     * Display a page add product view with product respectively and categories, brands.
     *
     * @return \Illuminate\View\View
     */
    public function editProduct($id)
    {
        $product = $this->productRepo->find($id);
        $categories = $this->categoryRepo->getCategoryForProduct();
        $brands = $this->brandRepo->getBrandsForProduct();

        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update an existing product.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'description' => 'required',
            'short_description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'mimes:jpg,jpeg,png|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        // Prepare data to update
        $productData = $request->only([
            'name', 'slug', 'description', 'short_description', 'regular_price', 'sale_price', 
            'SKU', 'status', 'featured', 'quantity', 'category_id', 'brand_id'
        ]);
        $productData['slug'] = Str::slug($request->slug);

        $product = $this->productRepo->find($request->id);
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $this->productRepo->deleteProductImage($product->image);

            // Handle image upload and get image filename
            $imageName = $this->productRepo->saveProductImage($request->file('image'));
            $productData['image'] = $imageName;
        }

        if ($request->hasFile('images')) {
            // Delete old gallery image if it exists
            foreach (explode(',', $product->images) as $gfile) {
                $this->productRepo->deleteProductImage($gfile);
            }

            $productData['images'] = $this->productRepo->saveGalleryImages($request->file('images'));
        }

        $this->productRepo->updateProduct($request->id, $productData);

        return redirect()->route('admin.products.index')->with('status', 'Product has updated successfully');
    }

    /**
     * Delete a product by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProduct($id)
    {
        // Delete associated image
        $product = $this->productRepo->find($id);
        $this->productRepo->deleteProductImage($product->image);

        // Delete associated gallery image
        foreach (explode(',', $product->images) as $gfile) {
            $this->productRepo->deleteProductImage($gfile);
        }

        $this->productRepo->deleteProduct($id);

        return redirect()->route('admin.products.index')->with('status', 'Product has deleted successfully');
    }
}