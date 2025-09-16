<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmanager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\SpigotProvider;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\ModrinthProvider;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\PaperMCProvider;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Models\Server;

class AddonController extends Controller
{
    public function __construct(
        private BlueprintExtensionLibrary $blueprint,
        private DaemonFileRepository $fileRepository,
    ) {}

    protected function getProvider($provider)
    {
        switch (strtolower($provider)) {
            case 'modrinth':
                return new ModrinthProvider();
            case 'papermc':
                return new PaperMCProvider();
            case 'spigot':
            default:
                return new SpigotProvider();
        }
    }
    // List all installed addons
    public function index()
    {
        return response()->json(DB::table('installed_addons')->get());
    }

    // Search addons from a provider
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);
        $providerName = $request->input('provider', 'spigot');
        $provider = $this->getProvider($providerName);
        $results = $provider->searchAddons($query, $page);

        foreach ($results['items'] as &$item) {
            //Query DB to see if installed
            $installed = DB::table('installed_addons')
                            ->where('provider', $item['provider'])
                            ->where('uuid', $item['uuid'])->first();
            $item['installed'] = $installed ? true : false;
            $item['upToDate'] = $installed && $installed->version === $item['addonVersionId'];
        }

        return response()->json($results);
    }

    // Download and install an addon from a provider
    public function store(Request $request, Server $server)
    {
        $data = $request->validate([
            'uuid' => 'required|string',
            'provider' => 'required|string'
        ]);

        // Download file from provider
        $provider = $this->getProvider($data['provider']);
        $info = $provider->downloadAddon($data['uuid']);
        if (!$info) {
            return response()->json(['success' => false, 'error' => 'Failed to download file'], 400);
        }

        // Store file using DaemonFileRepository
        $fileName = $info['friendlyName'] . '-' . $info['friendlyVersion'] . '.jar';
        $filePath = 'plugins/' . $fileName;
        $this->fileRepository->setServer($server)->putContent($filePath, $info['fileContents']);

        // Calculate file hash
        $fileHash = hash('sha256', $info['fileContents']);

        // Save to DB
        DB::table('installed_addons')->updateOrInsert(
            ['uuid' => $data['uuid']],
            [
                'version' => $data['version'],
                'provider' => $data['provider'],
                'friendly_name' => $info['friendlyName'] ?? $data['uuid'],
                'friendly_version' => $info['friendlyVersion'] ?? $data['version'],
                'file_hash' => $fileHash,
                'file_path' => $filePath,
            ]
        );

        return response()->json(['success' => true, 'fileHash' => $fileHash, 'filePath' => $filePath]);
    }

    // Update an existing addon record (re-download file)
    public function update(Request $request, Server $server, $uuid)
    {
        $data = $request->validate([
            'provider' => 'required|string',
        ]);

        $existing = DB::table('installed_addons')
                        ->where('provider', $data['provider'])
                        ->where('uuid', $uuid)->first();
        if (!$existing) {
            return response()->json(['success' => false, 'error' => 'Addon not found'], 404);
        }

        //Delete existing file
        if (isset($existing->file_path)) {
            $this->fileRepository->setServer($server)->delete($existing->file_path);
        }

        $provider = $this->getProvider($existing['provider']);
        $info = $provider->downloadAddon($existing['resource_id']);
        if (!$info) {
            return response()->json(['success' => false, 'error' => 'Failed to download file'], 400);
        }

        $fileName = $info['friendlyName'] . '-' . $info['friendlyVersion'] . '.jar';
        $filePath = 'plugins/' . $fileName;
        $this->fileRepository->setServer($server)->putContent($filePath, $info['fileContents']);
        $fileHash = hash('sha256', $info['fileContents']);

        DB::table('installed_addons')->where('uuid', $uuid)->update([
            'version' => $data['version'],
            'friendly_name' => $info['friendlyName'] ?? $data['uuid'],
            'friendly_version' => $info['friendlyVersion'] ?? $data['version'],
            'file_hash' => $fileHash,
            'file_path' => $filePath,
        ]);
        return response()->json(['success' => true, 'fileHash' => $fileHash, 'filePath' => $filePath]);
    }

    // Remove an addon and its file
    public function destroy($uuid)
    {
        $addon = DB::table('installed_addons')->where('uuid', $uuid)->first();
        if ($addon && isset($addon->file_path)) {
            $this->fileRepository->delete($addon->file_path);
        }
        DB::table('installed_addons')->where('uuid', $uuid)->delete();
        return response()->json(['success' => true]);
    }
}
