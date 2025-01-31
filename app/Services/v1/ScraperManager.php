<?php

namespace App\Services\v1;

class ScraperManager
{
    private $scrapers = [];

    public function registerWebsite(string $website, $scraper)
    {
        $this->scrapers[$website] = $scraper;
    }


    public function fetchPricesHandler(?string $website = null, array $data = [])
    {
        // fetch price from specific website 
        if ($website)
            return $this->scrapers[$website]->fetchPrices($data);

        // fetch price from all websites
        $prices = [];
        foreach ($this->scrapers as $scraper) {
            $prices[] = $scraper->fetchPrices($data);
        }

        return $prices;
    }
}
