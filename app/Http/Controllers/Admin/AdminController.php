<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Slide;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class AdminController extends Controller
{
    /**
     * Repository for handling dashboard-related data operations.
     *
     * @var \App\Repositories\Eloquent\BrandRepositoryInterface
     * @var \App\Repositories\Eloquent\CategoryRepositoryInterface
     * @var \App\Repositories\Eloquent\ProductRepositoryInterface
     * @var \App\Repositories\Eloquent\DashboardRepositoryInterface 
     */
    protected $brandRepo;
    protected $categoryRepo;
    protected $productRepo;
    protected $dashboardRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\BrandRepositoryInterface $brandRepo
     * @param \App\Repositories\Eloquent\CategoryRepositoryInterface $categoryRepo
     * @param \App\Repositories\Eloquent\ProductRepositoryInterface $productRepo
     * @param \App\Repositories\Eloquent\DashboardRepositoryInterface $dashboardRepo
     */
    public function __construct(
        BrandRepositoryInterface $brandRepo,
        CategoryRepositoryInterface $categoryRepo,
        ProductRepositoryInterface $productRepo,
        DashboardRepositoryInterface $dashboardRepo
    ) {
        $this->brandRepo = $brandRepo;
        $this->categoryRepo = $categoryRepo;
        $this->productRepo = $productRepo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = $this->dashboardRepo->getDashboardData();
        $monthlyDatas = $this->dashboardRepo->getMonthlyData();

        function getMonthlyDataString($monthlyDatas, $column)
        {
            return implode(',', collect($monthlyDatas)->pluck($column)->toArray());
        }

        $AmountMonthly = getMonthlyDataString($monthlyDatas, 'TotalAmount');
        $AmountOrderedMonthly = getMonthlyDataString($monthlyDatas, 'TotalOrderedAmount');
        $AmountDeliveredMonthly = getMonthlyDataString($monthlyDatas, 'TotalDeliveredAmount');
        $AmountCanceledMonthly = getMonthlyDataString($monthlyDatas, 'TotalCanceledAmount');

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
        return view('admin.index', compact(
            'orders',
            'dashboardDatas',
            'AmountMonthly',
            'AmountOrderedMonthly',
            'AmountDeliveredMonthly',
            'AmountCanceledMonthly',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
    }

    public function saveImageToFolder($image, $imageName, $folderName, $width, $height)
    {
        $destinationPath = public_path('uploads/' . $folderName);
        $img = Image::read($image->path());

        //Cover and resize image and then save image to folder
        $img->cover($width, $height, 'top');
        $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    //Category management
    public function categories()
    {
        $categories = $this->categoryRepo->getAll();
        return view('admin.categories', compact('categories'));
    }

    public function addCategory()
    {
        return view('admin.category-add');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        //Init category
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        //Get and save image
        $image = $request->file('image');
        $fileExtension = $request->file('image')->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
        $this->saveImageToFolder($image, $fileName, 'categories', 124, 124);
        $category->image = $fileName;

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category has added successfully');
    }

    public function editCategory($id)
    {
        $category = $this->categoryRepo->find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function updateCategory(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:jpg,jpeg,png|max:2048'
        ]);

        $category = $this->categoryRepo->find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            $img = public_path('uploads/categories') . '/' . $category->image;
            if (File::exists($img)) File::delete($img);

            $image = $request->file('image');
            $fileExtension = $request->file('image')->extension();
            $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
            $this->saveImageToFolder($image, $fileName, 'categories', 124, 124);
            $category->image = $fileName;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category has updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = $this->categoryRepo->find($id);
        $img = public_path('uploads/categories') . '/' . $category->image;
        if (File::exists($img)) File::delete($img);

        $category->delete();

        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully');
    }

    //Product management
    public function products()
    {
        $products = $this->productRepo->getAll();
        return view('admin.products', compact('products'));
    }

    public function addProduct()
    {
        $categories = $this->categoryRepo->getCategoryForProduct();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }

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

        $currentTimestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $currentTimestamp . '.' . $image->extension();
            $this->saveImageProductToFolder($image, $imageName);
            $product->image = $imageName;
        }

        $galleryArray = array();
        $galleryImage = '';
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedExtension = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $galleryExtension = $file->getClientOriginalExtension();
                $galleryCheck = in_array($galleryExtension, $allowedExtension);
                if ($galleryCheck) {
                    $galleryFileName = $currentTimestamp . '-' . $counter . '.' . $galleryExtension;
                    $this->saveImageProductToFolder($file, $galleryFileName);
                    array_push($galleryArray, $galleryFileName);
                    $counter++;
                } else {
                    return redirect()->back()->withErrors(['images' => 'Invalid file type for gallery images.']);
                }
            }
            if (!empty($galleryArray)) {
                $galleryImage = implode(',', $galleryArray);
                $product->images = $galleryImage;
            }
        }
        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product has added successfully!');
    }

    public function saveImageProductToFolder($image, $imageName)
    {
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image);

        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails . '/' . $imageName);
    }

    public function editProduct($id)
    {
        $product = $this->productRepo->find($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

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

        $product = $this->productRepo->find($request->id);
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

        $currentTimestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            $imageSave = public_path('uploads/products') . '/' . $product->image;
            $imageSaveThumbnails = public_path('uploads/products/thumbnails') . '/' . $product->image;
            if (File::exists($imageSave)) File::delete($imageSave);
            if (File::exists($imageSaveThumbnails)) File::delete($imageSaveThumbnails);

            $image = $request->file('image');
            $imageName = $currentTimestamp . '.' . $image->extension();
            $this->saveImageProductToFolder($image, $imageName);
            $product->image = $imageName;
        }

        $galleryArray = array();
        $galleryImages = '';
        $counter = 1;

        if ($request->hasFile('images')) {
            foreach (explode(',', $product->images) as $gfile) {
                $imageSaves = public_path('uploads/products') . '/' . $gfile;
                $imageSavesThumbnails = public_path('uploads/products/thumbnails') . '/' . $gfile;

                if (File::exists($imageSaves)) File::delete($imageSaves);
                if (File::exists($imageSavesThumbnails)) File::delete($imageSavesThumbnails);
            }

            $allowedExtension = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $galleryExtension = $file->getClientOriginalExtension();
                $galleryCheck = in_array($galleryExtension, $allowedExtension);
                if ($galleryCheck) {
                    $galleryFileName = $currentTimestamp . '-' . $counter . '.' . $galleryExtension;
                    $this->saveImageProductToFolder($file, $galleryFileName);
                    array_push($galleryArray, $galleryFileName);
                    $counter += 1;
                }
            }
            $galleryImages = implode(',', $galleryArray);
            $product->images = $galleryImages;
        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product has updated successfully');
    }

    public function deleteProduct($id)
    {
        $product = $this->productRepo->find($id);
        $imageSave = public_path('uploads/products') . '/' . $product->image;
        $imageSaveThumbnails = public_path('uploads/products/thumbnails') . '/' . $product->image;

        if (File::exists($imageSave)) File::delete($imageSave);
        if (File::exists($imageSaveThumbnails)) File::delete($imageSaveThumbnails);

        foreach (explode(',', $product->images) as $gfile) {
            $imageSaves = public_path('uploads/products') . '/' . $gfile;
            $imageSavesThumbnails = public_path('uploads/products/thumbnails') . '/' . $gfile;

            if (File::exists($imageSaves)) File::delete($imageSaves);
            if (File::exists($imageSavesThumbnails)) File::delete($imageSavesThumbnails);
        }

        $product->delete();

        return redirect()->route('admin.products')->with('status', 'Product has deleted successfully');
    }

    //Coupon management
    public function coupons()
    {
        $coupons = Coupon::orderBy('expired_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function addCoupon()
    {
        return view('admin.coupon-add');
    }

    public function storeCoupon(Request $request)
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

        return redirect()->route('admin.coupons')->with('status', 'Coupon has added sucessfully');
    }

    public function editCoupon($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function updateCoupon(Request $request)
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

        return redirect()->route('admin.coupons')->with('status', 'Coupon has updated successfully');
    }

    public function deleteCoupon($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has deleted successfully');
    }

    //Order management
    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function orderDetails($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();

        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function updateOrderStatus(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }

        return back()->with('status', 'Status changed successfully');
    }

    //Slide management
    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }

    public function addSlide()
    {
        return view('admin.slide-add');
    }

    public function storeSlide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $fileExtension = $image->extension();
        $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
        $this->saveImageToFolder($image, $fileName, 'slides', 400, 690);
        $slide->image = $fileName;
        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide has added successfully');
    }

    public function editSlide($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function updateSlide(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'status' => 'required'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->hasFile('image')) {
            $img = public_path('uploads/slides') . '/' . $slide->image;
            if (File::exists($img)) File::delete($img);

            $image = $request->file('image');
            $fileExtension = $image->extension();
            $fileName = Carbon::now()->timestamp . '.' . $fileExtension;
            $this->saveImageToFolder($image, $fileName, 'slides', 400, 690);
            $slide->image = $fileName;
        }
        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide has updated successfully');
    }

    public function deleteSlide($id)
    {
        $slide = Slide::find($id);
        $img = public_path('uploads/slides') . '/' . $slide->image;
        if (File::exists($img)) {
            File::delete($img);
        }
        $slide->delete();

        return redirect()->route('admin.slides')->with('status', 'Slide has deleted successfully!');
    }

    //Contact management
    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }

    public function deleteContact($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with('status', 'Contact message has deleted successfully!');
    }
}
