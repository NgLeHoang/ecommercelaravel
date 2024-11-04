<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id','DESC')->paginate(12);
        return view('admin.brands', compact('brands'));
    }

    public function brand_add()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp. '.' .$file_extension;
        $this->saveImageToFolder($image, $file_name, "brands", 124, 124);
        $brand->image = $file_name;

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully');
    }

    public function saveImageToFolder($image, $imageName, $folderName, $width, $height)
    {
        $destinationPath = public_path('uploads/'.$folderName);
        $img = Image::read($image->path());

        $img->cover($width, $height, "top");
        $img->resize($width, $height, function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_edit($id) 
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$request->id,
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        if($request->hasFile('image'))
        {
            $img = public_path('uploads/brands').'/'.$brand->image;
            if(File::exists($img))
            {
                File::delete($img);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp. '.' .$file_extension;
            $this->saveImageToFolder($image, $file_name, "brands", 124, 124);
            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status','Brand has been updated successfully!');
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        $img = public_path('uploads/brands').'/'.$brand->image;
        if(File::exists($img))
        {
            File::delete($img);
        }
        $brand->delete();

        return redirect()->route('admin.brands')->with('status','Brand has been deleted successfully');
    }

    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(12);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->saveImageToFolder($image, $file_name, 'categories', 124, 124);
        $category->image = $file_name;

        $category->save();

        return redirect()->route('admin.categories')->with('status','Category has added successfully');
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id,
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        if($request->hasFile('image'))
        {
            $img = public_path('uploads/categories').'/'.$category->image;
            if(File::exists($img))
            {
                File::delete($img);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extension;
            $this->saveImageToFolder($image, $file_name, 'categories', 124, 124);
            $category->image = $file_name;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status','Category has updated successfully');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        $img = public_path('uploads/categories').'/'.$category->image;
        if(File::exists($img))
        {
            File::delete($img);
        }
        $category->delete();

        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully');
    }
}
