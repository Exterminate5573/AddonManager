<?php

namespace Pterodactyl\BlueprintFramework\Extensions\{identifier};

use Illuminate\Support\Facades\Http;

class PaperMCProvider
{
    public function searchAddons($query, $page = 1)
    {
        $response = Http::get('https://hangar.papermc.io/api/v1/projects', [
            'query' => $query,
            'page' => $page,
            'size' => 10,
        ]);
    }

    public function downloadAddon($resourceId)
    {
        // TODO: Implement PaperMC API download logic
        return null;
    }
}
