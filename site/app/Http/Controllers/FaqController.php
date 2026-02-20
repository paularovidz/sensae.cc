<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::published()->ordered()->get()->groupBy('category');

        return view('faq', [
            'faqsByCategory' => $faqs,
        ]);
    }
}
