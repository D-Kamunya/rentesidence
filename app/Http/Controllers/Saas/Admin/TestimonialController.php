<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialRequest;
use App\Services\TestimonialService;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public $testimonialService;

    public function __construct()
    {
        $this->testimonialService = new TestimonialService;
    }

    public function index()
    {
        $data['pageTitle'] = __("Testimonials");
        $data['subTestimonialsActiveClass'] = 'active';
        $data['testimonials'] = $this->testimonialService->getAll();
        return view('admin.frontend.testimonial', $data);
    }

    public function store(TestimonialRequest $request)
    {
        return $this->testimonialService->store($request);
    }

    public function getInfo(Request $request)
    {
        return $this->testimonialService->getInfo($request->id);
    }

    public function destroy($id)
    {
        return $this->testimonialService->delete($id);
    }
}
