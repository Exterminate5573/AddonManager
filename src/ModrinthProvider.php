<?php

namespace Pterodactyl\BlueprintFramework\Extensions\{identifier};

use Illuminate\Support\Facades\Http;

class ModrinthProvider
{
    public function searchAddons($query, $page = 1)
    {

        $response = Http::get('https://api.modrinth.com/v2/search', [
            'query' => $query,
            //TODO: Setting for sorting
            'index' => 'downloads',
            //'facets' => 'categories',
            'offset' => ($page - 1) * 10,
            'limit' => 10,
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
        foreach ($data['hits'] as $item) {
            $items[] = [
                'uuid' => $item['project_id'] ?? null,
                'addonName' => $item['title'] ?? '',
                'addonDescription' => $item['description'] ?? '',
                'addonVersion' => '',
                'addonVersionId' => $item['latest_version'] ?? '',
                'addonAuthor' => $item['author'] ?? '',
                'iconURL' => $item['icon_url'] ?? null,
                'provider' => 'modrinth',
            ];
        }

        $totalPages = (int)ceil($data['total_hits'] / 10);
        return [
            'items' => $items,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $data['total_hits'],
                'count' => count($items),
                'perPage' => 10,
            ],
        ];
    }

    public function downloadAddon($resourceId)
    {
        //TODO: VersionId from request
        $versions = Http::get("https://api.modrinth.com/v2/project/{$resourceId}/version")->json();
        if (empty($versions)) {
            return null;
        } else {
            $latestVersion = $versions[0]; // Assuming the first version is the latest
            $downloadUrl = $latestVersion['files'][0]['url'] ?? null; // Get the first file URL
            if (!$downloadUrl) {
                return null;
            }

            $response = Http::get($downloadUrl);
            if ($response->ok()) {
                return [
                    'fileContents' => $response->body(),
                    'friendlyName' => $latestVersion['name'] ?? $resourceId,
                    'friendlyVersion' => $latestVersion['version_number'] ?? 'unknown',
                ];
            } else {
                return null;
            }
        }
    }
}
