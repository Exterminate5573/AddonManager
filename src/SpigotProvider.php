<?php

namespace Pterodactyl\BlueprintFramework\Extensions\{identifier};

use Illuminate\Support\Facades\Http;

class SpigotProvider
{
    public function searchAddons($query, $page = 1)
    {
        $response = Http::get('https://api.spiget.org/v2/search/resources/' . urlencode($query), [
            'size' => 10,
            'page' => $page,
        ]);
        if (!$response->ok()) {
            return [
                'items' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => 1,
                    'total' => 0,
                    'count' => 0,
                    'perPage' => 10,
                ],
            ];
        }
        $data = $response->json();
        $items = [];
        foreach ($data as $item) {
            $items[] = [
                'uuid' => $item['id'] ?? null,
                'addonName' => $item['name'] ?? '',
                'addonDescription' => $item['description'] ?? '',
                'addonVersion' => '', // Not available in search
                'addonVersionId' => $item['version']['id'] ?? '',
                'addonAuthor' => '', // Not available in search
                'iconURL' => $item['icon']['url'] ?? null,
                'provider' => 'spigot',
            ];
        }
        $totalPages = (int)($response->header('X-Page-Count') ?? 1);
        $total = $totalPages * 10; // Approximate, Spiget doesn't always provide total
        return [
            'items' => $items,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'count' => count($items),
                'perPage' => 10,
            ],
        ];
    }

    public function downloadAddon($resourceId)
    {
        // This is necessary as Spiget does not provide all the needed info in one call
        $resourceUrl = "https://api.spiget.org/v2/resources/{$resourceId}";
        $resourceResponse = Http::get($resourceUrl);
        if (!$resourceResponse->ok()) {
            return null;
        }
        $resourceData = $resourceResponse->json();

        $versionUrl = "https://api.spiget.org/v2/resources/{$resourceId}/version/latest";
        $versionResponse = Http::get($versionUrl);
        if (!$versionResponse->ok()) {
            return null;
        }
        $versionData = $versionResponse->json();

        $url = "https://api.spiget.org/v2/resources/{$resourceId}/versions/{$versionData['id']}/download";
        $response = Http::withOptions(['allow_redirects' => true])->get($url);

        if ($response->ok()) {
            $fileSource = $response->header("X-Spiget-File-Source");
            if ($fileSource === 'external') {
                // Externally hosted, cannot download directly
                return null;
            }
            // CDN file, return contents
            return [
                'fileContents' => $response->body(),
                'friendlyName' => $resourceData['name'] ?? $resourceId,
                'friendlyVersion' => $versionData['name'] ?? 'unknown',
            ];
        }

        // Handle 302 redirect manually if needed
        if ($response->status() === 302) {
            $fileSource = $response->header("X-Spiget-File-Source");
            $location = $response->header('Location');
            if ($fileSource === 'cdn' && $location) {
                // Follow the redirect to the CDN
                $cdnResponse = Http::get($location);
                if ($cdnResponse->ok()) {
                    return [
                        'fileContents' => $cdnResponse->body(),
                        'friendlyName' => $resourceData['name'] ?? $resourceId,
                        'friendlyVersion' => $versionData['name'] ?? 'unknown',
                    ];
                }
            }
            // Externally hosted, cannot download directly
            return null;
        }

        return null;
    }
}
