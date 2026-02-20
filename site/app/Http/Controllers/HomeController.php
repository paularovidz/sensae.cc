<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Review;
use App\Models\Sens;
use App\Services\SensaeApiService;

class HomeController extends Controller
{
    public function index(SensaeApiService $api)
    {
        $pricing = $api->getPricing();
        $sessions = $pricing['sessions'] ?? [];
        $packs = $pricing['packs'] ?? [];

        return view('home', [
            'sens' => Sens::published()->ordered()->get(),
            'reviews' => Review::published()->ordered()->get(),
            'faqs' => Faq::published()->ordered()->take(6)->get(),
            'unitPricing' => [
                'discovery_price' => (int) ($sessions['discovery']['price'] ?? 55),
                'discovery_duration' => (int) ($sessions['discovery']['duration'] ?? 75),
                'regular_price' => (int) ($sessions['regular']['price'] ?? 45),
                'regular_duration' => (int) ($sessions['regular']['duration'] ?? 45),
            ],
            'packPricing' => [
                'pack_2_price' => (int) ($packs['pack_2']['price'] ?? 110),
                'pack_2_sessions' => (int) ($packs['pack_2']['sessions'] ?? 2),
                'pack_4_price' => (int) ($packs['pack_4']['price'] ?? 200),
                'pack_4_sessions' => (int) ($packs['pack_4']['sessions'] ?? 4),
            ],
        ]);
    }
}
