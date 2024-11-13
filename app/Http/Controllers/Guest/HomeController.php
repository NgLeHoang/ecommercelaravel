<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\SlideRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;

class HomeController extends Controller
{
    /**
     * Repository for handling slide data operations.
     *
     * @var \App\Repositories\Contracts\SlideRepositoryInterface
     */
    protected $slideRepo;

    /**
     * Repository for handling category data operations.
     *
     * @var \App\Repositories\Contracts\CategoryRepositoryInterface
     */
    protected $categoryRepo;

    /**
     * Repository for handling product data operations.
     *
     * @var \App\Repositories\Contracts\ProductRepositoryInterface
     */
    protected $productRepo;

    /**
     * Repository for handling contact data operations.
     *
     * @var \App\Repositories\Contracts\ContactRepositoryInterface
     */
    protected $contactRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Contracts\SlideRepositoryInterface $slideRepo
     * @param \App\Repositories\Contracts\CategoryRepositoryInterface $categoryRepo
     * @param \App\Repositories\Contracts\ProductRepositoryInterface $productRepo
     * @param \App\Repositories\Contracts\ContactRepositoryInterface $contactRepo
     */
    public function __construct(
        SlideRepositoryInterface $slideRepo,
        CategoryRepositoryInterface $categoryRepo,
        ProductRepositoryInterface $productRepo,
        ContactRepositoryInterface $contactRepo
    ) 
    {
        $this->slideRepo = $slideRepo;
        $this->categoryRepo = $categoryRepo;
        $this->productRepo = $productRepo;
        $this->contactRepo = $contactRepo;
    }

    /**
     * Display the home page with necessary data.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $slides = $this->slideRepo->getSlideForHomePage();
        $categories = $this->categoryRepo->getCategoryForHomePage();
        $saleProducts = $this->productRepo->getSaleProducts();
        $featuredProducts = $this->productRepo->getFeaturedProducts();
        return view('index', compact('slides', 'categories', 'saleProducts', 'featuredProducts'));
    }

    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle storing contact form data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeContact(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required',
        ]);

        $this->contactRepo->create($request->only(['name', 'email', 'phone', 'comment']));

        return redirect()->back()->with('success', 'Your message has been sent successfully');
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('about');
    }
}
