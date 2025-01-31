<?php

namespace App\Interfaces;

interface ScraperInterface
{
    /**
     * Fetch prices from the specific website.
     *
     * @return array
     */
    public function fetchPrices($data): array;
}
