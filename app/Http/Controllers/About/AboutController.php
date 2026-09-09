<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\Controller;
use App\Services\Testimonial\TestimonialService;
use App\Support\Settings;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __construct(private readonly TestimonialService $testimonials) {}

    public function index(): View
    {
        return view('pages.about.index', [
            'aboutTitle' => Settings::get('contents.about_title'),
            'aboutContent' => Settings::get('contents.about_content'),
            'testimonials' => $this->testimonials->active(),
        ]);
    }
}
