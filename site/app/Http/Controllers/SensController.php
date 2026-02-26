<?php

namespace App\Http\Controllers;

use App\Models\Sens;

class SensController extends Controller
{
    public function index()
    {
        $allSens = Sens::published()->ordered()->get();

        return view('sens.index', [
            'allSens' => $allSens,
        ]);
    }

    public function show(string $slug)
    {
        $sens = Sens::published()->where('slug', $slug)->firstOrFail();
        $allSens = Sens::published()->ordered()->get();

        return view('sens.show', [
            'sens' => $sens,
            'allSens' => $allSens,
        ]);
    }
}
