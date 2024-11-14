<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    /**
     * Repository for handling category data operations.
     *
     * @var \App\Repositories\Eloquent\CategoryRepositoryInterface
     */
    protected $categoryRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\CategoryRepositoryInterface $categoryRepo
     */
    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Display a listing of all categories in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        $categories = $this->categoryRepo->getAll();
        return view('admin.categories', compact('categories'));
    }

    /**
     * Display a page add category view.
     *
     * @return \Illuminate\View\View
     */
    public function addCategory()
    {
        return view('admin.category-add');
    }

    /**
     * Store a new category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $categoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
        ];

        // Handle image upload and get image filename
        if ($request->hasFile('image')) {
            $fileName = $this->categoryRepo->saveCategoryImage($request->file('image'));
            $categoryData['image'] = $fileName;
        }

        $this->categoryRepo->storeCategory($categoryData);

        return redirect()->route('admin.categories.index')->with('status', 'Category has added successfully');
    }

    /**
     * Display a page add category view with category respectively.
     *
     * @return \Illuminate\View\View
     */
    public function editCategory($id)
    {
        $category = $this->categoryRepo->find($id);
        return view('admin.category-edit', compact('category'));
    }

    /**
     * Update an existing category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCategory(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        // Prepare category data for update
        $categoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
        ];

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $this->categoryRepo->deleteCategoryImage($request->id);

            // Handle image upload and get image filename
            $fileName = $this->categoryRepo->saveCategoryImage($request->file('image'));
            $categoryData['image'] = $fileName;
        }

        // Update the category in the database
        $this->categoryRepo->updateCategory($request->id, $categoryData);

        return redirect()->route('admin.categories.index')->with('status', 'Category has updated successfully');
    }

    /**
     * Delete a category by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCategory($id)
    {
        // Delete category and associated image
        $this->categoryRepo->deleteCategoryImage($id);
        $this->categoryRepo->deleteCategory($id);

        return redirect()->route('admin.categories.index')->with('status', 'Category has been deleted successfully');
    }
}