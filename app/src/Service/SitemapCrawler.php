<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SitemapCrawler
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ],
            'timeout' => 0,
        ]);
    }

    public function fetchSitemap(string $sitemapUrl): array
    {
        try {
            $response = $this->client->get($sitemapUrl);

            if ($response->getStatusCode() === 200) {
                return $this->parseSitemap((string) $response->getBody());
            }

            throw new \Exception("Failed to fetch sitemap, HTTP status: " . $response->getStatusCode());
        } catch (RequestException $e) {
            throw new \Exception("Request failed: " . $e->getMessage());
        }
    }

    private function parseSitemap(string $xmlContent): array
    {
        $urls = [];
        try {
            $xml = new \SimpleXMLElement($xmlContent);

            foreach ($xml->url as $url) {
                if (isset($url->loc)) {
                    $urls[] = (string) $url->loc;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("Invalid Sitemap format: " . $e->getMessage());
        }

        return $urls;
    }

    public function checkUrlStatus(string $url): int
    {
        try {
            $response = $this->client->head($url);
            return $response->getStatusCode();
        } catch (RequestException $e) {
            return $e->getCode() ?: 0;
        }
    }
}
