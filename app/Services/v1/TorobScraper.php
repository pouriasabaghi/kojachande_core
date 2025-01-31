<?php

namespace App\Services\v1;

use App\Interfaces\ScraperInterface;
use Illuminate\Support\Facades\Http;

class TorobScraper implements ScraperInterface
{
    public function fetchPrices($data): array
    {
        $digiProductName = $data['product_name'];
        $digiProductPrice = !empty($data['product_price']) ? \Utils::fixNumber($data['product_price']) : null;

        // Torob Endpoint 
        $url = 'https://torob.com/search/?query=' . urlencode($digiProductName);

        // Send Http Request
        $response = Http::get($url);

        // Check Request Status
        if ($response->failed())
            return ["error" => "Failed to fetch page"];

        // Process HTML With DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->body()); // استفاده از @ برای جلوگیری از هشدارها در صورت HTML نامعتبر
        $xpath = new \DOMXPath($dom);

        // Extract Data
        $productNodes = $xpath->query('//h2[contains(@class, "ProductCard_desktop_product-name__JwqeK")]');
        $priceNodes = $xpath->query('//div[contains(@class, "ProductCard_desktop_product-price-text__y20OV")]');
        $ّimageNodes = $xpath->query('//div[contains(@class, "ProductImageSlider_slide__kN_Ed")]//img');

        // Check If Any Data Found
        if ($productNodes->length === 0 || $priceNodes->length === 0)
            return ['error' => 'محصولی با این عنوان در ترب یافت نشد.'];

        $results = [];
        // Extract Data For Each Product
        for ($i = 0; $i < $productNodes->length; $i++) {
            $productName = $productNodes->item($i)->textContent;

            $productPriceStr = $priceNodes->item($i)->textContent;
            $productPriceNumeric = \Utils::fixNumber($productPriceStr);

            $productImage = $ّimageNodes->item($i)->getAttribute('srcset');

            $productLink = "https://torob.com" . $productNodes->item($i)->parentNode->parentNode->getAttribute('href');

            $priceCompare = ($digiProductPrice && intval($productPriceNumeric)) ? $digiProductPrice <=> intval($productPriceNumeric) : 0;

            // Add Result To Array
            $results[] = [
                'product' => $productName,
                'price' => $productPriceStr,
                'link' => $productLink,
                'image' => $productImage ? explode('1x', $productImage)[0] : null,
                'price_compare' => $priceCompare,
                'shop_name' => "ترب",
            ];
        }

        return $results;
    }


}
