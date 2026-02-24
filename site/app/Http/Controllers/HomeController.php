<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Review;
use App\Models\Sens;
class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'sens' => Sens::published()->ordered()->get(),
            'reviews' => Review::published()->ordered()->get(),
            'faqs' => Faq::published()->ordered()->take(6)->get(),
        ]);
    }
}
