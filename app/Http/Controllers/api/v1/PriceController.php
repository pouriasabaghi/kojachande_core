<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Services\v1\ScraperManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PriceController extends Controller
{
    public function fetchPrices(Request $request, ScraperManager $scraperManager)
    {
        $data = $request->validate(['product_name' => 'required|string|max:255', 'product_price' => 'nullable']);

        $cacheKey = "prices-" . md5($data['product_name']);
        $prices = Cache::remember($cacheKey, now()->addMinutes(value: 30), fn() => $scraperManager->fetchPricesHandler(data: $data));

        return response()->json($prices);
    }
}
