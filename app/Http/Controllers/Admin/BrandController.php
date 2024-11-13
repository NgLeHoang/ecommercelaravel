<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Repository for handling brand data operations.
     *
     * @var \App\Repositories\Eloquent\BrandRepositoryInterface
     */
    protected $brandRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\BrandRepositoryInterface $brandRepo
     */
    public function __construct(BrandRepositoryInterface $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }

    /**
     * Display a listing of all brands in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function brands()
    {
        $brands = $this->brandRepo->getAll();
        return view('admin.brands', compact('brands'));
    }

    /**
     * Display a page add brand view.
     *
     * @return \Illuminate\View\View
     */
    public function addBrand()
    {
        return view('admin.brand-add');
    }

    /**
     * Store a new brand.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $brandData = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
        ];

        // Handle image upload and get image filename
        if ($request->hasFile('image')) {
            $fileName = $this->brandRepo->saveBrandImage($request->file('image'));
            $brandData['image'] = $fileName;
        }

        $this->brandRepo->storeBrand($brandData);
        
        return redirect()->route('admin.brands.index')->with('status', 'Brand has been added successfully');
    }

    /**
     * Display a page add brand view with brand respectively.
     *
     * @return \Illuminate\View\View
     */
    public function editBrand($id)
    {
        $brand = $this->brandRepo->find($id);

        return view('admin.brand-edit', compact('brand'));
    }
    
    /**
     * Update an existing brand.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBrand(Request $request)
    {
        // Validate incoming request data for brand update
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        // Prepare brand data for update
        $brandData = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
        ];

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $this->brandRepo->deleteBrandImage($request->id);

            // Handle image upload and get image filename
            $fileName = $this->brandRepo->saveBrandImage($request->file('image'));
            $brandData['image'] = $fileName;
        }

        // Update the brand in the database
        $this->brandRepo->updateBrand($request->id, $brandData);

        return redirect()->route('admin.brands.index')->with('status', 'Brand has been updated successfully!');
    }

    /**
     * Delete a brand by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteBrand($id)
    {
        // Delete brand and associated image
        $this->brandRepo->deleteBrandImage($id);
        $this->brandRepo->deleteBrand($id);

        return redirect()->route('admin.brands.index')->with('status', 'Brand has been deleted successfully');
    }
}
