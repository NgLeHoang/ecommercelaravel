<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SlideRepositoryInterface;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    /**
     * Repository for handling slide data operations.
     *
     * @var \App\Repositories\Eloquent\SlideRepositoryInterface
     */
    protected $slideRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\SlideRepositoryInterface $slideRepo
     */
    public function __construct(SlideRepositoryInterface $slideRepo)
    {
        $this->slideRepo = $slideRepo;
    }

    /**
     * Display a listing of all slides in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function slides()
    {
        $slides = $this->slideRepo->getAll();
        return view('admin.slides', compact('slides'));
    }

    /**
     * Display a page add slide view.
     *
     * @return \Illuminate\View\View
     */
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

        // Prepare data for store
        $slideData = $request->only([
            'tagline', 'title', 'subtitle', 'link', 'status'
        ]);

        // Handle image upload and get image filename
        if ($request->hasFile('image')) {
            $fileName = $this->slideRepo->saveSlideImage($request->file('image'));
            $slideData['image'] = $fileName;
        }

        // Save slide
        $this->slideRepo->storeSlide($slideData);

        return redirect()->route('admin.slides.index')->with('status', 'Slide has added successfully');
    }

    /**
     * Display a page add slide view with slide respectively.
     *
     * @return \Illuminate\View\View
     */
    public function editSlide($id)
    {
        $slide = $this->slideRepo->find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    /**
     * Update an existing slide.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        $slideData = $request->only([
            'tagline', 'title', 'subtitle', 'link', 'status'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $this->slideRepo->deleteSlideImage($request->id);

            $fileName = $this->slideRepo->saveSlideImage($request->file('image'));
            $slideData['image'] = $fileName;
        }
        
        $this->slideRepo->updateSlide($request->id, $slideData);

        return redirect()->route('admin.slides.index')->with('status', 'Slide has updated successfully');
    }

    /**
     * Delete a slide by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSlide($id)
    {
        $this->slideRepo->deleteSlideImage($id);
        $this->slideRepo->deleteSlide($id);

        return redirect()->route('admin.slides.index')->with('status', 'Slide has deleted successfully!');
    }
}
