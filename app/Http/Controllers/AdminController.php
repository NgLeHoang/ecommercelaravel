<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at','DESC')->get()->take(10);
        $dashboardDatas = DB::select("SELECT sum(total) AS TotalAmount,
            sum(if(status='ordered',total,0)) AS TotalOrderedAmount,
            sum(if(status='deliverd',total,0)) AS TotalDeliveredAmount,
            sum(if(status='canceled',total,0)) AS TotalCanceledAmount,
            count(*) AS Total,
            sum(if(status='ordered',1,0)) AS TotalOrdered,
            sum(if(status='delivered',1,0)) AS TotalDelivered,
            sum(if(status='canceled',1,0)) AS TotalCanceled
            FROM orders
        ");
        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
        IFNULL(D.TotalAmount,0) AS TotalAmount,
        IFNULL(D.TotalOrderedAmount,0) AS TotalOrderedAmount,
        IFNULL(D.TotalDeliveredAmount,0) AS TotalDeliveredAmount,
        IFNULL(D.TotalCanceledAmount,0) AS TotalCanceledAmount FROM month_names M
        LEFT JOIN (SELECT DATE_FORMAT(created_at, '%b') AS MonthName,
        MONTH(created_at) AS MonthNo,
        sum(total) AS TotalAmount,
        sum(if(status='ordered',total,0)) AS TotalOrderedAmount,
        sum(if(status='delivered',total,0)) AS TotalDeliveredAmount,
        sum(if(status='canceled',total,0)) AS TotalCanceledAmount
        FROM orders WHERE YEAR(created_at) = YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
        ORDER BY MONTH(created_at)) D ON D.MonthNo = M.id
        ");
        $AmountMonthly = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $AmountOrderedMonthly = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $AmountDeliveredMonthly = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $AmountCanceledMonthly = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
        return view('admin.index', compact('orders','dashboardDatas','AmountMonthly','AmountOrderedMonthly','AmountDeliveredMonthly'
                                            ,'AmountCanceledMonthly','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
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

    public function products()
    {
        $products = Product::orderBy('id','DESC')->paginate(12);
        return view('admin.products',compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories','brands'));
    }

    public function product_store(Request $request)
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

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->status = $request->status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image')) 
        {
            $image = $request->file('image');
            $imageName = $current_timestamp.'.'.$image->extension();
            $this->saveImageProductToFolder($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_img = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $allowedExtension = ['jpg','jpeg','png'];
            $files = $request->file('images');
            foreach($files as $file) 
            {
                $galleryExtension = $file->getClientOriginalExtension();
                $galleryCheck = in_array($galleryExtension, $allowedExtension);
                if($galleryCheck)
                {
                    $galleryFileName = $current_timestamp.'-'.$counter.'.'.$galleryExtension;
                    $this->saveImageProductToFolder($file, $galleryFileName);
                    array_push($gallery_arr, $galleryFileName);
                    $counter++;
                }
                else
                {
                    return redirect()->back()->withErrors(['images' => 'Invalid file type for gallery images.']);
                }
            }
            if(!empty($gallery_arr))
            {
                $gallery_img = implode(',', $gallery_arr);
                $product->images = $gallery_img;
            }
        }
        $product->save();

        return redirect()->route('admin.products')->with('status','Product has added successfully!');
    }

    public function saveImageProductToFolder($image, $imageName)
    {
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image);

        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        $img->resize(104, 104, function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails.'/'.$imageName);
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();

        return view('admin.product-edit', compact('product','categories','brands'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,'.$request->id,
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

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->status = $request->status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            $imageSave = public_path('uploads/products').'/'.$product->image;
            $imageSaveThumbnails = public_path('uploads/products/thumbnails').'/'.$product->image;
            if(File::exists($imageSave))
                File::delete($imageSave);
            if(File::exists($imageSaveThumbnails))
                File::delete($imageSaveThumbnails);

            $image = $request->file('image');
            $imageName = $current_timestamp.'.'.$image->extension();
            $this->saveImageProductToFolder($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(',',$product->images) as $gfile)
            {
                $imageSaves = public_path('uploads/products').'/'.$gfile;
                $imageSavesThumbnails = public_path('uploads/products/thumbnails').'/'.$gfile;

                if(File::exists($imageSaves))
                    File::delete($imageSaves);
                if(File::exists($imageSavesThumbnails))
                    File::delete($imageSavesThumbnails);
            }

            $allowedExtension = ['jpg','jpeg','png'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $galleryExtension = $file->getClientOriginalExtension();
                $galleryCheck = in_array($galleryExtension, $allowedExtension);
                if($galleryCheck)
                {
                    $galleryFileName = $current_timestamp.'-'.$counter.'.'.$galleryExtension;
                    $this->saveImageProductToFolder($file, $galleryFileName);
                    array_push($gallery_arr,$galleryFileName);
                    $counter += 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
            $product->images = $gallery_images;
        }

        $product->save();

        return redirect()->route('admin.products')->with('status','Product has updated successfully');
    } 

    public function product_delete($id)
    {
        $product = Product::find($id);
        $imageSave = public_path('uploads/products').'/'.$product->image;
        $imageSaveThumbnails = public_path('uploads/products/thumbnails').'/'.$product->image;

        if(File::exists($imageSave))
            File::delete($imageSave);
        if(File::exists($imageSaveThumbnails))
            File::delete($imageSaveThumbnails);

        foreach(explode(',',$product->images) as $gfile)
        {
            $imageSaves = public_path('uploads/products').'/'.$gfile;
            $imageSavesThumbnails = public_path('uploads/products/thumbnails').'/'.$gfile;

            if(File::exists($imageSaves))
                File::delete($imageSaves);
            if(File::exists($imageSavesThumbnails))
                File::delete($imageSavesThumbnails);
        }

        $product->delete();

        return redirect()->route('admin.products')->with('status','Product has deleted successfully');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expired_date','DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expired_date = $request->expired_date;

        $coupon->save();

        return redirect()->route('admin.coupons')->with('status','Coupon has added sucessfully');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expired_date' => 'required|date',
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expired_date = $request->expired_date;

        $coupon->save();

        return redirect()->route('admin.coupons')->with('status','Coupon has updated successfully');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('status','Coupon has deleted successfully');
    }

    public function orders() 
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first(); 

        return view('admin.order-details', compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status == 'delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        elseif($request->order_status == 'canceled')
        {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        if($request->order_status == 'delivered')
        {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }

        return back()->with('status','Status changed successfully');
    }
}
