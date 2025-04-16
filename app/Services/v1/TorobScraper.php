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

        $url = 'https://torob.com/search/?query=' . urlencode($digiProductName);

        // Simulate real browser headers
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Referer' => 'https://torob.com/',
            'DNT' => '1',
        ];

        $proxies = [
            'http://172.167.161.8:8080',
            'http://4.145.89.88:8080',
            'http://159.69.57.20:8880',
        ];
        
        $proxy = $proxies[array_rand($proxies)];

        // Send Http Request with headers
        $response = Http::withOptions([
            'proxy' => $proxy,
        ])->withHeaders($headers)->get($url);

        if ($response->failed()) {
            return ["error" => "Failed to fetch"];
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->body());
        $xpath = new \DOMXPath($dom);

        $productNodes = $xpath->query('//h2[contains(@class, "ProductCard_desktop_product-name__JwqeK")]');
        $priceNodes = $xpath->query('//div[contains(@class, "ProductCard_desktop_product-price-text__y20OV")]');
        $imageNodes = $xpath->query('//div[contains(@class, "ProductImageSlider_slide__kN_Ed")]//img');

        if ($productNodes->length === 0 || $priceNodes->length === 0) {
            return ['error' => 'محصولی با این عنوان در ترب یافت نشد.'];
        }

        $results = [];

        for ($i = 0; $i < $productNodes->length; $i++) {
            $productName = $productNodes->item($i)->textContent;
            $productPriceStr = $priceNodes->item($i)->textContent;
            $productPriceNumeric = \Utils::fixNumber($productPriceStr);

            $productImage = $imageNodes->item($i)?->getAttribute('srcset');
            $productLink = "https://torob.com" . $productNodes->item($i)->parentNode->parentNode->getAttribute('href');

            $priceCompare = ($digiProductPrice && intval($productPriceNumeric)) ? $digiProductPrice <=> intval($productPriceNumeric) : 0;

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
