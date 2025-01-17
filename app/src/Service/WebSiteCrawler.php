<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WebSiteCrawler
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

    protected function fetchWebsiteContent(string $url): string
    {
        try {
            $response = $this->client->get($url);

            if ($response->getStatusCode() === 200) {
                return (string) $response->getBody();
            }

            throw new \Exception("Failed to fetch content, HTTP status: " . $response->getStatusCode());
        } catch (RequestException $e) {
            throw new \Exception("Request failed: " . $e->getMessage());
        }
    }

    protected function extractAnchors(string $htmlContent): array
    {
        $anchors = [];
        $dom = new \DOMDocument();

        // Suppress warnings from invalid HTML
        @$dom->loadHTML($htmlContent);
        $links = $dom->getElementsByTagName('a');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $title = $link->getAttribute('title');

            if (trim($href)!=='') {
                $anchors[] = [
                    'href' => $href,
                    'text' => (string) $title!=='' ? $title : $link->textContent,
                ];
            }
        }

        return $anchors;
    }

    public function catchUrl(string $baseUrl): array
    {
        $htmlContent = $this->fetchWebsiteContent($baseUrl);
        $anchors = $this->extractAnchors($htmlContent);
        $parsedBaseUrl = parse_url($baseUrl);
        $result = [];

        foreach ($anchors as $anchor) {
            $href = $anchor['href'];
            $statusCode = $this->checkUrlStatus($this->normalizeUrl($href, $baseUrl));
            $linkElement = [
                'href' => $href,
                'text' => $anchor['text'],
                'type' => $this->isInternalLink($href, $parsedBaseUrl) ? 'internal' : 'external',
                'status' => $statusCode,
            ];

            $result[] = $linkElement;
        }

        return $result;
    }

    private function isInternalLink(string $href, array $parsedBaseUrl): bool
    {
        // Check if link starts with "/" (relative path)
        if (str_starts_with($href, '/')) {
            return true;
        }

        // Parse the URL and compare the host with the base URL's host
        $parsedHref = parse_url($href);
        if (isset($parsedHref['host']) && $parsedHref['host'] === $parsedBaseUrl['host']) {
            return true;
        }

        // If the base URL is part of the href (e.g., subdirectory structure)
        if (strpos($href, $parsedBaseUrl['host']) !== false) {
            return true;
        }

        return false;
    }

    private function normalizeUrl(string $href, string $baseUrl): string
    {
        if (str_starts_with($href, '/')) {
            return rtrim($baseUrl, '/') . $href;
        }

        return $href;
    }

    private function checkUrlStatus(string $url): int
    {
        try {
            $response = $this->client->head($url);
            return $response->getStatusCode();
        } catch (RequestException $e) {
            return $e->getCode() ?: 0;
        }
    }

}
