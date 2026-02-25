<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class TarifsController extends Controller
{
    public function index()
    {
        return view('tarifs', [
            'faqs' => Faq::published()->ordered()->where('category', 'Tarifs')->get(),
        ]);
    }
}
